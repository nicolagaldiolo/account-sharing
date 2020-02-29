import Vue from 'vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import App from '~/components/App'
import Clipboard from 'v-clipboard'
import VueCurrencyInput from 'vue-currency-input'
import VModal from 'vue-js-modal'
import toasted from 'vue-toasted'

import Echo from 'laravel-echo'
import '~/plugins'
import '~/components'

Vue.config.productionTip = false
Vue.use(require('vue-moment')) // Use inside component as this.$moment() or as {{ item.created_at | moment("D/M/YYYY") }} in template
Vue.use(Clipboard)
Vue.use(VueCurrencyInput)
Vue.use(VModal)
Vue.use(toasted)
window.Pusher = require('pusher-js');

window.Echo = new Echo({
  authEndpoint: '/api/broadcasting/auth',
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY,
  cluster: process.env.MIX_PUSHER_APP_CLUSTER,
  encrypted: true
})


/* eslint-disable no-new */
new Vue({
  i18n,
  store,
  router,
  ...App
})
