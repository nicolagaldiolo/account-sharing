<template>
  <div>
    <div v-if="credentialConfirmed.ownerConfirmed || owner">
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
      <!-- Action only for active user -->
      <a class="btn btn-block btn-success" @click.prevent="confirmCredentials" v-if="credentialConfirmed.iMustConfirm">Conferma credenziali</a>
      <hr>
      <div class="card">
        <div class="card-header">
          <strong>Stato delle credenziali</strong><br>
          <span>{{ credentialConfirmed.confirmed.length > 0 ? 'Credenziali confermate' : 'Credenziali non confermate' }} {{credentialConfirmed.confirmed.length}}/{{credentialConfirmed.total.length}} utenti</span>
        </div>
        <ul class="list-group list-group-flush">
          <li v-for="(user, index) in credentialConfirmed.confirmed" :key="index" class="list-group-item">
            <img class="mr-2 rounded-circle" :src="user.photo_url" style="width: 32px; height: 32px;">
            <div class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
              <strong class="text-gray-dark">{{user.name}}</strong>
              <span class="d-block">Credenzilai confermate il {{user.credential_updated_at | moment("D MMMM YYYY")}}</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <div v-else>
      <h4>Credenziali non ancora inserite dall'admin</h4>
      <span>Le credenziali verranno fornite al più presto</span>
    </div>
  </div>
</template>

<script>
    import Form from 'vform'
    import axios from 'axios'
    import VButton from './Button'

    export default {

      name: 'Credentials',
      components: {
        VButton
      },
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
        showCredential: false
      }),

      created () {
        window.Echo.private(`App.User.${this.authUser.id}`).notification(notifications => {
          this.$store.dispatch('sharings/updateSharing', {sharing: notifications.data})
          let message = (notifications.type === 'App\\Notifications\\CredentialConfirmed') ? 'Credenziali confermate' : 'Credenziali aggiornate'
          alert(message)
        })
      },

      computed: {
        credentialConfirmed () {
          const ownerConfirmed = this.sharing.credential_updated_at;
          const userLogged = this.sharing.members.filter(user => this.authUser.id === user.id)[0]
          const userLoggedConfirmed = userLogged && userLogged.credential_updated_at

          return {
            ownerConfirmed: ownerConfirmed,
            iMustConfirm: (userLogged && !userLoggedConfirmed) || (ownerConfirmed && userLoggedConfirmed && this.$moment(ownerConfirmed).isAfter(this.$moment(userLoggedConfirmed))),
            confirmed: this.sharing.members.filter(member => {
              const memberConfirmed = member.credential_updated_at
              return memberConfirmed && ownerConfirmed && this.$moment(memberConfirmed).isAfter(ownerConfirmed)
            }),
            total: this.sharing.members
          };
        },
        saveCredentialReady () {
          return !this.form.username || !this.form.password
        },
      },

      methods: {
        credentialToggle () {
          this.showCredential = !this.showCredential
        },

        async saveCredentials () {
          const { data } = await this.form.patch(`/api/sharings/${this.sharing.id}/credential`)
          if (data) this.$store.dispatch('sharings/updateSharing', { sharing: data.data })
        },
        async confirmCredentials () {
          axios.post(`/api/sharings/${this.sharing.id}/credential`).then(response => {
            this.$store.dispatch('sharings/updateSharing', { sharing: response.data.data })
          });
        }
      }
    }
</script>
