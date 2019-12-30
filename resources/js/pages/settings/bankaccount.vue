<template>
  <div>
    <card :title="$t('your_info')">
      <form @submit.prevent="addBankAccount">
        <div v-if="routingNumberRequired" class="form-row">
          <label>Routing Number</label>
          <input v-model="routingNumber" type="text"/>
        </div>
        <div class="form-row">
          <label for="account-number">Account Number</label>
          <input v-model="accountNumber" type="text" id="account-number"/>
        </div>
        <button type="submit">Submit</button>
      </form>
    </card>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'

export default {
  scrollToTop: false,
  metaInfo () {
    return {
      title: this.$t('settings')
    }
  },

  data: () => ({
    routingNumber: '',
    accountNumber: '',
    stripe: window.Stripe(window.config.stripeKey)
  }),

  computed: {
    ...mapGetters({
      user: 'auth/user'
    }),
    routingNumberRequired () {
      return this.user.country === 'gb'
    }
  },

  methods: {
    // https://stackoverflow.com/questions/36419252/how-to-provide-external-account-parameter-while-creating-managed-account-in-stri/36421220
    addBankAccount () {
      const bankAccountParams = {
        country: this.user.country,
        currency: this.user.currency,
        account_number: this.accountNumber,
        account_holder_name: this.user.username,
        account_holder_type: 'individual'
      }

      if (this.routingNumberRequired) {
        bankAccountParams['routing_number'] = this.routingNumber
      }

      this.stripe.createToken('bank_account', bankAccountParams).then(result => {
        if (result.error) {
          // Display error.message in your UI.
          alert(result.error);
        } else {
          axios.post('/api/settings/bank-account', {
            bank_account: result.token.id
          }).then((response) => {
            console.log(response)
          })
        }
      })

    }
  }
}
</script>
