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
        <a @click="joinGroup" class="btn btn-primary btn-lg btn-block">Entra nel gruppo</a>
      </div>
      <div v-else>
        <div v-if="sharing.sharing_state_machine.transitions.length">
          <a v-for="(transition, index) in sharing.sharing_state_machine.transitions" :key="index" @click="doTransition(transition.value)" class="btn btn-primary btn-lg btn-block">
            {{transition.metadata.title}}
          </a>
        </div>
        <div v-else class="alert alert-primary text-center" role="alert">
          {{sharing.sharing_state_machine.status.metadata.title}}
        </div>
      </div>
    </div>

    <div v-if="owner || joined">
      <h2>Sono il proprietario o faccio parte del gruppo</h2>
    </div>

  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'

export default {
  middleware: 'auth',

  created () {
    this.$store.dispatch('sharings/fetchSharing', this.$route.params.sharing_id)
  },

  computed: {
    ...mapGetters({
      sharing: 'sharings/sharing',
      user: 'auth/user'
    }),
    availability: function () {
      return this.sharing.availability > 0
    },
    owner: function () {
      return this.user.id === this.sharing.owner_id
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
