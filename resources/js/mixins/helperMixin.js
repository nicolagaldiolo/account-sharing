export const helperMixin = {
  methods: {
    getQueryString (params) {
      let queryString = Object.keys(params).filter(key => {
        if (params[key]) {
          return key
        }
      }).map(key => key + '=' + params[key]).join('&')

      return (queryString) ? '?' + queryString : ''
    },

    calcCredentialStatus (credentialStatus) {
      let status;
      switch (credentialStatus) {
        case 1 :
          status = {
            class: 'c-green',
            state: 'Confermate'
          }
          break
        case 2 :
          status = {
            class: 'c-red',
            state: 'Errate'
          }
          break
        case 0 :
        default :
          status = {
            class: 'c-default',
            state: 'Non confermate'
          }
          break
      }
      return status
    },

    showNotificationToast (message = 'notification_title', id) {
      const options = {
        action: {
          text: 'Chiudi',
          onClick: (e, toastObject) => {
            if (id) this.$store.dispatch('settings/readNotification', id)
            toastObject.goAway(0)
          }
        },
        duration: 6000
      }
      this.$toasted.show(message, options)
    },

    showSimpleToast (message) {
      const options = {
        action: {
          text: 'Chiudi',
          onClick: (e, toastObject) => {
            toastObject.goAway(0)
          }
        },
        duration: 6000
      }
      this.$toasted.show(message, options)
    }
  }
}
