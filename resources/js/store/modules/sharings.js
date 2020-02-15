import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  sharings: [],
  sharing: {},
  chats: {},
  credentials: [],
  sharingRequests: []
}

// getters
export const getters = {
  sharings: state => state.sharings,
  sharing: state => state.sharing,
  chats: state => state.chats,
  credentials: state => state.credentials,
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

  [types.FETCH_CREDENTIALS_SUCCESS] (state, { credentials }) {
    state.credentials = credentials
  },

  [types.FETCH_CREDENTIALS_FAILURE] (state) {
    state.credentials = []
  },

  [types.SAVE_CREDENTIALS_SUCCESS] (state, credentials) {
    const newCredentials = state.credentials.filter(item => item.id !== credentials.id)
    newCredentials.push(credentials)
    state.credentials = newCredentials
  },

  [types.FETCH_CHATS_SUCCESS] (state, { chats }) {
    state.chats = chats
  },

  [types.FETCH_CHATS_FAILURE] (state) {
    state.chats = {}
  },

  [types.FETCH_SHARING_REQUESTS_SUCCESS] (state, { sharingRequests }) {
    state.sharingRequests = sharingRequests
  },

  [types.FETCH_SHARING_REQUESTS_FAILURE] (state) {
    state.sharingRequests = []
  },

  [types.UPDATE_SHARING] (state, { sharing }) {
    state.sharing = sharing
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

      commit(types.FETCH_SHARINGS_SUCCESS, { sharings: data.data })
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
      commit(types.FETCH_SHARING_SUCCESS, { sharing: data.data })
    } catch (e) {
      commit(types.FETCH_SHARING_FAILURE)
    }
  },

  async fetchChats ({ commit }, { id, currentPage }) {
    try {
      const { data } = await axios.get(`/api/sharings/${id}/chats?page=${currentPage}`)
      commit(types.FETCH_CHATS_SUCCESS, { chats: data })
      return true
    } catch (e) {
      commit(types.FETCH_CHATS_FAILURE)
      return false
    }
  },

  async fetchSharingRequests ({ commit }) {
    try {
      const { data } = await axios.get('/api/sharing-requests/');
      commit(types.FETCH_SHARING_REQUESTS_SUCCESS, { sharingRequests: data })
    } catch (e) {
      commit(types.FETCH_SHARING_REQUESTS_FAILURE)
    }
  },

  async fetchCredentials ({ commit }, id) {
    try {
      const { data } = await axios.get(`/api/sharings/${id}/credentials`)
      commit(types.FETCH_CREDENTIALS_SUCCESS, { credentials: data.data })
    } catch (e) {
      commit(types.FETCH_CREDENTIALS_FAILURE)
    }
  },

  saveCredentials ({ commit }, payload) {
    commit(types.SAVE_CREDENTIALS_SUCCESS, payload)
  }
}
