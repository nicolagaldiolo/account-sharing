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
        <router-view :key="$route.fullPath"/>
      </transition>
    </div>
  </div>

</template>

<script>
export default {
  computed: {
    tabs () {
      return [
        {
          name: 'Rimborsi',
          route: 'admin.refunds'
        },
        {
          name: 'Sharings',
          route: 'admin.sharings'
        },
      ]
    }
  }

}
</script>
