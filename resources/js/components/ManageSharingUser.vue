<template>
  <div class="d-flex justify-content-between align-items-center">
    <div class="media align-items-center">
      <img src="https://lorempixel.com/40/40/?28787" class="mr-3 rounded-circle" alt="">
      <div class="media-body align-items-centeri">
        <h6 class="mt-0">{{user.username}}<br>
          <small class="text-muted">{{ sharingUserStatus }}</small>
        </h6>
      </div>
    </div>
    <div>
      <a v-for="(item, index) in user.transitions" :key="index" @click.prevent="transition(item)" href="#" class="btn btn-primary btn-sm">{{item}}</a>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'

  export default {
    name: 'ManageSharingUser',
    props: {
      sharing: {
        type: Object,
        default: null
      },
      user: {
        type: Object,
        default: null
      }
    },
    computed: {
      sharingUserStatus: function () {
        return window.config.sharingUserStatus[this.user.user_status]
      }
    },

    methods: {
      transition: function (transition) {
        axios.patch(`/api/sharings/${this.sharing.id}/user/${this.user.id}/transition-user/${transition}`).then((response) => {
          this.$store.dispatch('sharings/syncSharings', { sharing: response.data.data })
        });
      }
    }
  }
</script>
