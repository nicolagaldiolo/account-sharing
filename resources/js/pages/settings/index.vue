<template>
  <div class="container">
    <div class="row">
      <div class="col-md-3">
        <card :title="$t('settings')" class="settings-card">
          <ul class="nav flex-column nav-pills">
            <li v-for="tab in tabs" :key="tab.route" class="nav-item">
              <router-link :to="{ name: tab.route }" class="nav-link" active-class="active">
                <fa :icon="tab.icon" fixed-width />
                {{ tab.name }}
              </router-link>
            </li>
          </ul>
        </card>
        <card title="Wallets" class="settings-card mt-5">
          <ul class="nav flex-column nav-pills">
            <li v-for="tab in tabs2" :key="tab.route" class="nav-item">
              <router-link :to="{ name: tab.route }" class="nav-link" active-class="active">
                <fa :icon="tab.icon" fixed-width />
                {{ tab.name }}
              </router-link>
            </li>
          </ul>
        </card>
      </div>

      <div class="col-md-9">
        <transition name="fade" mode="out-in">
          <router-view />
        </transition>
      </div>
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
          icon: 'user',
          name: this.$t('profile'),
          route: 'settings.profile'
        },
        {
          icon: 'lock',
          name: this.$t('password'),
          route: 'settings.password'
        }
      ]
    },
    tabs2 () {
        return [
            {
                icon: 'lock',
                name: 'Wallet',
                route: 'settings.wallet'
            },
            {
                icon: 'lock',
                name: 'Carte di pagamento',
                route: 'settings.paymentmethods'
            },
        ]
    }
  }
}
</script>

<style>
.settings-card .card-body {
  padding: 0;
}
</style>
