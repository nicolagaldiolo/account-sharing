<template>
  <div>
    <div :class="[{ 'selected': paymentmethod.id === defaultPaymentmethod }, 'credit-card']" class="credit-card">
      <span v-if="paymentmethod.id === defaultPaymentmethod" class="badge badge-pill badge-primary">Default</span>
      <div class="card-company"></div>
      <div class="card-number">
        <div class="digit-group">XXXX XXXX XXXX {{paymentmethod.card.last4}}</div>
      </div>
      <div class="card-expire">
        <span class="card-text">Expires</span> {{paymentmethod.card.exp_month}}/{{paymentmethod.card.exp_year}}
      </div>
      <div class="card-holder">{{paymentmethod.card.brand}}</div>
    </div>
    <div v-if="paymentmethod.id !== defaultPaymentmethod" class="text-center">
      <v-link class="btn btn-sm btn-secondary" :data-action=paymentmethod.id :loading="defaultStatus" :action="setDefaultPaymentMethods">Default</v-link>
      <v-link class="btn btn-sm btn-secondary" v-if="eraseable" :data-action=paymentmethod.id :loading="removeStatus" :action="removePaymentMethod">Elimina</v-link>
    </div>
  </div>
</template>

<script>
  import Swal from 'sweetalert2'

  export default {
    data: () => ({
      defaultStatus: false,
      removeStatus: false
    }),

    props: {
      paymentmethod: { type: Object, default: null },
      defaultPaymentmethod: { type: String, default: '' },
      eraseable: { type: Boolean, default: true }
    },

    methods: {
      setDefaultPaymentMethods (event) {
        this.defaultStatus = true
        const id = event.target.getAttribute('data-action')
        this.$store.dispatch('stripe/setDefaultPaymentMethod', id).then((result) => {
          if (result) {
            Swal.fire({
              type: 'success',
              title: 'Metodo di pagamento impostato come default'
            })
          } else {
            Swal.fire({
              type: 'error',
              title: 'Errore, si prega di riprovare piu tardi'
            })
          }
          this.defaultStatus = false
        })
      },

      removePaymentMethod (event) {
        this.removeStatus = true
        const id = event.target.getAttribute('data-action')
        this.$store.dispatch('stripe/removePaymentMethod', id).then((result) => {
          if (result) {
            Swal.fire({
              type: 'success',
              title: 'Metodo di pagamento eliminato con successo'
            })
          } else {
            Swal.fire({
              type: 'error',
              title: 'Errore, si prega di riprovare piu tardi'
            })
          }
          this.removeStatus = false
        })
      }
    },
  }
</script>
<style scoped>
  .credit-card {
    margin: 10px;
    display: flex;
    flex-shrink: 0;
    background: #fff;
    position: relative;
    flex-direction: column;
    border-radius: 6px;
    width: 240px;
    height: 151px;
    border: 1px solid #999;
    opacity: 0.5;
  }

  .credit-card.selected {
    opacity: 1;
  }

  .credit-card .badge{
    position: absolute;
    top: 25px;
    z-index: 1;
    right: 10px;
  }

  .card-type,
  .card-company {
    text-align: right;
    text-transform: uppercase;
    margin: 10px;
    color: rgba(0, 0, 40, 0.5);
  }
  .card-company {
    font-weight: normal;
    padding: 18px;
    background: #000;
    margin: 15px 0;
  }
  .card-number,
  .card-expire,
  .card-holder {
    display: flex;
    justify-content: center;
    color: #000;
  }
  .card-number .digit-group,
  .card-expire .digit-group,
  .card-holder .digit-group {
    margin: 5px;
  }
  .card-expire{
    font-size: 16px;
    justify-content: flex-start;
    padding: 0 20px;
  }
  .card-holder {
    justify-content: flex-end;
    padding: 0;
    position: absolute;
    right: 10px;
    bottom: 10px;
  }
  .card-expire .card-text,
  .card-holder .card-text {
    font-size: 12px;
    font-family: sans-serif;
    color: #000;
    color: rgba(0, 0, 40, 0.5);
    text-shadow: none;
    margin: 3px;
    margin-left: 10px;
  }
  .card-type {
    margin-top: auto;
    font-size: 14px;
  }
</style>
