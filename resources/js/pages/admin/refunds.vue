<template>
  <div>
    <div v-for="refund in items" :key="refund.id">
      <div class="row no-gutters">
        <div class="col-md-2 sharing_image_bg" :style="{'background-image': `url(${refund.sharing.image})`}"></div>
        <div class="col-md-10">
          <div class="card-body">
            <ul class="list-group">
              <li class="list-group-item">
                <div class="d-flex">
                  <strong>{{refund.sharing.name}}</strong>
                  <router-link class="pl-1" :to="{ name: 'sharing.show', params: { category_id: refund.sharing.category_id, sharing_id: refund.sharing.id } }">Vedi gruppo</router-link>
                </div>
                {{refund.sharing.description}}
              </li>
              <li class="list-group-item">
                <strong>Utente:</strong><br>
                <div class="media align-items-center">
                  <img width="40" :src="refund.user.photo_url" class="mr-1 rounded-circle" alt="">
                  <div class="media-body align-items-center">
                    <span>{{refund.user.username}}</span>
                  </div>
                </div>
              </li>
              <li class="list-group-item">
                <strong>Data richiesta:</strong><br>
                {{refund.created_at | moment("D MMMM YYYY")}}
              </li>
              <li class="list-group-item">
                <strong>Stato del rimborso:</strong><br>
                {{status(refund.status)}}
              </li>
              <li class="list-group-item">
                <strong>Totale rimborso:</strong><br>
                <money-format :value="refund.total.value" :locale="refund.user.country" :currency-code="refund.total.currency" :subunit-value=false :hide-subunits=false></money-format>
              </li>
              <li class="list-group-item">
                <strong>Motivo del rimborso:</strong><br>
                {{refund.reason}}
              </li>
            </ul>

            <div class="mt-3">
              <a @click.prevent="manage(refund.id, 'APPROVE', $event)" href="#" class="btn btn-primary">Approva</a>
              <a @click.prevent="manage(refund.id, 'REFUSE', $event)" href="#" class="btn btn-secondary">Rifiuta</a>
            </div>
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
  import axios from 'axios'
  import Swal from 'sweetalert2'

  export default {
    components: {
      InfiniteLoading,
      MoneyFormat,
    },
    mixins: [ helperMixin ],
    props: {
      type: {
        type: String,
        default: ''
      }
    },

    computed: mapGetters({
      refunds: 'admin/refunds'
    }),

    data () {
      return {
        items: [],
        current_page: 1,
        loading_state: {}
      }
    },

    watch: {
      refunds (data) {
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
      status (status) {
        let str = ''
        switch (status) {
          case 0:
            str = 'PENDING'
            break
          case 1:
            str = 'APPROVED'
            break
          case 2:
            str = 'REFUSED'
            break
        }
        return str
      },

      async infiniteHandler (state) {
        this.loading_state = state
        this.$store.dispatch('admin/fetchRefunds', this.getQueryString({
          page: this.current_page,
          type: this.type
        }))
      },

      manage (id, action, evt) {

        Swal.fire({
          type: 'warning',
          title: 'Sicuro di voler procedere?',
          text: 'Non puoi annullare questa operazione',
          showCancelButton: true
        }).then(result => {
          if (result.value) {
            axios.patch(`/api/admin/refunds/${id}`, { action }).then((response) => {
              const refund = response.data.data
              this.items = this.items.filter(item => item.id !== refund.id)
            })
          }
        });
      }

    }

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
