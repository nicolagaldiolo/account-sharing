<template>
  <div class="list-group-item list-group-item-action flex-column align-items-start mb-3">
    <div class="d-flex">
      <div class="mr-3" style="font-size: 2rem;">
        <fa :icon="transactionFormatted.icon" fixed-width/>
      </div>
      <div class="w-100">
        <div class="d-flex w-100 justify-content-between">
          <div>
            <h5 class="mb-1">{{ transactionFormatted.title }}</h5>
            <p class="m-0">{{item.title}}</p>
            <p class="m-0" v-if="item.last4">{{transactionFormatted.paymentLabel}}: <strong>{{item.last4}}</strong></p>
            <p class="m-0">{{transactionFormatted.paymentDirectionLabel}}: <strong>{{item.user}}</strong></p>
          </div>
          <div class="d-flex flex-column align-items-end">
            <small>{{ item.created_at | moment("D/M/YYYY") }}</small>
            <div class="d-flex money_data" :class="transactionFormatted.colorClass">
              {{item.direction === 'INCOMING' ? "+" : "-"}}
              <money-format :value="(item.total.value * 1)" locale="IT" :currency-code='item.total.currency' :subunit-value=false :hide-subunits=false></money-format>
            </div>
            <a v-if="item.refundable" href="#" class="btn btn-primary" @click.prevent="refundRequest">Richiedi rimborso <br><small>Entro il {{ item.refundable.within | moment("D/M/YYYY") }}</small></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
    import MoneyFormat from 'vue-money-format'
    import VButton from "./Button";
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
            VButton,
            'money-format': MoneyFormat
        },

        data () {
            return {
            }
        },

        computed: {
            transactionFormatted: function () {
                var title2,icon,paymentLabel,paymentDirectionLabel

                switch (this.item.type) {
                    case 'INVOICE':
                        title2 = (this.item.direction === 'INCOMING') ? 'Quota ricevuta' : 'Quota inviata'
                        icon = 'credit-card'
                        paymentLabel = 'Su carta di credito'
                        paymentDirectionLabel = (this.item.direction === 'INCOMING') ? 'Inviato da' : 'Inviato a'
                        break
                    case 'REFUND':
                        title2 = (this.item.direction === 'INCOMING') ? 'Rimborso ricevuto' : 'Rimborso inviato'
                        icon = 'coins'
                        paymentLabel = 'Su carta di credito'
                        paymentDirectionLabel = (this.item.direction === 'INCOMING') ? 'Inviato da' : 'Inviato a'
                        break
                    case 'PAYOUT':
                        title2 = 'Prelievo'
                        icon = 'wallet'
                        paymentLabel = 'Su conto corrente'
                        paymentDirectionLabel = 'Inviato a'
                        break
                }

                return {
                    colorClass: (this.item.direction === 'INCOMING') ? 'green' : 'red',
                    title: title2,
                    icon: icon,
                    paymentLabel: paymentLabel,
                    paymentDirectionLabel: paymentDirectionLabel
                }

            }

        },

        methods: {
            refundRequest () {
              axios.post('/api/settings/refunds', {
                  payment_intent: this.item.refundable.payment_intent
              }).then(({ data }) => {
                  console.log(data);
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
