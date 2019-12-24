<template>
  <div>

    <div class="balance">
      <h2>Balance</h2>
      <ul class="list-group mb-3">
        <li class="list-group-item d-flex justify-content-between lh-condensed">
          <div>
            <h4 class="my-0">Saldo contabile</h4>
            <small class="text-muted" v-if="balance.dayofdelay">Disponibili dopo <strong>{{balance.dayofdelay}}gg</strong> dalla ricezione del pagamento</small>
          </div>
          <span v-for="(pending, index) in balance.pending" :key="index" class="text-muted">
            <money-format :value="(pending.total / 100)" locale="IT" :currency-code='pending.currency' :subunit-value=false :hide-subunits=false></money-format>
          </span>
        </li>
        <li class="list-group-item d-flex justify-content-between lh-condensed">
          <div>
            <h4 class="my-0">Saldo disponibile</h4>
          </div>
          <span v-for="(available, index) in balance.available" :key="index" class="text-muted">
            <money-format :value="(available.total / 100)" locale="IT" :currency-code='available.currency' :subunit-value=false :hide-subunits=false></money-format>
          </span>
        </li>
      </ul>
      <a href="#" class="btn btn-primary btn-lg btn-block">Preleva</a>
    </div>

    <h2>Filtra la ricerca</h2>

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
    import MoneyFormat from "vue-money-format";

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
      },
      balance: {}
  }),

  components: {
      Transaction,
      InfiniteLoading,
      'money-format': MoneyFormat
  },

  created () {
    this.getBalance()
  },

    watch: {
      'search_fields.type': function (val) {
          this.search_fields.subtype = ''
      }

    },

  methods: {

      getBalance () {
        axios.get('/api/settings/balance').then(function (result) {
          if (result.error) {
            // Display error.message in your UI.
          } else {
            this.balance = result.data
          }
        }.bind(this))
      },

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
