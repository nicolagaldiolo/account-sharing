<template>
  <div>
    <div v-for="sharingRequest in sharingRequests" :key="sharingRequest.id" class="card mb-3">
      <div class="row no-gutters">
        <div class="col-md-2">
          <img src="" class="card-img" alt="">
        </div>
        <div class="col-md-10">
          <div class="card-body">
            <h5 class="card-title">Nicola Galdiolo vuole entrare a far parte del gruppo <strong>{{sharingRequest.name}}</strong></h5>
            <p class="card-text">
              <a href="" class="btn btn-primary btn-sm">Accetta</a>
              <a href="" class="btn btn-outline-secondary btn-sm">Rifiuta</a>
            </p>
            <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    middleware: 'auth',

    created() {
      this.$store.dispatch('sharings/fetchSharingRequests');
    },

    computed: mapGetters({
      sharingRequests: 'sharings/sharingRequests',
    })
  }
</script>
