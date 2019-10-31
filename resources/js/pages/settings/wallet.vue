<template>
  <div>
    <card v-if="customer.sources.data.length" title="Credit Card">
      <div v-for="card in customer.sources.data" :key="card.id">
        <h5 class="card-title">{{card.brand}}</h5>
        <strong>{{card.last4}}</strong>
        <span>{{card.exp_month}}</span>
        <span>{{card.exp_year}}</span>
        <span v-if="card.id === customer.default_source" class="badge badge-pill badge-primary">Default</span>
        <a v-else class="btn btn-link" href="#" @click.prevent="updateCustomer(card.id)">Rendi default</a>
        <a v-if="customer.sources.data.length > 1" class="btn btn-link" href="#" @click.prevent="removeCard(card.id)">Elimina carta</a>
      </div>
      <template v-slot:footer>
        <div>
          <div>
            <label for="card-element">
              Credit or debit card
            </label>
            <div id="card-element">
              <!-- A Stripe Element will be inserted here. -->
            </div>

            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert"></div>
          </div>

          <button @click.prevent="addCard">Submit Payment</button>
          
        </div>
        
      </template>
    </card>

  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    scrollToTop: false,

  data: () => ({
    style: {
      base: {
        color: '#32325d',
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
          color: '#aab7c4'
        }
      },
      invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
      }
    },
    mytestcard: '',
    stripe : Stripe(window.config.stripeKey)
  }),

  computed: mapGetters({
    customer: 'stripe/customer'
  }),

  created () {
    this.$store.dispatch('stripe/fetchCustomer')


  },

  mounted () {
    // Create a Stripe client.
    

    // Create an instance of Elements.
    var element = this.stripe.elements();

    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)
    

    // Create an instance of the card Element.
    this.mytestcard = element.create('card', {style: this.style});

    // Add an instance of the card Element into the `card-element` <div>.
    
    setTimeout(()=>this.mytestcard.mount('#card-element') , 2000);
    

    // Handle real-time validation errors from the card Element.
    this.mytestcard.addEventListener('change', function(event) {
      var displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });


  },

  methods: {
    updateCustomer (id) {
      this.$store.dispatch('stripe/updateCustomer', id)
    },

    removeCard (cardId) {
        this.$store.dispatch('stripe/removeCard', cardId)
    },
    
    addCard () {
      this.stripe.createPaymentMethod('card', this.mytestcard).then(data => {
        this.$store.dispatch('stripe/addCard', data.paymentMethod)
        //this.$refs.card.clear()
      });
    }
  }
}
</script>

<style>

  .StripeElement {
    box-sizing: border-box;

    height: 40px;

    padding: 10px 12px;

    border: 1px solid transparent;
    border-radius: 4px;
    background-color: white;

    box-shadow: 0 1px 3px 0 #e6ebf1;
    -webkit-transition: box-shadow 150ms ease;
    transition: box-shadow 150ms ease;
  }

  .StripeElement--focus {
    box-shadow: 0 1px 3px 0 #cfd7df;
  }

  .StripeElement--invalid {
    border-color: #fa755a;
  }

  .StripeElement--webkit-autofill {
    background-color: #fefde5 !important;
  }

</style>
