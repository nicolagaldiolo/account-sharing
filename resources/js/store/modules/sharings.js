import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  sharings: {
    data: [],
    links: {},
    meta: {}
  },
  sharing: {},
  chats: {
    data: [],
    links: {},
    meta: {}
  },
  credentials: []
}

// getters
export const getters = {
  sharings: state => state.sharings,
  sharing: state => state.sharing,
  chats: state => state.chats,
  credentials: state => state.credentials
}

// mutations
export const mutations = {
  [types.FETCH_SHARINGS_SUCCESS] (state, { sharings }) {
    const obj = {
      data: (sharings.meta.current_page === 1) ? sharings.data : state.sharings.data.concat(...sharings.data),
      links: sharings.links,
      meta: sharings.meta
    }
    state.sharings = Object.assign({}, obj)
  },

  [types.FETCH_SHARINGS_FAILURE] (state) {
    state.sharings = {}
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
    const obj = {
      data: (chats.meta.current_page === 1) ? chats.data : state.chats.data.concat(...chats.data),
      links: chats.links,
      meta: chats.meta
    }
    state.chats = Object.assign({}, obj)
  },

  [types.FETCH_CHATS_FAILURE] (state) {
    state.chats = {}
  },

  [types.UPDATE_SHARING] (state, { sharing }) {
    state.sharing = sharing
  },

  [types.SYNC_SHARINGS] (state, { sharing }) {
    let indexOfData = state.sharings.data.map(e => e.id).indexOf(sharing.id)
    let sharingsDataUpdated = state.sharings.data.filter(item => item.id !== sharing.id)
    sharingsDataUpdated.splice(indexOfData, 0, sharing)

    state.sharings.data = sharingsDataUpdated

    // Re-assign the state with Object.assign because vuex state doesn't react at change in deph
    state.sharings = Object.assign({}, state.sharings)
  }
}

// actions
export const actions = {
  async fetchSharings ({ commit }, params) {

    try {
      const data = await axios.get('/api/sharings' + params)
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

  async fetchChats ({ commit }, { id, params }) {
    try {
      const { data } = await axios.get(`/api/sharings/${id}/chats` + params)
      commit(types.FETCH_CHATS_SUCCESS, { chats: data })
      return true // da cancellare
    } catch (e) {
      commit(types.FETCH_CHATS_FAILURE)
      return false // da cancellare
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
