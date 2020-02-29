import Swal from "sweetalert2";
<template>
  <div id="app">
    <loading ref="loading" />
    <transition name="page" mode="out-in">
      <component :is="layout" v-if="layout" />
    </transition>
  </div>
</template>

<script>
import Loading from './Loading'
import { helperMixin } from '~/mixins/helperMixin'
import { mapGetters } from 'vuex'

// Load layout components dynamically.
const requireContext = require.context('~/layouts', false, /.*\.vue$/)

const layouts = requireContext.keys()
  .map(file =>
    [file.replace(/(^.\/)|(\.vue$)/g, ''), requireContext(file)]
  )
  .reduce((components, [name, component]) => {
    components[name] = component.default || component
    return components
  }, {})

export default {
  el: '#app',

  components: {
    Loading
  },

  mixins: [ helperMixin ],

  data: () => ({
    layout: null,
    defaultLayout: 'default'
  }),

  metaInfo () {
    const { appName } = window.config

    return {
      title: appName,
      titleTemplate: `%s · ${appName}`
    }
  },

  computed: mapGetters({
    user: 'auth/user'
  }),

  watch: {
    user: function (user) {
      if (user.id) {
        window.Echo.private('App.User.' + user.id).notification((notification) => {
          switch (notification.type) {
            case 'App\\Notifications\\CredentialUpdated' :
              if (notification.data.sharing.id === parseInt(this.$route.params.sharing_id)) {
                this.$store.dispatch('sharings/updateSharing', { sharing: notification.data.sharing })
                this.$store.dispatch('sharings/fetchCredentials', this.$route.params.sharing_id)
                if (!notification.data.sharing.multiaccount || notification.data.recipient.id === this.user.id) {
                  this.showNotificationToast(notification.desc, notification.id)
                }
              }
              break
            case 'App\\Notifications\\CredentialConfirmed' :
              if (notification.data.sharing.id === parseInt(this.$route.params.sharing_id)) {
                this.$store.dispatch('sharings/updateSharing', { sharing: notification.data.sharing })
                // Show notification only for owner not others members
                if (notification.data.sharing.owner.id === user.id) {
                  if (notification.data.action === 1) {
                    this.showNotificationToast(notification.desc, notification.id)
                  } else if (notification.data.action === 2) {
                    this.showNotificationToast(notification.desc, notification.id)
                  }
                }
              }
              break
            default :
              this.showNotificationToast(notification.desc, notification.id)
          }
        })
      }
    }
  },

  mounted () {
    this.$loading = this.$refs.loading
  },

  methods: {
    /**
     * Set the application layout.
     *
     * @param {String} layout
     */
    setLayout (layout) {
      if (!layout || !layouts[layout]) {
        layout = this.defaultLayout
      }

      this.layout = layouts[layout]
    }
  }
}
</script>
