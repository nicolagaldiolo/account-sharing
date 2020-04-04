<template>
  <div>
    <!-- Balance -->
    <balance />

    <!-- Transaction -->
    <div class="mt-5">
      <h2>Filtra la ricerca</h2>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="type">Movimenti</label>
          <select v-model="filters.type" @change="updateResults" id="type" class="form-control">
            <option value="" selected>Tutti i movimenti</option>
            <option value="INVOICE">Pagamenti</option>
            <option value="PAYOUTS">Prelievi</option>
            <option value="REFUNDS">Rimborsi</option>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="from">Periodo Da</label>
          <input v-model="filters.from" @change="updateResults" type="date" class="form-control" id="from">
        </div>
        <div class="form-group col-md-4">
          <label for="to">A</label>
          <input v-model="filters.to" @change="updateResults" type="date" class="form-control" id="to">
        </div>
      </div>

      <div v-if="filters.type !== 'PAYOUTS'" class="form-row bg-white p-4">
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="filters.subtype" @change="updateResults($event)" type="radio" name="direction" id="subtype1" value="">
          <label class="form-check-label" for="subtype1">Tutti</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="filters.subtype" @change="updateResults($event)" type="radio" name="direction" id="subtype2" value="OUTCOMING">
          <label class="form-check-label" for="subtype2">Uscite</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="filters.subtype" @change="updateResults($event)" type="radio" name="direction" id="subtype3" value="INCOMING">
          <label class="form-check-label" for="subtype3">Entrate</label>
        </div>
      </div>
      <div v-if="filters.type === 'REFUNDS'" class="form-row bg-white p-4 mt-3">
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="filters.refundtype" @change="updateResults($event)" type="radio" name="refundtype" id="refundtype1" value="">
          <label class="form-check-label" for="refundtype1">Tutti</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="filters.refundtype" @change="updateResults($event)" type="radio" name="refundtype" id="refundtype2" value="0">
          <label class="form-check-label" for="refundtype2">In attesa</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="filters.refundtype" @change="updateResults($event)" type="radio" name="refundtype" id="refundtype4" value="2">
          <label class="form-check-label" for="refundtype4">Rifiutati</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="filters.refundtype" @change="updateResults($event)" type="radio" name="refundtype" id="refundtype3" value="1">
          <label class="form-check-label" for="refundtype3">Completati</label>
        </div>
      </div>

      <div class="list-group mt-4">
        <div v-for="item in items" :key="item.id">
          <transaction :item="item"></transaction>
        </div>
      </div>

      <infinite-loading spinner="waveDots" :identifier="infiniteId" @infinite="infiniteHandler"></infinite-loading>
    </div>
  </div>
</template>

<script>
    import InfiniteLoading from 'vue-infinite-loading'
    import Transaction from '~/components/Transaction'
    import { helperMixin } from '~/mixins/helperMixin'
    import MoneyFormat from 'vue-money-format'
    import { mapGetters } from 'vuex'
    import Balance from '~/components/Balance'
    import { EventBus } from '~/app'

    export default {
  data: () => ({
    items: [],
    infiniteId: +new Date(),
    filters: {
      type: '',
      from: '',
      to: '',
      subtype: '',
      refundtype: '',
      page: 1
    },
    loading_state: {}
  }),

  mixins: [
    helperMixin
  ],

  components: {
    Balance,
    Transaction,
    InfiniteLoading,
    'money-format': MoneyFormat
  },

  computed: {
    ...mapGetters({
      dayRefundLimit: 'config/dayRefundLimit',
      authUser: 'auth/user',
      transactions: 'settings/transactions'
    })
  },

  mounted () {
    EventBus.$on('refresh-transaction', this.updateResults)
  },

  watch: {
    'filters.type': function (val) {
      this.filters.subtype = ''
    },
    'filters.subtype': function (val) {
      this.filters.refundtype = ''
    },
    transactions (data) {
      if (data.data && data.data.length) {
        this.items = data.data
        this.loading_state.loaded()
      }
      if (this.filters.page < data.meta.last_page) {
        this.filters.page = (data.meta.current_page + 1)
      } else {
        this.loading_state.complete()
      }
    }
  },

  methods: {
    updateResults (event) {
      this.filters.page = 1
      this.items = []
      this.infiniteId += 1
    },
    async infiniteHandler (state) {
      this.loading_state = state
      this.$store.dispatch('settings/fetchTransactions', this.getQueryString(this.filters))
    }
  }
}
</script>
