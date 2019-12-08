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
          <div class="text-right">
            <div class="d-flex money_data" :class="transactionFormatted.colorClass">
              {{item.direction === 'INCOMING' ? "+" : "-"}}
              <money-format :value="item.total.value" locale="IT" :currency-code='item.total.currency' :subunit-value=false :hide-subunits=false></money-format>
            </div>
            <small>{{ item.created_at | moment("D/M/YYYY") }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
    import MoneyFormat from 'vue-money-format'
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
            }
        },

        computed: {
            transactionFormatted: function () {
                var title2,icon, paymentLabel,paymentDirectionLabel

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
                    colorClass : (this.item.direction === 'INCOMING') ? 'green' : 'red',
                    title : title2,
                    icon : icon,
                    paymentLabel : paymentLabel,
                    paymentDirectionLabel : paymentDirectionLabel
                }

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
