<template>
  <div>
    <h1>Filtra la ricerca</h1>

    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="type">Movimenti</label>
        <select v-model="search_fields.type" @change="updateResults" id="type" class="form-control">
          <option value="" selected>Tutti i movimenti</option>
          <option value="INVOICE">Pagamenti</option>
          <option value="PAYOUTS">Prelievi</option>
          <option value="REFUNDS">Rimborsi</option>
        </select>
      </div>
      <div class="form-group col-md-4">
        <label for="from">Periodo dal</label>
        <input v-model="search_fields.from" @change="updateResults" type="date" class="form-control" id="from">
      </div>
      <div class="form-group col-md-4">
        <label for="to">al</label>
        <input v-model="search_fields.to" @change="updateResults" type="date" class="form-control" id="to">
      </div>
    </div>

    <div v-if="search_fields.type !== 'PAYOUTS'" class="form-row">
      <div class="form-check form-check-inline">
        <input class="form-check-input" v-model="search_fields.subtype" @change="updateResults($event)" type="radio" name="direction" id="subtype1" value="">
        <label class="form-check-label" for="subtype1">Tutti</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" v-model="search_fields.subtype" @change="updateResults($event)" type="radio" name="direction" id="subtype2" value="OUTCOMING">
        <label class="form-check-label" for="subtype2">Effettuati</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" v-model="search_fields.subtype" @change="updateResults($event)" type="radio" name="direction" id="subtype3" value="INCOMING">
        <label class="form-check-label" for="subtype3">Ricevuti</label>
      </div>
    </div>

    <div class="list-group mt-4">
      <div v-for="list in lists" :key="list.id">
        <transaction :item="list"></transaction>
      </div>
    </div>

    <infinite-loading spinner="waveDots" :identifier="infiniteId" @infinite="infiniteHandler"></infinite-loading>

  </div>
</template>

<script>
    import InfiniteLoading from 'vue-infinite-loading'
    import axios from 'axios'
    import Transaction from '~/components/Transaction'

export default {
  data: () => ({
      lists: [],
      infiniteId: +new Date(),
      search_fields: {
          type: '',
          from: '',
          to: '',
          subtype: '',
          page: 1
      }
  }),

  components: {
      Transaction,
      InfiniteLoading
  },

    watch: {
      'search_fields.type': function (val) {
          this.search_fields.subtype = ''
      }

    },

  methods: {
      updateResults (event) {
          this.search_fields.page = 1
          this.lists = []
          this.infiniteId += 1
      },

      async infiniteHandler ($state) {
          const params = this.search_fields
          var queryString = Object.keys(params).filter(key => {
              if (params[key]) {
                  return key
              }
          }).map(key => key + '=' + params[key]).join('&')

          axios.get('/api/settings/transactions' + ((queryString) ? '?' + queryString : '')).then(({ data }) => {
              if (data.data.length) {
                  this.lists.push(...data.data)
                  $state.loaded()
              }

              if (this.search_fields.page < data.meta.last_page) {
                  this.search_fields.page += 1
              } else {
                  $state.complete()
              }

          })
      }
  }
}
</script>
