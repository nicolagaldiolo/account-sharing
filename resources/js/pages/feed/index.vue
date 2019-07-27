<template>

  <div>
    <div class="bg-white">
      <div class="container">
        <ul class="nav nav-pills nav-fill">
          <li v-for="tab in tabs" :key="tab.route" class="nav-item">
            <router-link :to="{ name: tab.route }" class="nav-link" active-class="active">{{ tab.name }}</router-link>
          </li>
        </ul>
      </div>
    </div>
    <div class="container mt-4">
      <transition name="fade" mode="out-in">
        <router-view />
      </transition>
    </div>
  </div>

</template>

<script>
export default {
  middleware: 'auth',

  computed: {
    tabs () {
      return [
        {
          name: 'Richieste',
          route: 'feed.requests'
        },
        {
          name: 'Notifiche',
          route: 'feed.notifications'
        }
      ]
    }
  }
}
</script>
