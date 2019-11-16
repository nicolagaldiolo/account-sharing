<template>
  <wallet checkout-mode="true" action="restore"></wallet>
</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'
import wallet from '../settings/wallet';

export default {
    middleware: [
        'auth',
        'userCanRestore'
    ],
    components: {
        wallet
    },

    data: () => ({
        stripe: window.Stripe(window.config.stripeKey)
    }),

    created () {
    },

    computed: {
        ...mapGetters({
            sharing: 'sharings/sharing',
            authUser: 'auth/user'
        }),
    }
}
</script>

<style scoped>
</style>
