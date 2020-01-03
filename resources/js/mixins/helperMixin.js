export const helperMixin = {
  methods: {
    getQueryString (params) {
      let queryString = Object.keys(params).filter(key => {
        if (params[key]) {
          return key
        }
      }).map(key => key + '=' + params[key]).join('&')

      return (queryString) ? '?' + queryString : ''
    }
  }
}
