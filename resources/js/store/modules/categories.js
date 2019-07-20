import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  categories: [],
  category: {}
}

// getters
export const getters = {
  categories: state => state.categories,
  category: state => state.category
}

// mutations
export const mutations = {
  [types.FETCH_CATEGORIES_SUCCESS] (state, { categories }) {
    state.categories = categories
  },

  [types.FETCH_CATEGORIES_FAILURE] (state) {
    state.categories = []
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

  async fetchCategories ({ commit }) {
    try {
      const { data } = await axios.get('/api/categories')
      commit(types.FETCH_CATEGORIES_SUCCESS, { categories: data })
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
