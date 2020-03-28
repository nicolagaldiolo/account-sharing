<template>
  <div class="list-group-item list-group-item-action flex-column align-items-start mb-3">
    <div class="d-flex">
      <div class="mr-3" style="font-size: 2rem;">
        <fa :icon="icon" fixed-width/>
      </div>
      <div class="w-100">
        <div class="d-flex w-100 justify-content-between align-items-center">
          <div>
            <h5 class="mb-1">{{ title }}</h5>
            <p class="m-0">{{ item.service }}</p>
            <p class="m-0" v-if="item.last4">{{ paymentLabel }}: <strong>{{ item.last4 }}</strong></p>
            <p class="m-0">{{ paymentDirectionLabel }} <strong>{{ item.user }}</strong></p>
          </div>
          <a v-if="cancelRefund" href="#" class="btn btn-secondary" @click.prevent="cancelRefundRequest">Annulla rimborso</a>
          <a v-if="item.refundable" href="#" class="btn btn-primary" @click.prevent="refundRequest">Richiedi rimborso <br><small>Entro il {{ item.refundable.within | moment("D/M/YYYY") }}</small></a>
          <div class="d-flex flex-column align-items-end">
            <small>{{ item.created_at | moment("D/M/YYYY") }}</small>
            <div class="d-flex money_data" :class="colorClass">
              {{sign}}
              <money-format :value="(item.total.value * 1)" locale="IT" :currency-code='item.total.currency' :subunit-value=false :hide-subunits=false></money-format>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MoneyFormat from 'vue-money-format'
import axios from 'axios'

export default {
  name: 'Transaction',
  props: {
    item: {
      type: Object,
      default: null
    }
  },
  components: {
    'money-format': MoneyFormat
  },

  data () {
    return {
      title: null,
      sign: null,
      colorClass: null,
      icon: null,
      paymentLabel: null,
      paymentDirectionLabel: null,
      cancelRefund: null
    }
  },

  created () {
    switch (this.item.type) {
      case 'INVOICE':
        this.title = (this.item.refunded)
          ? (this.item.direction === 'INCOMING') ? 'Quota annullata' : 'Quota rimborsata'
          : (this.item.direction === 'INCOMING') ? 'Quota ricevuta' : 'Quota inviata'
        this.sign = (this.item.refunded) ? null : this.item.direction === 'INCOMING' ? '+' : '-'
        this.colorClass = (this.item.refunded) ? null : (this.item.direction === 'INCOMING') ? 'green' : 'red'
        this.icon = 'credit-card'
        this.paymentLabel = 'Carta di credito'
        this.paymentDirectionLabel = (this.item.direction === 'INCOMING') ? 'Da:' : 'A:'
        break
      case 'REFUND':
        let refundStatus,
          cancelRefundBtn
        switch (this.item.status) {
          case 0:
            refundStatus = 'Rimborso in attesa'
            cancelRefundBtn = true
            break
          case 1:
            refundStatus = 'Rimborso completato'
            cancelRefundBtn = false
            break
          case 2:
            refundStatus = 'Rimborso rifiutato'
            cancelRefundBtn = false
            break
        }
        this.title = refundStatus
        this.cancelRefund = cancelRefundBtn
        this.icon = 'coins'
        this.paymentLabel = 'Carta di credito'
        this.paymentDirectionLabel = (this.item.direction === 'INCOMING') ? 'Da:' : 'A:'
        break
      case 'PAYOUT':
        this.title = 'Prelievo'
        this.sign = this.item.direction === 'INCOMING' ? '+' : '-'
        this.colorClass = (this.item.direction === 'INCOMING') ? 'green' : 'red'
        this.icon = 'wallet'
        this.paymentLabel = 'Conto corrente'
        this.paymentDirectionLabel = 'A:'
        break
    }
  },
  methods: {
    cancelRefundRequest () {
      axios.delete(`/api/settings/refund/${this.item.id}`).then(({ data }) => {

        /*
        if (data.data.length) {
            this.lists.push(...data.data)
            $state.loaded()
        }

        if (this.search_fields.page < data.meta.last_page) {
            this.search_fields.page += 1
        } else {
            $state.complete()
        }

         */
      })
    },

    refundRequest () {
      axios.post('/api/settings/refund', {
        payment_intent: this.item.refundable.payment_intent
      }).then(({ data }) => {

        /*
        if (data.data.length) {
            this.lists.push(...data.data)
            $state.loaded()
        }

        if (this.search_fields.page < data.meta.last_page) {
            this.search_fields.page += 1
        } else {
            $state.complete()
        }

         */
      })
    }
  }
}
</script>

<style scoped>
  .money_data{
    font-size: 1.5rem;
  }
  .green{
    color: #4ec769;
  }
  .red{
    color: #fb4c68;
  }
</style>
