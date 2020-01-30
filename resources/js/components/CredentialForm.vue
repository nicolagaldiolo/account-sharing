<template>
  <div>
    <h4>Credenziali di accesso</h4>
    <div class="custom-control custom-switch ml-auto">
      <input type="checkbox" @click="credentialToggle" class="custom-control-input" :id="credentialSwitchId">
      <label class="custom-control-label" :for="credentialSwitchId">Mostra credenziali</label>
    </div>
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
      <v-button class="btn-block" v-if="owner" :disabled="cannotUpdateCredential" :loading="form.busy" type="success">Aggiorna credenziali</v-button>
    </form>
    <!-- Action only for active user -->
    <div v-if="iMustConfirm">
      <v-link class="btn btn-block btn-success" :data-action=1 :loading="confirmStatus" :action="confirmCredentials">Conferma credenziali</v-link>
      <v-link class="btn btn-block btn-danger" :data-action=2 :loading="wrongStatus" :action="confirmCredentials">Le credenziali sono errate</v-link>
    </div>

  </div>
</template>

<script>
  import Form from 'vform'
  import axios from "axios";
  import Swal from 'sweetalert2'
  import VLink from "./Link";
  export default {
    name: 'CredentialForm',
    components: {
      VLink
    },
    props: {
      credential: {
        type: Object,
        default: null
      },
      member: {
        type: Object,
        default: null
      },
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
      showCredential: false,
      confirmStatus: false,
      wrongStatus: false
    }),

    created () {

      this.form.keys().forEach(key => {
        this.form[key] = this.credential ? this.credential[key] : ''
      })
    },

    computed: {
      credentialSwitchId () {
        return 'customSwitch_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
      },

      iMustConfirm () {
        return (!this.owner && (this.member && this.member.credential_status === 0))
      },
      cannotUpdateCredential () {
        return !this.form.username || !this.form.password || (this.credential && this.credential.username === this.form.username && this.credential.password === this.form.password)
      }
    },

    methods: {
      credentialToggle () {
        this.showCredential = !this.showCredential
      },

      async saveCredentials () {
        const api = (this.sharing.multiaccount) ? `/api/sharings/${this.sharing.id}/credential/${this.member.id}` : `/api/sharings/${this.sharing.id}/credential/`;
        const { data } = await this.form.patch(api)

        if (data.data) {
          Swal.fire({
            type: 'success',
            title: 'Credenziali aggiornate',
            text: this.sharing.multiaccount ? 'L\'utente ' + this.member.username + ' è stato informato' : 'Gli utenti sono stati informati'
          })

          this.$store.dispatch('sharings/saveCredentials', data.data).then(
            this.$store.dispatch('sharings/fetchSharing', this.sharing.id)
          )

          this.$modal.hide('credentialBox')
        }
      },
      async confirmCredentials (event) {
        const action = parseInt(event.target.getAttribute('data-action'))

        if (action === 1) {
          this.confirmStatus = true
        } else if (action === 2) {
          this.wrongStatus = true
        }

        axios.post(`/api/sharings/${this.sharing.id}/credential/${action}`).then(response => {
          this.$store.dispatch('sharings/updateSharing', { sharing: response.data.data })

          if (action === 1) {
            this.confirmStatus = false
            Swal.fire({
              type: 'success',
              title: 'Credenziali confermate',
              text: 'Grazie per aver confermato le credenziali'
            })
          } else if (action === 2) {
            this.wrongStatus = false
            Swal.fire({
              type: 'warning',
              title: 'Segnalate credenziali errate',
              text: `${this.sharing.owner.username} provvederà a fornire delle credenziali corrette`
            })
          }

          this.$modal.hide('credentialBox')

        })
      }
    },
  }
</script>
