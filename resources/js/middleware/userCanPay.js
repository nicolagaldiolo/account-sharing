import store from '~/store'

export default async (to, from, next) => {
  try {
    const data = store.getters['sharings/sharing'];
    const user = store.getters['auth/user'];

    if (Object.keys(data).length === 0) await store.dispatch('sharings/fetchSharing', to.params.sharing_id)

    const sharing = store.getters['sharings/sharing'];

    if (sharing.user_status && (sharing.user_status.state.value === 1 ||
      (sharing.user_status.state.value === 3 && sharing.members.find(item => item.id === user.id).subscription.status === 4))
    ) {
      next()
    } else {
      next({ name: 'sharing.show', params: { category_id: to.params.category_id, sharing_id: to.params.sharing_id } })
    }
  } catch (e) {
    next({ name: 'home' })
  }
}
