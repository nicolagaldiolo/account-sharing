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
    }
  }
}
