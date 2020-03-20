<template>
    <div v-if="sharings">
      <h2>{{title}}</h2>
      <div class="row">
        <div class="col-md-3 mb-4" v-for="sharing in items" :key="sharing.id">
          <sharing-card :sharing="sharing" :authUser="authUser"/>
        </div>
      </div>
      <infinite-loading spinner="waveDots" :identifier="type" @infinite="infiniteHandler"></infinite-loading>
    </div>
</template>

<script>
import InfiniteLoading from 'vue-infinite-loading'
import { helperMixin } from '~/mixins/helperMixin'
import { mapGetters } from 'vuex'
import SharingCard from './SharingCard'

export default {
  components: {
    SharingCard,
    InfiniteLoading,
  },

  mixins: [ helperMixin ],

  props: {
    title: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: ''
    }
  },

  computed: {
    ...mapGetters({
      sharings: 'sharings/sharings',
      authUser: 'auth/user'
    })
  },

  data () {
    return {
      items: [],
      current_page: 1,
      loading_state: {}
    }
  },

  watch: {
    sharings (data) {
      if (data.data && data.data.length) {
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

      const params = {
        page: this.current_page,
        type: this.type
      }

      if (this.$route.params.category_id) {
        params.category = this.$route.params.category_id
      }

      this.$store.dispatch('sharings/fetchSharings', this.getQueryString(params))
    }
  }
}
</script>
