import Swal from "sweetalert2";
<template>
  <div>
    <div v-if="authUser.admin && sharingPending" class="pt-2 pb-2 mb-4 text-white bg-danger">
      <div class="container">
        <div class="d-flex align-items-center">
          <div>
            <h4 class="mb-0 pb-0">Approva condivisione</h4>
            <span>Approva o rifiuta la condivisione</span>
          </div>

          <div class="ml-auto">
            <v-link class="btn-sm" type="light" :data-action=1 :loading="confirmStatus" :action="changeStatus">Approva condivisione</v-link>
            <v-link class="btn-sm" type="outline-light" :data-action=2 :loading="refusedStatus" :action="changeStatus">Rifiuta condivisione</v-link>
          </div>
        </div>
      </div>
    </div>

    <section class="jumbotron" :style="{'background-image': 'url(' + sharing.image + ')'}">
      <div class="container">
        <div class="row">
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">{{sharing.name}}</h5>
                <p class="card-text">{{sharing.description}}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="container">

      <div v-if="sharingApproved">
        <div v-if="foreign">
          <a @click.prevent="doTransition()" class="btn btn-primary btn-lg btn-block">Entra nel gruppo</a>
        </div>
        <div v-else>

          <!--<div v-if="userSubscription === 4" class="alert alert-danger" role="alert">
            Attenzione ci sono problemi con i pagamenti.
            <router-link :to="{ name: 'sharing.restore' }" class="alert-link">Completa pagamento</router-link>
          </div>-->

          <div v-if="sharing.user_status && !joined">
            <div v-if="sharing.user_status.transitions.length">
              <a v-for="(transition, index) in sharing.user_status.transitions" :key="index" href="#" @click.prevent="doTransition(transition.value)" class="btn btn-primary btn-lg btn-block">
                {{transition.metadata.title}}
              </a>
            </div>
            <div v-else class="alert alert-primary text-center" role="alert">
              {{sharing.user_status.state.metadata.title}}
            </div>
          </div>

          <a v-if="availability && (joined || owner)" class="btn btn-primary btn-lg btn-block">Invita i tuoi amici</a>
        </div>

        <div v-if="joined || owner" class="mt-4">
          <div class="row">
            <div class="col-md-4">
              <credentials :authUser="authUser" :owner="owner" :sharing="sharing"></credentials>
              <h4>Membri del gruppo</h4>
              <div v-for="(member, index) in globalMembers" :key="index" class="media text-muted pt-3">
                <member-item :sharingId="sharing.id" :owner="sharing.owner" :member="member" :authUser="authUser"/>
              </div>
            </div>
            <div class="col-md-8">
              <Chat :authUser="authUser" :sharing="sharing" :joined="joined" :owner="owner"/>
            </div>
          </div>
        </div>
      </div>
      <div v-else-if="sharingPending" class="alert alert-primary text-center" role="alert">
        Condivisione in fase di approvazione, attendere comunicazione da parte dello staff.
      </div>

    </div>
  </div>
</template>
<script>
    import { mapGetters } from 'vuex'
    import axios from 'axios'
    import Swal from 'sweetalert2'
    import MemberItem from '~/components/MemberItem'
    import Chat from '~/components/Chat'
    import Credentials from '~/components/Credentials'

    export default {
        components: {
          Credentials,
          MemberItem,
          Chat
        },

        data: () => ({
          globalMembers: [],
          confirmStatus: false,
          refusedStatus: false
        }),

        created () {

          this.$store.dispatch('sharings/fetchSharing', this.$route.params.sharing_id).then(() => {
            if (!this.$store.getters['sharings/sharing'].id && this.$store.getters['sharings/sharing'].category_id === +this.$route.params.category_id) {
              this.$router.push({ name: 'home' })
            }
          })
        },

        computed: {
            ...mapGetters({
                sharing: 'sharings/sharing',
                authUser: 'auth/user'
            }),
            availability () {
                return this.sharing.availability > 0
            },
            owner () {
              return this.sharing.owner && this.authUser.id === this.sharing.owner.id
            },
            foreign () {
                return !this.sharing.user_status && !this.owner
            },
            joined () {
                return this.sharing.user_status && this.sharing.user_status.state.value === 3
            },
            sharingPending () {
              return this.sharing.status === 0
            },
            sharingApproved () {
              return this.sharing.status === 1
            },
            sharingRefused () {
                return this.sharing.status === 2
            },
            /*
            userSubscription () {
                const user = this.sharing.active_users.filter(user => user.id === this.authUser.id)
                return (user.length && user[0].sharing_status.subscription) ? user[0].sharing_status.subscription.status : {}
            }

             */
        },

        watch: {
            sharing (value) {
                this.globalMembers = [value.owner];
                if(value.members) this.globalMembers.push(...value.members);
            }
        },


        methods: {

            async changeStatus (event) {
              const action = parseInt(event.target.getAttribute('data-action'))

              if (action === 1) {
                this.confirmStatus = true
              } else if (action === 2) {
                this.refusedStatus = true
              }

              axios.patch(`/api/sharings/${this.sharing.id}/status/${action}`).then(response => {
                this.$store.dispatch('sharings/updateSharing', { sharing: response.data.data })

                if (action === 1) {
                  this.confirmStatus = false
                  Swal.fire({
                    type: 'success',
                    title: 'Gruppo confermato',
                    text: 'Il gruppo è stato confermato'
                  })
                } else if (action === 2) {
                  this.refusedStatus = false
                  Swal.fire({
                    type: 'success',
                    title: 'Gruppo rifiutato',
                    text: 'Il gruppo è stato rifiutato'
                  }).then(result => {
                    if (result.value) this.$router.push({ name: 'home' })
                  })
                }

              })
            },

            doTransition (transition) {
                if(transition === 'pay') {
                    const category = this.$route.params.category_id
                    const sharing = this.$route.params.sharing_id
                    this.$router.push({ name: 'sharing.checkout', params: { category, sharing } })
                } else {
                    let api = (transition)
                        ? `/api/sharings/${this.sharing.id}/transitions/${transition}`
                        : `/api/sharings/${this.sharing.id}/transitions`
                    axios.patch(api).then((response) => {
                        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
                    })
                }
            }

        }
    }
</script>

<style scoped>
  .jumbotron{
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
  }
</style>
