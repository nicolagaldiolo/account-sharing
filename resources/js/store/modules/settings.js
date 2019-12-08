import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  transactions: []
}

// getters
export const getters = {
  transactions: state => state.transactions
}

// mutations
export const mutations = {
  [types.FETCH_TRANSACTIONS_SUCCESS] (state, { transactions }) {
    state.transactions = transactions
  },

  [types.FETCH_TRANSACTIONS_FAILURE] (state) {
    state.transactions = []
  }
}

// actions
export const actions = {

  async fetchTransactions ({ commit }, params = {}) {
    try {
      var queryString = Object.keys(params).filter(key => {
        if (params[key]) {
          return key
        }
      }).map(key => key + '=' + params[key]).join('&')

      const { data } = await axios.get('/api/settings/transactions' + ((queryString) ? '?' + queryString : ''))
      commit(types.FETCH_TRANSACTIONS_SUCCESS, { transactions: data })
      return true
    } catch (e) {
      commit(types.FETCH_TRANSACTIONS_FAILURE)
      return false
    }
  }

}
