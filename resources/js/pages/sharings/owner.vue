<template>
  <div>
    <div v-for="sharing in sharings.data" :key="sharing.id">
      <div class="row no-gutters">
        <div class="col-md-2" :style="{'background-image': `url(${sharing.image})`}"></div>
        <div class="col-md-10">
          <div class="card-body">
            <h5 class="card-title">{{sharing.name}} <small>{{sharing.availability}}/{{sharing.capacity}} disponibili</small></h5>
            <router-link :to="{ name: 'sharing.show', params: { category_id: sharing.category_id, sharing_id: sharing.id } }">Visualizza scheda</router-link>
            <p class="card-text">
              <div v-if="sharing.users && sharing.users.length">
                <ul class="list-group">
                  <li v-for="user in sharing.users" :key="user.id" class="list-group-item">
                    <manage-sharing-user :user="user" :sharing="sharing"/>
                  </li>
                </ul>
              </div>
            </p>
            <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
          </div>
        </div>
      </div>
    </div>
    <infinite-loading spinner="waveDots" :identifier="type" @infinite="infiniteHandler"></infinite-loading>

  </div>
</template>

<script>
import InfiniteLoading from 'vue-infinite-loading'
import { helperMixin } from '~/mixins/helperMixin'
import { mapGetters } from 'vuex'
import ManageSharingUser from '../../components/ManageSharingUser'

export default {
  components: {
    InfiniteLoading,
    ManageSharingUser
  },
  mixins: [ helperMixin ],
  middleware: 'auth',

  props: {
    type: {
      type: String,
      default: ''
    }
  },

  computed: mapGetters({
    sharings: 'sharings/sharings'
  }),

  data () {
    return {
      current_page: 1,
      loading_state: {}
    }
  },

  watch: {
    sharings (data) {
      if (data.data && data.data.length) {
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
      this.$store.dispatch('sharings/fetchSharings', this.getQueryString({
        page: this.current_page,
        type: this.type
      }))
    }
  }

}
</script>
<style scoped>
  .category-item{
    position: relative;
  }
  .category-item span{
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    text-align: center;
  }
</style>
