<template>
  <div>

    <card :title="$t('your_info')">
      <form @submit.prevent="update" @keydown="form.onKeydown($event)">
        <alert-success :form="form" :message="$t('info_updated')" />

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('name') }}</label>
          <div class="col-md-7">
            <input v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" class="form-control" type="text" name="name">
            <has-error :form="form" field="name" />
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('surname') }}</label>
          <div class="col-md-7">
            <input v-model="form.surname" :class="{ 'is-invalid': form.errors.has('surname') }" class="form-control" type="text" name="surname">
            <has-error :form="form" field="surname" />
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('email') }}</label>
          <div class="col-md-7">
            <input v-model="form.email" :class="{ 'is-invalid': form.errors.has('email') }" class="form-control" type="email" name="email">
            <has-error :form="form" field="email" />
          </div>
        </div>

        <div class="form-group row">
          <div class="col-md-9 ml-md-auto">
            <v-button :loading="form.busy" type="success">
              {{ $t('update') }}
            </v-button>
          </div>
        </div>
      </form>
    </card>
  </div>
</template>

<script>
import Form from 'vform'
import { mapGetters } from 'vuex'
import { Card, createToken } from 'vue-stripe-elements-plus'

export default {
    scrollToTop: false,
  metaInfo () {
    return { title: this.$t('settings') }
  },

  data: () => ({
    form: new Form({
      name: '',
      surname: '',
      email: ''
    }),
      complete: false,
      stripeOptions: {
        hidePostalCode: true
        // see https://stripe.com/docs/stripe.js#element-options for details
      }
  }),

  computed: mapGetters({
    user: 'auth/user',
  }),

  created () {
    // Fill the form with user data.
    this.form.keys().forEach(key => {
      this.form[key] = this.user[key]
    })
  },

  methods: {
    async update () {
      const { data } = await this.form.patch('/api/settings/profile')
      this.$store.dispatch('auth/updateUser', { user: data })
    }
  }
}
</script>
