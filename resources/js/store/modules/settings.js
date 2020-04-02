import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  transactions: {
    data: [],
    links: {},
    meta: {}
  },
  notifications: {
    data: [],
    links: {},
    meta: {}
  }
}

// getters
export const getters = {
  transactions: state => state.transactions,
  notifications: state => state.notifications
}

// mutations
export const mutations = {
  [types.FETCH_TRANSACTIONS_SUCCESS] (state, { transactions }) {
    state.transactions = Object.assign({}, {
      data: (transactions.meta.current_page === 1) ? transactions.data : state.transactions.data.concat(...transactions.data),
      links: transactions.links,
      meta: transactions.meta
    })
  },

  [types.FETCH_TRANSACTIONS_FAILURE] (state) {
    state.transactions = {}
  },

  [types.FETCH_NOTIFICATIONS_SUCCESS] (state, { notifications }) {
    state.notifications = Object.assign({}, {
      data: state.notifications.data.concat(...notifications.data),
      links: notifications.links,
      meta: notifications.meta
    })
  },

  [types.FETCH_NOTIFICATIONS_FAILURE] (state) {
    state.notifications = {}
  },

  [types.FETCH_READ_NOTIFICATIONS_SUCCESS] (state, { notification }) {
    const indexOfData = state.notifications.data.map(e => e.id).indexOf(notification.id)
    state.notifications.data.splice(indexOfData, 1)
    state.notifications = Object.assign({}, state.notifications)
  },

  [types.FETCH_READ_NOTIFICATIONS_FAILURE] (state) {
    //
  }

}

// actions
export const actions = {

  async fetchTransactions ({ commit }, params) {
    try {
      const { data } = await axios.get('/api/settings/transactions' + params)
      commit(types.FETCH_TRANSACTIONS_SUCCESS, { transactions: data })
    } catch (e) {
      commit(types.FETCH_TRANSACTIONS_FAILURE)
    }
  },

  async fetchNotifications ({ commit }, params) {
    try {
      const { data } = await axios.get('/api/settings/notifications' + params)
      commit(types.FETCH_NOTIFICATIONS_SUCCESS, { notifications: data })
    } catch (e) {
      commit(types.FETCH_NOTIFICATIONS_FAILURE)
    }
  },

  async readNotification ({ commit }, id) {
    try {
      const { data } = await axios.patch(`/api/settings/notifications/${id}`)
      commit(types.FETCH_READ_NOTIFICATIONS_SUCCESS, { notification: data })
    } catch (e) {
      commit(types.FETCH_READ_NOTIFICATIONS_FAILURE)
    }
  }

}
