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
            <p class="m-0">{{ item.obj.service }}</p>
            <p class="m-0" v-if="item.last4">{{ paymentLabel }}: <strong>{{ item.obj.last4 }}</strong></p>
            <p class="m-0">{{ paymentDirectionLabel }} <strong>{{ item.obj.user }}</strong></p>
          </div>
          <a v-if="cancelRefund" href="#" class="btn btn-secondary" @click.prevent="cancelRefundRequest">Annulla richiesta</a>
          <a v-if="refundable" href="#" class="btn btn-primary" @click.prevent="openRefundModal">Richiedi rimborso <br><small>Entro il {{ item.obj.refundable_within | moment("D/M/YYYY") }}</small></a>
          <div class="d-flex flex-column align-items-end">
            <small>{{ item.obj.created_at | moment("D/M/YYYY") }}</small>
            <div class="d-flex money_data" :class="colorClass">
              {{sign}}
              <money-format :value="(item.obj.total.value * 1)" locale="IT" :currency-code='item.obj.total.currency' :subunit-value=false :hide-subunits=false></money-format>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { helperMixin } from '~/mixins/helperMixin'
import MoneyFormat from 'vue-money-format'
import axios from 'axios'
import Swal from 'sweetalert2'
import RefundRequestForm from './RefundRequestForm'
import { EventBus } from '../app'

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

  mixins: [
    helperMixin
  ],

  data () {
    return {
      title: null,
      sign: null,
      colorClass: null,
      icon: null,
      paymentLabel: null,
      paymentDirectionLabel: null,
      cancelRefund: null,
      refundable: this.item.obj.refundable
    }
  },

  created () {
    switch (this.item.type) {
      case 'INVOICE':
        this.title = (this.item.obj.refunded)
          ? (this.item.obj.direction === 'INCOMING') ? 'Quota annullata' : 'Quota rimborsata'
          : (this.item.obj.direction === 'INCOMING') ? 'Quota ricevuta' : 'Quota inviata'
        this.sign = (this.item.obj.refunded) ? null : this.item.obj.direction === 'INCOMING' ? '+' : '-'
        this.colorClass = (this.item.obj.refunded) ? null : (this.item.obj.direction === 'INCOMING') ? 'green' : 'red'
        this.icon = 'credit-card'
        this.paymentLabel = 'Carta di credito'
        this.paymentDirectionLabel = (this.item.obj.direction === 'INCOMING') ? 'Da:' : 'A:'
        break
      case 'REFUND':
        let refundStatus,
          cancelRefundBtn
        switch (this.item.obj.status) {
          case 0:
            refundStatus = 'Rimborso in attesa'
            cancelRefundBtn = (true && this.item.obj.direction === 'INCOMING')
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
        this.paymentDirectionLabel = (this.item.obj.direction === 'INCOMING') ? 'Da:' : 'A:'
        break
      case 'PAYOUT':
        this.title = 'Prelievo'
        this.sign = this.item.obj.direction === 'INCOMING' ? '+' : '-'
        this.colorClass = (this.item.obj.direction === 'INCOMING') ? 'green' : 'red'
        this.icon = 'wallet'
        this.paymentLabel = 'Conto corrente'
        this.paymentDirectionLabel = 'A:'
        break
    }
  },
  methods: {
    cancelRefundRequest () {
      if (this.item.type === 'REFUND') {
        axios.delete(`/api/settings/refund/${this.item.obj.id}`).then(() => {
          EventBus.$emit('refresh-transaction')
          Swal.fire({
            type: 'success',
            title: 'Richiesta annullata con successo',
            text: 'La richiesta di rimborso è stata annullata correttamente'
          })
        })
      }
    },

    openRefundModal () {
      this.$modal.show(
        RefundRequestForm,
        {
          paymentIntent: this.item.obj.payment_intent
        },
        {
          adaptive: true,
          maxWidth: 700,
          height: 'auto'
        },
        ''
      )
    }
  },

  watch: {
    item (data) {
      this.refundable = data.obj.refundable
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
