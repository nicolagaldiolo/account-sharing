<template>
  <div>
    <div class="container">
      <div v-for="(notification, key) in items" :key="key" class="card mb-3">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <img :src="notification.data.icon" width="40" class="rounded-circle" alt="">
            <div>
              <h6 class="card-title pb-0 mb-0">{{ notification.data.desc }}</h6>
              <p class="card-text"><small class="text-muted">{{ notification.created_at | moment("D MMMM YYYY") }}</small></p>
            </div>
            <a href="#" @click.prevent="read" :data-id="notification.id">Chiudi</a>
          </div>
        </div>
      </div>

    </div>
    <infinite-loading spinner="waveDots" @infinite="infiniteHandler"></infinite-loading>
  </div>

</template>

<script>
import InfiniteLoading from 'vue-infinite-loading'
import { helperMixin } from '~/mixins/helperMixin'
import { mapGetters } from 'vuex'
import axios from "axios";

export default {
  components: {
    InfiniteLoading
  },

  mixins: [ helperMixin ],

  computed: mapGetters({
    notifications: 'settings/notifications'
  }),

  data () {
    return {
      items: [],
      current_page: 1,
      loading_state: {}
    }
  },

  watch: {
    notifications (data) {

      if (data.data.length) {
        this.items = data.data
        this.loading_state.loaded()
      }

      if (this.current_page < data.meta.last_page) {
        this.current_page += 1
      } else {
        this.loading_state.complete()
      }
    }
  },

  methods: {
    async infiniteHandler (state) {
      this.loading_state = state
      this.$store.dispatch('settings/fetchNotifications', this.getQueryString({
        page: this.current_page
      }))
    },
    read: function (event) {
      this.$store.dispatch('settings/readNotification', event.target.getAttribute('data-id'))
    }
  }

}
</script>
