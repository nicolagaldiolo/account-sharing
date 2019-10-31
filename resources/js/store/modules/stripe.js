import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  customer: {}
}

// getters
export const getters = {
  customer: state => state.customer,
  userCard: state => []
}

// mutations
export const mutations = {
  [types.ADD_STRIPE_CARD_SUCCESS] (state, { card }) {
    state.customer.sources.data.push(card)
  },

  [types.ADD_STRIPE_CARD_FAILURE] (state, { card }) {
    //
  },

  [types.UPDATE_STRIPE_CUSTOMER_SUCCESS] (state, { customer }) {
    state.customer = customer
  },

  [types.UPDATE_STRIPE_CUSTOMER_FAILURE] (state, { customer }) {
    state.customer = {}
  }
}

// actions
export const actions = {

  async fetchCustomer ({ commit }) {
    try {
      const { data } = await axios.get('/api/settings/customer')
      commit(types.UPDATE_STRIPE_CUSTOMER_SUCCESS, { customer: data })
    } catch (e) {
      commit(types.UPDATE_STRIPE_CUSTOMER_FAILURE)
    }
  },

  async updateCustomer ({ commit }, sourceId) {
    try {
      const { data } = await axios.patch('/api/settings/customer', {
        default_source: sourceId
      })
      commit(types.UPDATE_STRIPE_CUSTOMER_SUCCESS, { customer: data })
    } catch (e) {
      commit(types.UPDATE_STRIPE_CUSTOMER_FAILURE)
    }
  },

  async removeCard ({ commit }, sourceId) {
    try {
      const { data } = await axios.delete('/api/settings/customer', { data: { id: sourceId } })
      commit(types.UPDATE_STRIPE_CUSTOMER_SUCCESS, { customer: data })
    } catch (e) {
      commit(types.UPDATE_STRIPE_CUSTOMER_FAILURE)
    }
  },

  async addCard ({ commit }, token) {
    try {
      const { data } = await axios.post('/api/settings/cards', token)
      commit(types.ADD_STRIPE_CARD_SUCCESS, { card: data })
    } catch (e) {
      commit(types.ADD_STRIPE_CARD_FAILURE, '')
    }
  }

}
