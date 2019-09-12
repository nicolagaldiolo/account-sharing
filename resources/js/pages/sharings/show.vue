<template>
  <div>
    <section class="jumbotron" :style="{'background-image': 'url(' + sharing.image + ')'}">
      <div class="container">
        <div class="row">
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <img :src="sharing.owner.photo_url">
                <h5 class="card-title">{{sharing.owner.name}}</h5>
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
        <a v-if="availability" class="btn btn-primary btn-lg btn-block">Invita altra gente</a>
      </div>
      <div v-else-if="foreign">
        <a @click.prevent="joinGroup" class="btn btn-primary btn-lg btn-block">Entra nel gruppo</a>
      </div>
      <div v-else>
        <div v-if="sharing.sharing_state_machine.transitions.length">
          <a v-for="(transition, index) in sharing.sharing_state_machine.transitions" :key="index" @click.prevent="doTransition(transition.value)" class="btn btn-primary btn-lg btn-block">
            {{transition.metadata.title}}
          </a>
        </div>
        <div v-else class="alert alert-primary text-center" role="alert">
          {{sharing.sharing_state_machine.status.metadata.title}}
        </div>
      </div>
    </div>

    <div v-if="owner || joined">
      <div class="container mt-4">
        <div class="row">
          <div v-if="sharing.active_users.length" class="col-md-4">
            <h4>Membri del gruppo</h4>


            <member-item :user="sharing.owner" :sharing="sharing" :isAdmin="true"/>
            <div v-for="(user, index) in sharing.active_users" :key="index" class="media text-muted pt-3">
              <member-item :user="user" :sharing="sharing"/>
            </div>



            <!--<div class="mt-4">
              <hr>
              <a @click.prevent="leaveGroup" href="#" class="btn btn-outline-secondary btn-block">Abbandona gruppo</a>
              <hr>
              <small>Il prossimo rinnovo sarà il <strong>{{sharing.renewalInfo.renewalDate | moment("D MMMM YYYY")}}</strong></small>
              <hr>
              <small>Se vuoi chiedere un rimborso il giorno limite è il <strong>{{sharing.refundInfo.day_limit | moment("D MMMM YYYY")}}</strong></small>
            </div>
            -->


          </div>
          <div class="col-md-8">
            <Chat :authUser="authUser" :sharing="sharing"/>
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

export default {
  middleware: 'auth',
  components: {
    MemberItem,
    Chat
  },

  created () {
    this.$store.dispatch('sharings/fetchSharing', this.$route.params.sharing_id)
  },

  computed: {
    ...mapGetters({
      sharing: 'sharings/sharing',
      authUser: 'auth/user'
    }),

    availability: function () {
      return this.sharing.availability > 0
    },
    owner: function () {
      return this.authUser.id === this.sharing.owner_id
    },
    foreign: function () {
      return this.sharing.sharing_state_machine === null
    },
    joined: function () {
      return this.sharing.sharing_state_machine && this.sharing.sharing_state_machine.status.value === 3
    },
  },

  methods: {
    joinGroup () {
      axios.post(`/api/sharings/${this.sharing.id}/join`).then((response) => {
        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
      })
    },
    doTransition (transition) {
      axios.patch(`/api/sharings/${this.sharing.id}/transitions/${transition}`).then((response) => {
        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
      })
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
