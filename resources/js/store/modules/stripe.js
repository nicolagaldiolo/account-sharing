import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  paymentmethods: {}
}

// getters
export const getters = {
  paymentmethods: state => state.paymentmethods
}

// mutations
export const mutations = {

  [types.FETCH_STRIPE_PAYMENTMETHODS_SUCCESS] (state, { paymentmethods }) {
    state.paymentmethods = paymentmethods
  },

  [types.FETCH_STRIPE_PAYMENTMETHODS_FAILURE] (state) {
    state.paymentmethods = {}
  }
}

// actions
export const actions = {

  async fetchPaymentMethods ({ commit }) {
    try {
      const { data } = await axios.get('/api/settings/paymentmethods')
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_SUCCESS, { paymentmethods: data })
    } catch (e) {
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_FAILURE)
    }
  },

  async removePaymentMethod ({ commit }, paymentMethodId) {
    try {
      const { data } = await axios.delete('/api/settings/paymentmethods', {
        data: {
          payment_method: paymentMethodId
        }
      })
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_SUCCESS, { paymentmethods: data })
    } catch (e) {
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_FAILURE)
    }
  },

  async addPaymentMethod ({ commit }, paymentMethod) {

    try {
      const { data } = await axios.post('/api/settings/paymentmethods', {
        payment_method: paymentMethod
      })
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_SUCCESS, { paymentmethods: data })
    } catch (e) {
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_FAILURE)
    }

  },

  async setDefaultPaymentMethods ({ commit }, paymentMethodId) {
    try {
      const { data } = await axios.patch('/api/settings/paymentmethods', {
        payment_method: paymentMethodId
      })
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_SUCCESS, { paymentmethods: data })
    } catch (e) {
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_FAILURE)
    }
  }

}
