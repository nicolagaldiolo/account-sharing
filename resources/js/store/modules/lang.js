import Cookies from 'js-cookie'
import * as types from '../mutation-types'

const { locale, locales, countries } = window.config

// state
export const state = {
  locale: Cookies.get('locale') || locale,
  locales,
  countries
}

// getters
export const getters = {
  locale: state => state.locale,
  locales: state => state.locales,
  countries: state => state.countries
}

// mutations
export const mutations = {
  [types.SET_LOCALE] (state, { locale }) {
    state.locale = locale
  }
}

// actions
export const actions = {
  setLocale ({ commit }, { locale }) {
    commit(types.SET_LOCALE, { locale })
    Cookies.set('locale', locale, { expires: 365 })
  }
}
