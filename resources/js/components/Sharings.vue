<template>
    <div>
      <h2 v-if="title">{{title}}</h2>
      <div class="row">
        <div class="col-md-3 mb-4" v-for="sharing in items" :key="sharing.id">
          <div class="card">
            <router-link :to="{ name: 'sharing.show', params: { category_id: sharing.category_id, sharing_id: sharing.id } }">
              <img :src="sharing.image" class="card-img-top" alt="...">
            </router-link>
            <div class="card-body">
              <div v-if="sharing.owner" class="card-text d-flex align-items-center">
                <img class="rounded-circle mr-2" :src="sharing.owner.photo_url" width="30">
                <small class="text-muted">
                  <span><strong>{{sharing.owner.username}}</strong></span><br>
                  <span>Membro dal <strong>{{sharing.owner.created_at | moment("D MMMM YYYY")}}</strong></span>

                </small>
              </div>
              <div class="pt-3">
                <h5 class="card-title p-0 m-0">{{sharing.name}}</h5>
                <span class="d-flex">
                  <money-format :value="sharing.price_with_fee" :locale="authUser.country" :currency-code="authUser.currency" :subunit-value=false :hide-subunits=false></money-format>
                  <span class="pl-1">({{sharing.renewal_frequency}})</span>
                </span>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
              <small class="text-muted">
                <strong>{{sharing.availability}}/{{sharing.max_slot_available}} Posti disponibili</strong>
              </small>
              <fa :class="calcCredentialStatus(sharing ? sharing.credential_status : 0).class" icon="key" fixed-width />
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
import MoneyFormat from 'vue-money-format'

export default {
  components: {
    InfiniteLoading,
    MoneyFormat
  },

  mixins: [ helperMixin ],
  middleware: [
    'auth',
    'registrationCompleted'
  ],

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
      this.$store.dispatch('sharings/fetchSharings', this.getQueryString({
        page: this.current_page,
        type: this.type
      }))
    }
  }
}
</script>
