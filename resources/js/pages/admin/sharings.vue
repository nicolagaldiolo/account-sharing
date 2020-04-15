<template>
  <div>
    <div v-for="sharing in items" :key="sharing.id">
      <div class="row no-gutters">
        <div class="col-md-2 sharing_image_bg" :style="{'background-image': `url(${sharing.image})`}"></div>
        <div class="col-md-10">
          <div class="card-body">
            <ul class="list-group">
              <li class="list-group-item">
                <div class="d-flex">
                  <strong>{{sharing.name}}</strong>
                  <router-link class="pl-1" :to="{ name: 'sharing.show', params: { category_id: sharing.category_id, sharing_id: sharing.id } }">Vedi gruppo</router-link>
                </div>
                {{sharing.description}}
              </li>
              <li class="list-group-item">
                <strong>Utente:</strong><br>
                <div class="media align-items-center">
                  <img width="40" :src="sharing.owner.photo_url" class="mr-1 rounded-circle" alt="">
                  <div class="media-body align-items-center">
                    <span>{{sharing.owner.username}}</span>
                  </div>
                </div>
              </li>
              <li class="list-group-item">
                <strong>Data creazione:</strong><br>
                {{sharing.created_at | moment("D MMMM YYYY")}}
              </li>
              <li class="list-group-item">
                <strong>Ricorrenza pagamento:</strong><br>
                {{sharing.renewal_frequency.frequency}}
              </li>
              <li class="list-group-item">
                <strong>Totale:</strong><br>
                <money-format :value="sharing.price_with_fee" :locale="sharing.owner.country" :currency-code="sharing.owner.currency" :subunit-value=false :hide-subunits=false></money-format>
              </li>
              <li class="list-group-item">
                <strong>Posti disponibili:</strong><br>
                {{sharing.availability}}/{{sharing.max_slot_available}}
              </li>
            </ul>
            <div class="mt-3">
              <v-link type="primary" :data-id="sharing.id" :data-action=1 :loading="confirmStatus" :action="manage">Approva</v-link>
              <v-link type="secondary" :data-id="sharing.id" :data-action=2 :loading="refusedStatus" :action="manage">Rifiuta</v-link>
            </div>
          </div>
        </div>
      </div>
      <hr>
    </div>
    <infinite-loading spinner="waveDots" :identifier="type" @infinite="infiniteHandler"></infinite-loading>

  </div>
</template>

<script>
  import InfiniteLoading from 'vue-infinite-loading'
  import { helperMixin } from '~/mixins/helperMixin'
  import { mapGetters } from 'vuex'
  import MoneyFormat from 'vue-money-format'
  import axios from 'axios'
  import Swal from 'sweetalert2'

  export default {
    components: {
      InfiniteLoading,
      MoneyFormat
    },
    mixins: [ helperMixin ],
    props: {
      type: {
        type: String,
        default: ''
      }
    },

    computed: mapGetters({
      sharings: 'admin/sharings'
    }),

    data () {
      return {
        items: [],
        current_page: 1,
        loading_state: {},
        confirmStatus: false,
        refusedStatus: false
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
        this.$store.dispatch('admin/fetchSharings', this.getQueryString({
          page: this.current_page,
          type: this.type
        }))
      },

      async manage (event) {
        const action = parseInt(event.target.getAttribute('data-action'))
        const id = parseInt(event.target.getAttribute('data-id'))

        if (action === 1) {
          this.confirmStatus = true
        } else if (action === 2) {
          this.refusedStatus = true
        }

        Swal.fire({
          type: 'warning',
          title: 'Sicuro di voler procedere?',
          text: 'Non puoi annullare questa operazione',
          showCancelButton: true
        }).then(result => {
          if (result.value) {
            axios.patch(`/api/admin/sharings/${id}`, { action }).then(response => {
              const sharing = response.data.data
              this.items = this.items.filter(item => item.id !== sharing.id)
              if (action === 1) {
                Swal.fire({
                  type: 'success',
                  title: 'Gruppo confermato',
                  text: 'Il gruppo è stato confermato'
                }).then(this.removeLoader)
              } else if (action === 2) {
                Swal.fire({
                  type: 'success',
                  title: 'Gruppo rifiutato',
                  text: 'Il gruppo è stato rifiutato'
                }).then(this.removeLoader)
              }
            })
          } else {
            this.removeLoader()
          }
        });
      },

      removeLoader () {
        this.confirmStatus = false
        this.refusedStatus = false
      }
    },



  }
</script>
<style scoped>
  .sharing_image_bg{
    background-repeat: no-repeat;
    background-size: 100%;
  }
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
