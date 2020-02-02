import store from '~/store'

export default async (to, from, next) => {
  try {
    const data = store.getters['sharings/sharing'];

    if (Object.keys(data).length === 0) await store.dispatch('sharings/fetchSharing', to.params.sharing_id)

    if (store.getters['sharings/sharing'].user_status && store.getters['sharings/sharing'].user_status.state.value === 1) {
      next()
    } else {
      next({ name: 'sharing.show', params: { category_id: to.params.category_id, sharing_id: to.params.sharing_id } })
    }
  } catch (e) {
    next({ name: 'home' })
  }
}
