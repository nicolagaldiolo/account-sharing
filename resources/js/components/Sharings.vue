<template>
    <div>
      <h2 v-if="title">{{title}}</h2>

      <div class="row">
        <div class="col-md-3 mb-4" v-for="sharing in sharings" :key="sharing.id">
          <div class="card">
            <router-link :to="{ name: 'sharing.show', params: { category_id: sharing.category_id, sharing_id: sharing.id } }">
              <img :src="sharing.image" class="card-img-top" alt="...">
            </router-link>
            <div class="card-body">
              <p class="card-text">
                <a href="">
                  <img class="rounded-circle" :src="sharing.owner.photo_url" width="40">
                  <small class="text-muted">{{sharing.owner.name}}</small>
                </a>
              </p>
              <h5 class="card-title">{{sharing.name}}</h5>
            </div>
            <div class="card-footer">
              <small class="text-muted"></small>
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
    import axios from 'axios'

    export default {
      components: {
        InfiniteLoading
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

      data(){
        return {
          sharings: [],
          current_page: 1
        }
      },

      watch: {
        type: function(){
          this.sharings = [];
          this.current_page = 1;
        }
      },

      methods: {
        async infiniteHandler ($state) {

          const params = {
            page: this.current_page,
            type: this.type
          }

          axios.get('/api/sharings' + this.getQueryString(params)).then(({ data }) => {
            if (data.data.length) {
              this.sharings.push(...data.data)
              $state.loaded()
            }

            if (this.current_page < data.meta.last_page) {
              this.current_page += 1
            } else {
              $state.complete()
            }

          })
        }
      }
    }
</script>
