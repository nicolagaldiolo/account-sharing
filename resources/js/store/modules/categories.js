import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  data: {},
  category: {}
}

// getters
export const getters = {
  category: state => state.category,
  categories: state => state.data.categories,
  renewal_frequencies: state => state.data.renewal_frequencies,
  sharings_visibility: state => state.data.sharings_visibility,
}

// mutations
export const mutations = {
  [types.FETCH_CATEGORIES_SUCCESS] (state, { data }) {
    state.data = data
  },

  [types.FETCH_CATEGORIES_FAILURE] (state) {
    state.data = {}
  },

  [types.FETCH_CATEGORY_SUCCESS] (state, { category }) {
    state.category = category
  },

  [types.FETCH_CATEGORY_FAILURE] (state) {
    state.category = {}
  }
}

// actions
export const actions = {

  async fetchCategories ({ commit }, embed = []) {
    try {
      let params = (embed.length > 0) ? `?embed=${embed.join(',')}` : '';
      const { data } = await axios.get('/api/categories' + params);
      commit(types.FETCH_CATEGORIES_SUCCESS, { data: data.data })
    } catch (e) {
      commit(types.FETCH_CATEGORIES_FAILURE)
    }
  },

  async fetchCategory ({ commit }, id) {
    try {
      const { data } = await axios.get('/api/categories/' + id)
      commit(types.FETCH_CATEGORY_SUCCESS, { category: data })
    } catch (e) {
      commit(types.FETCH_CATEGORIES_FAILURE)
    }
  }
}
