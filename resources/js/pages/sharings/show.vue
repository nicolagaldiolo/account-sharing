<template>
  <div>
    <section class="jumbotron" :style="{'background-image': 'url(' + sharing.image + ')'}">
      <div class="container">
        <div class="row">
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <!--<img :src="sharing.owner.photo_url">-->
                <!--<h5 class="card-title">{{sharing.owner.name}}</h5>-->
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
                <!--<a href="#" class="btn btn-primary">Go somewhere</a>-->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="container">
      <div v-if="owner || joined">

        <div v-if="userSubscription === 4" class="alert alert-danger" role="alert">
          Attenzione ci sono problemi con i pagamenti.
          <router-link :to="{ name: 'sharing.restore' }" class="alert-link">Completa pagamento</router-link>
        </div>

        <!--<a v-if="availability" class="btn btn-primary btn-lg btn-block">Invita altra gente</a>
        <div v-if="sharing.sharing_state_machine.transitions.length">
          <a v-for="(transition, index) in sharing.sharing_state_machine.transitions" :key="index" href="#" @click.prevent="doTransition(transition.value)" class="btn btn-primary btn-lg btn-block">
            {{transition.metadata.title}}
          </a>
        </div>-->
      </div>
      <div v-else-if="foreign">
        <a @click.prevent="doTransition()" class="btn btn-primary btn-lg btn-block">Entra nel gruppo</a>
      </div>
      <div v-else>
        <div v-if="sharing.sharing_state_machine.transitions.length">
          <a v-for="(transition, index) in sharing.sharing_state_machine.transitions" :key="index" href="#" @click.prevent="doTransition(transition.value)" class="btn btn-primary btn-lg btn-block">
            {{transition.metadata.title}}
          </a>
        </div>
        <div v-else class="alert alert-primary text-center" role="alert">
          {{sharing.sharing_state_machine.status.metadata.title}}
        </div>
      </div>
    </div>

    <div v-if="joined">
      <div class="container mt-4">
        <div class="row">
          <div class="col-md-4">
            <h4>Membri del gruppo</h4>

            <div v-for="(user, index) in sharing.active_users" :key="index" class="media text-muted pt-3">
              <member-item :owner="owner" :user="user" :authUser="authUser"/>
            </div>

            <hr>
            <h4>Credenziali di accesso</h4>
            <div class="custom-control custom-switch ml-auto">
              <input type="checkbox" @click="credentialToggle" class="custom-control-input" id="customSwitch1">
              <label class="custom-control-label" for="customSwitch1">Mostra credenziali</label>
            </div>
            <alert-success :form="form" message="Success!"></alert-success>
            <form @submit.prevent="saveCredentials" @keydown="form.onKeydown($event)">
              <div class="form-group">
                <label>Username</label>
                <input :readonly="!owner" v-model="form.username" name="userame" :type="(showCredential) ? 'text' : 'password'" :class="{ 'is-invalid': form.errors.has('username') }" class="form-control" placeholder="Username">
                <a v-on:click.prevent="" href="#" class="btn btn-sm btn-primary" v-clipboard="()=>form.username">Copy</a>
                <has-error :form="form" field="username" />
              </div>
              <div class="form-group">
                <label>Password</label>
                <input :readonly="!owner" v-model="form.password" name="password" :type="(showCredential) ? 'text' : 'password'" :class="{ 'is-invalid': form.errors.has('password') }" class="form-control" placeholder="Password">
                <a v-on:click.prevent="" href="#" class="btn btn-sm btn-primary" v-clipboard="()=>form.password">Copy</a>
                <has-error :form="form" field="password" />
              </div>
              <v-button class="btn-block" v-if="owner" :disabled="saveCredentialReady" :loading="form.busy" type="success">Aggiorna credenziali</v-button>
            </form>
            <a class="btn btn-block btn-success" @click.prevent="confirmCredentials" v-if="joinerCredentialConfirmed.iMustConfirm.length && !owner && joined">Conferma credenziali</a>
            <hr>
            <div class="card">
              <div class="card-header">
                <strong>Stato delle credenziali</strong><br>
                <span>{{ joinerCredentialConfirmed.confirmed.length > 0 ? 'Credenziali confermate' : 'Credenziali non confermate' }} {{joinerCredentialConfirmed.confirmed.length}}/{{joinerCredentialConfirmed.total.length}} utenti</span>
              </div>
              <ul class="list-group list-group-flush">
                <li v-for="(user, index) in joinerCredentialConfirmed.confirmed" :key="index" class="list-group-item">
                  <img class="mr-2 rounded-circle" :src="user.photo_url" style="width: 32px; height: 32px;">
                  <div class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                    <strong class="text-gray-dark">{{user.name}}</strong>
                    <span class="d-block">Credenzilai confermate il {{user.sharing_status.credential_updated_at | moment("D MMMM YYYY")}}</span>
                  </div>
                </li>
              </ul>
            </div>


          </div>
          <div class="col-md-8">
            <Chat :authUser="authUser" :sharing="sharing" :joined="joined" :owner="owner"/>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
<script>
    import { mapGetters } from 'vuex'
    import axios from 'axios'
    import MemberItem from '~/components/MemberItem'
    import Chat from '~/components/Chat'
    import Form from 'vform'
    import VButton from "../../components/Button";
    //import auth from "../../middleware/auth"

    export default {
        middleware: 'auth',
        components: {
            VButton,
            MemberItem,
            Chat
        },

        data: () => ({
            form: new Form({
                username: '',
                password: ''
            }),
            showCredential: false,
        }),

        created () {
            this.$store.dispatch('sharings/fetchSharing', this.$route.params.sharing_id);
            /*
            window.Echo.private(`App.User.${this.authUser.id}`).notification(notifications => {
                //console.log(notifications);
                if(notifications.data) {
                    this.$store.dispatch('sharings/updateSharing', { sharing: notifications.data })
                    let message = (notifications.type === 'App\\Notifications\\CredentialConfirmed') ? 'Credenziali confermate' : 'Credenziali aggiornate'
                    alert(message)
                }
            })
            */
        },

        computed: {
            ...mapGetters({
                sharing: 'sharings/sharing',
                authUser: 'auth/user'
            }),
            joinerCredentialConfirmed () {
                return {
                    iMustConfirm: this.sharing.active_users_without_owner.filter(item => this.authUser.id === item.id && !item.sharing_status.credential_updated_at),
                    confirmed: this.sharing.active_users_without_owner.filter(item => item.sharing_status.credential_updated_at),
                    total: this.sharing.active_users_without_owner
                };
            },
            saveCredentialReady () {
                return this.form.username === '' || this.form.password === ''
            },
            availability () {
                return this.sharing.availability > 0
            },
            owner () {
                return this.authUser.id === this.sharing.owner_id
            },
            foreign () {
                return this.sharing.sharing_state_machine === null
            },
            joined () {
                return this.sharing.sharing_state_machine !== null && this.sharing.sharing_state_machine.status.value === 3
            },
            userSubscription () {
                //const user = this.sharing.active_users.filter(user => user.id === this.authUser.id)
                return ''//(user.length && user[0].sharing_status.subscription) ? user[0].sharing_status.subscription.status : {}
            }
        },

        watch: {
            sharing(){
                this.form.keys().forEach(key => {
                    this.form[key] = this.sharing[key]
                })
            }
        },


        methods: {
            credentialToggle () {
                this.showCredential = !this.showCredential
            },
            doTransition (transition) {
                if(transition === 'pay') {
                    //https://router.vuejs.org/guide/essentials/navigation.html
                    const category = this.sharing.category.id
                    const sharing = this.sharing.id
                    this.$router.push({ name: 'sharing.checkout', params: { category, sharing } })
                } else {
                    let api = (transition)
                        ? `/api/sharings/${this.sharing.id}/transitions/${transition}`
                        : `/api/sharings/${this.sharing.id}/transitions`
                    axios.patch(api).then((response) => {
                        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
                    })
                }
            },
            async saveCredentials () {
                const { data } = await this.form.patch(`/api/sharings/${this.sharing.id}/credential`)
                if(data) this.$store.dispatch('sharings/updateSharing', { sharing: data })
            },
            async confirmCredentials () {
                axios.post(`/api/sharings/${this.sharing.id}/credential`).then(response => {
                    this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
                });
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
