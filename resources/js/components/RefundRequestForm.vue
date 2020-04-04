<template>
  <div>
    <h4>Motivazioni del rimborso</h4>
    <div class="form-group mt-3">
      <textarea v-model="reason" class="form-control" rows="6" :maxlength="maxLength" placeholder="Descrivi perchè stai richiedendo un rimborso"></textarea>
      <small :class="{ red: maxLengthReached }">{{textareaLength}}/{{maxLength}} caratteri</small>
    </div>
    <v-link
      :class="[{ 'disabled': reason.length <= 0 }, 'btn-lg btn-block']"
      type="primary"
      :action="refundRequest"
    >Conferma</v-link>
  </div>
</template>

<script>
    import VLink from './Link'
    import { EventBus } from '~/app'
    import Swal from 'sweetalert2'
    import axios from 'axios'
    export default {
      name: 'RefundRequestForm',
      components: {
        VLink
      },
      props: {
        paymentIntent: {
          type: String,
          default: ''
        }
      },

      data () {
        return {
          maxLength: 500,
          reason: ''
        }
      },

      computed: {
        maxLengthReached () {
          return this.reason.length === this.maxLength
        },
        textareaLength () {
          return this.reason.length
        }
      },

      methods: {
        refundRequest () {
          axios.post('/api/settings/refund', {
            payment_intent: this.paymentIntent,
            reason: this.reason
          }).then(() => {
            this.$emit('close')
            EventBus.$emit('refresh-transaction')
            Swal.fire({
              type: 'success',
              title: 'Richiesta di rimborso effettuata',
              text: 'La tua richiesta sarà valutata dal nostro staff'
            })
          })
        }
      }
    }
</script>

<style scoped>
  .red{
    color: #fb4c68;
  }
</style>
