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
    console.log("Popolo lo stato");
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
  },

  [types.UPDATE_SHARING] (state, { sharing }) {
    console.log("Chiamo questo metodo");
    state.sharing = sharing
    console.log("Ho aggiornato lo stato");
  },

  [types.SYNC_SHARINGS] (state, { sharing }) {
    let data = sharing[0]
    let indexOfData = state.sharings.map(e => e.id).indexOf(data.id)
    let sharings = state.sharings.filter(item => item.id !== data.id)
    sharings.splice(indexOfData, 0, data)
    state.sharings = sharings
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

  updateSharing ({ commit }, payload) {
    commit(types.UPDATE_SHARING, payload)
  },

  syncSharings ({ commit }, payload) {
    commit(types.SYNC_SHARINGS, payload)
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
