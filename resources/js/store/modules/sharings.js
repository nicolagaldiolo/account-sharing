import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  sharings: [],
  sharing: {},
}

// getters
export const getters = {
  sharings: state => state.sharings,
  sharing: state => state.sharing
}

// mutations
export const mutations = {
  [types.FETCH_SHARINGS_SUCCESS] (state, { sharings }) {
    state.sharings = sharings
  },

  [types.FETCH_SHARINGS_FAILURE] (state) {
    state.sharings = []
  },

  [types.FETCH_SHARING_SUCCESS] (state, { sharing }) {
    state.sharing = sharing
  },

  [types.FETCH_SHARING_FAILURE] (state) {
    state.sharing = []
  }
}

// actions
export const actions = {
  async fetchSharings ({ commit }) {
    try {
      const { data } = await axios.get('/api/sharings')
      commit(types.FETCH_SHARINGS_SUCCESS, { sharings: data })
    } catch (e) {
      commit(types.FETCH_SHARINGS_FAILURE)
    }
  },
  async fetchSharing ({ commit }, id) {
    try {
      const { data } = await axios.get('/api/sharings/' + id)
      commit(types.FETCH_SHARING_SUCCESS, { sharing: data })
    } catch (e) {
      commit(types.FETCH_SHARING_FAILURE)
    }
  }
}
