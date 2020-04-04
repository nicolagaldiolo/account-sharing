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
import Swal from 'sweetalert2'
import CredentialForm from './CredentialForm'
import CredentialMemberStatus from './CredentialMemberStatus'
import { mapGetters } from 'vuex'
import { helperMixin } from '~/mixins/helperMixin'
import VLink from './Link'
import axios from 'axios'

export default {

  name: 'Credentials',
  components: {
    VLink,
    CredentialMemberStatus,
    CredentialForm
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
  }
}
</script>
<style scoped>
  .c-green{
    color: #1c7430;
  }

  .c-red{
    color: red;
  }
</style>
