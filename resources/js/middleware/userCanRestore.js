import store from '~/store'

export default async (to, from, next) => {
  try {
    const data = store.getters['sharings/sharing']
    const authUser = store.getters['auth/user']

    if (Object.keys(data).length === 0) await store.dispatch('sharings/fetchSharing', to.params.sharing_id)

    const currentUserData = store.getters['sharings/sharing'].active_users.filter(user => user.id === authUser.id)

    if (currentUserData.length && currentUserData[0].sharing_status.subscription && currentUserData[0].sharing_status.subscription.status === 4) {
      next()
    } else {
      next({ name: 'sharing.show', params: { category_id: to.params.category_id, sharing_id: to.params.sharing_id } })
    }
  } catch (e) {
    next({ name: 'home' })
  }
}
