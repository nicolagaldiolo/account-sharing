import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  paymentmethods: [],
  defaultPaymentmethod: ''
}

// getters
export const getters = {
  paymentmethods: state => state.paymentmethods,
  defaultPaymentmethod: state => state.defaultPaymentmethod
}

// mutations
export const mutations = {

  [types.FETCH_STRIPE_PAYMENTMETHODS_SUCCESS] (state, { paymentmethods }) {
    state.paymentmethods = paymentmethods
  },

  [types.FETCH_STRIPE_PAYMENTMETHODS_FAILURE] (state) {
    state.paymentmethods = []
  },

  [types.ADD_STRIPE_PAYMENTMETHOD_SUCCESS] (state, { paymentmethods }) {
    state.paymentmethods.push(paymentmethods)
  },

  [types.ADD_STRIPE_PAYMENTMETHOD_FAILURE] (state) {
    //
  },
  [types.REMOVE_STRIPE_PAYMENTMETHOD_SUCCESS] (state, { paymentmethods }) {
    state.paymentmethods = state.paymentmethods.filter(item => item.id !== paymentmethods.id)
  },

  [types.REMOVE_STRIPE_PAYMENTMETHOD_FAILURE] (state) {
    //
  },

  [types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_SUCCESS] (state, { defaultPaymentmethod }) {
    state.defaultPaymentmethod = defaultPaymentmethod
  },

  [types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_FAILURE] (state) {
    //
  }
}

// actions
export const actions = {

  async fetchPaymentMethods ({ commit }) {
    try {
      const { data } = await axios.get('/api/settings/paymentmethods')
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_SUCCESS, { paymentmethods: data.data })
      commit(types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_SUCCESS, { defaultPaymentmethod: data.meta.defaultPaymentMethod })
      return true
    } catch (e) {
      commit(types.FETCH_STRIPE_PAYMENTMETHODS_FAILURE)
      commit(types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_FAILURE)
      return false
    }
  },

  async addPaymentMethod ({ commit }, paymentMethod) {
    try {
      const { data } = await axios.post('/api/settings/paymentmethods', {
        payment_method: paymentMethod
      })
      commit(types.ADD_STRIPE_PAYMENTMETHOD_SUCCESS, { paymentmethods: data.data })
      commit(types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_SUCCESS, { defaultPaymentmethod: data.meta.defaultPaymentMethod })
      return true
    } catch (e) {
      commit(types.ADD_STRIPE_PAYMENTMETHOD_FAILURE)
      commit(types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_FAILURE)
      return false
    }
  },

  async removePaymentMethod ({ commit }, paymentMethodId) {
    try {
      const { data } = await axios.delete('/api/settings/paymentmethods', {
        data: {
          payment_method: paymentMethodId
        }
      })
      commit(types.REMOVE_STRIPE_PAYMENTMETHOD_SUCCESS, { paymentmethods: data.data })
      return true
    } catch (e) {
      commit(types.REMOVE_STRIPE_PAYMENTMETHOD_FAILURE)
      return false
    }
  },

  async setDefaultPaymentMethod ({ commit }, paymentMethodId) {
    try {
      const { data } = await axios.patch('/api/settings/paymentmethods', {
        payment_method: paymentMethodId
      })
      commit(types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_SUCCESS, { defaultPaymentmethod: data.data.defaultPaymentMethod })
      return true
    } catch (e) {
      commit(types.UPDATE_STRIPE_PAYMENTDEFAULTMETHOD_FAILURE)
      return false
    }
  }

}
