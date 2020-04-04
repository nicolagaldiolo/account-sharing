import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  sharings: {
    data: [],
    links: {},
    meta: {}
  },
  refunds: {
    data: [],
    links: {},
    meta: {}
  },
}

// getters
export const getters = {
  sharings: state => state.sharings,
  refunds: state => state.refunds,
}

// mutations
export const mutations = {
  [types.FETCH_ADMIN_SHARINGS_SUCCESS] (state, { sharings }) {
    const obj = {
      data: (sharings.meta.current_page === 1) ? sharings.data : state.sharings.data.concat(...sharings.data),
      links: sharings.links,
      meta: sharings.meta
    }

    state.sharings = Object.assign({}, obj)
  },

  [types.FETCH_ADMIN_SHARINGS_FAILURE] (state) {
    state.sharings = {}
  },

  [types.FETCH_ADMIN_REFUNDS_SUCCESS] (state, { refunds }) {
    state.refunds = Object.assign({}, {
      data: (refunds.meta.current_page === 1) ? refunds.data : state.refunds.data.concat(...refunds.data),
      links: refunds.links,
      meta: refunds.meta
    })
  },

  [types.FETCH_ADMIN_REFUNDS_FAILURE] (state) {
    state.refunds = {}
  },
}

// actions
export const actions = {
  async fetchSharings ({ commit }, params) {
    try {
      const { data } = await axios.get('/api/admin/sharings' + params)
      commit(types.FETCH_ADMIN_SHARINGS_SUCCESS, { sharings: data })
    } catch (e) {
      commit(types.FETCH_ADMIN_SHARINGS_FAILURE)
    }
  },

  async fetchRefunds ({ commit }, params) {
    try {
      const { data } = await axios.get('/api/admin/refunds' + params)
      commit(types.FETCH_ADMIN_REFUNDS_SUCCESS, { refunds: data })
    } catch (e) {
      commit(types.FETCH_ADMIN_REFUNDS_FAILURE)
    }
  },
}
