import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  sharings: [],
  sharing: {},
  sharingRequests: []
}

// getters
export const getters = {
  sharings: state => state.sharings,
  sharing: state => state.sharing,
  sharingRequests: state => state.sharingRequests
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
  },

  [types.FETCH_SHARING_REQUESTS_SUCCESS] (state, { sharingRequests }) {
    state.sharingRequests = sharingRequests
  },

  [types.FETCH_SHARING_REQUESTS_FAILURE] (state) {
    state.sharingRequests = []
  }
}

// actions
export const actions = {
  async fetchSharings ({ commit }, type = '') {
    try {
      let param = (type.length > 0) ? `?type=${type}` : '';
      const { data } = await axios.get('/api/sharings' + param);
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
  },
  async fetchSharingRequests ({ commit }) {
    try {
      const { data } = await axios.get('/api/sharing-requests/');
      commit(types.FETCH_SHARING_REQUESTS_SUCCESS, { sharingRequests: data })
    } catch (e) {
      commit(types.FETCH_SHARING_REQUESTS_FAILURE)
    }
  }
}
