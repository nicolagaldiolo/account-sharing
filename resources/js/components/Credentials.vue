<template>
  <div>
    <modal name="credentialBox" width="80%" height="auto" :scrollable="true">
      <!--<a @click="$modal.hide('credentialBox')">❌</a>-->
      <div v-if="sharing.multiaccount && owner">
        <div class="card">
          <ul class="list-group list-group-flush">
            <li v-for="(member, index) in sharing.members" :key="index" class="list-group-item">
              <credential-member-status :member="member"></credential-member-status>
              <p>
                <a class="btn btn-primary" data-toggle="collapse" :href="'#multiCollapseExample' + index" role="button" aria-expanded="false" :aria-controls="'multiCollapseExample' + index">Credenziali</a>
              </p>
              <div class="row">
                <div class="col-auto">
                  <div class="collapse multi-collapse" :id="'multiCollapseExample' + index">
                    <div class="card card-body">
                      <credential-form :credential="getCredential(member.id)" :member="member" :authUser="authUser" :owner="owner" :sharing="sharing"></credential-form>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div v-else>
        <div v-if="ownerConfirmed || owner">
          <div class="row">
            <div class="col">
              <credential-form :credential="credentials[0]" :member="getMe" :authUser="authUser" :owner="owner" :sharing="sharing"></credential-form>
            </div>
            <div class="col">
              <div class="card">
                <ul class="list-group list-group-flush">
                  <li v-for="(member, index) in sharing.members" :key="index" class="list-group-item">
                    <credential-member-status :member="member"></credential-member-status>
                  </li>
                </ul>
              </div>
            </div>
          </div>

        </div>
        <div v-else>
          <div class="text-center">
            <h4>Credenziali non ancora inserite dall'admin</h4>
            <span>Le credenziali non sono ancora state generate</span>
            <v-link class="btn btn-block btn-success" :class="{ 'disabled': askCredentialDisableStatus }" :loading="askCredentialStatus" :action="askCredentials">Sollecita credenziali</v-link>
          </div>
        </div>
      </div>



    </modal>


    <div class="card">
      <div class="card-header">
        <div>
          <fa :class="credentialStatus.class" icon="key" fixed-width />
          <strong>Credenziali</strong><br>
          <span>Stato: <strong>{{ credentialStatus.state}}</strong></span>
        </div>
        <a @click.prevent="$modal.show('credentialBox')" href="#">Gestisci credenziali</a>
      </div>
    </div>

  </div>
</template>

<script>
    import Form from 'vform'
    import VButton from './Button'
    import CredentialForm from './CredentialForm'
    import CredentialMemberStatus from './CredentialMemberStatus'
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2'
    import { helperMixin } from '~/mixins/helperMixin'
    import VLink from "./Link";
    import axios from "axios";

    export default {

      name: 'Credentials',
      components: {
        VLink,
        CredentialMemberStatus,
        CredentialForm,
        VButton
      },

      mixins: [ helperMixin ],

      props: {
        authUser: {
          type: Object,
          default: null
        },
        sharing: {
          type: Object,
          default: null
        },
        owner: {
          type: Boolean,
          default: false
        }
      },

      data: () => ({
        form: new Form({
          username: '',
          password: ''
        }),
        askCredentialStatus: false,
        askCredentialDisableStatus: false,
        showCredential: false
      }),

      created () {

        this.$store.dispatch('sharings/fetchCredentials', this.$route.params.sharing_id)

        /*
        COMPLETAMENTE DA RIVEDERE, QUESTO EVENTO VIENE INVOCATO IN QUALSIASI SHARING (PER QUELL'UTENTE) E NON VA BENE



        window.Echo.private(`App.User.${this.authUser.id}`).notification( notifications => {

          this.$store.dispatch('sharings/updateSharing', { sharing: notifications.data.sharing })

          switch (notifications.type) {
            case 'App\\Notifications\\CredentialUpdated' :
              this.$store.dispatch('sharings/fetchCredentials', this.$route.params.sharing_id)
              if (!this.sharing.multiaccount || notifications.data.recipient.id === this.authUser.id) {
                Swal.fire({
                  type: 'success',
                  title: 'Credenziali aggiornate',
                  text: 'Credenziali aggiornate da ' + notifications.data.user,
                  confirmButtonText: 'Ok'
                })
              }
              break
            case 'App\\Notifications\\CredentialConfirmed' :

              // Show swal only for owner not others members
              if (notifications.data.sharing.owner.id === this.authUser.id) {
                if (notifications.data.action === 1) {
                  Swal.fire({
                    type: 'success',
                    title: 'Credenziali confermate',
                    text: 'Credenziali confermate da ' + notifications.data.user,
                    confirmButtonText: 'Ok'
                  })
                } else if (notifications.data.action === 2) {

                  Swal.fire({
                    type: 'error',
                    title: 'Credenziali errate',
                    text: notifications.data.user + ' ha confermato che le credenziali sono errate',
                    confirmButtonText: 'Ok'
                  })
                }
              }
              break
          }
        }),
        */

        this.form.keys().forEach(key => {
          this.form[key] = this.sharing[key]
        })
      },

      computed: {
        ...mapGetters({
          credentials: 'sharings/credentials'
        }),

        credentialStatus () {
          return this.calcCredentialStatus(this.sharing ? this.sharing.credential_status : 0)
        },

        ownerConfirmed () {
          return (this.sharing.multiaccount) ? this.getCredential(this.authUser.id) : (this.credentials.length > 0)
        },
        getMe () {
          return this.sharing.members.find(user => this.authUser.id === user.id)
        },
      },

      methods: {
        getCredential (id) {
          return this.credentials.find(item => item.user_id === id)
        },
        askCredentials () {
          this.askCredentialStatus = true;
          axios.get(`/api/sharings/${this.sharing.id}/getcredentials`).then(response => {
            if (response.data.data) {
              Swal.fire({
                type: 'success',
                title: 'Credenziali richieste all\'admin',
                text: 'Verrai avvisato appena le credenziali saranno disponibili'
              })
            }
            this.askCredentialStatus = false
            this.askCredentialDisableStatus = true
            this.$modal.hide('credentialBox')
          })
        }
      },
    }
</script>
<style>
  .v--modal{
    padding: 20px;
  }

  .c-green{
    color: #1c7430;
  }

  .c-red{
    color: red;
  }
</style>
