import store from '~/store'

export default async (to, from, next) => {
  if (store.getters['auth/user'].registration_completed) {
    next()
  } else {
    next({ name: 'settings.firstinfo' })
  }
}
