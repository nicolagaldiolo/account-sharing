<template>
  <div>

    <card :title="$t('your_info')">
      <form @submit.prevent="update" @keydown="form.onKeydown($event)">
        <alert-success :form="form" :message="$t('info_updated')" />

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('country') }}</label>
          <div class="col-md-7">
            <select v-model="form.country" :class="{ 'is-invalid': form.errors.has('country') }" class="form-control" name="country">
              <option value="">Scegli la nazione</option>
              <option v-for="(country, key) in countries" :key="key" :value="key">{{country.label}}</option>
            </select>
            <has-error :form="form" field="country" />
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('birthday') }}</label>
          <div class="col-md-7">
            <input v-model="form.birthday" :class="{ 'is-invalid': form.errors.has('birthday') }" class="form-control" type="date" :max=ageFrom name="birthday">
            <has-error :form="form" field="birthday" />
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

export default {
  scrollToTop: false,
  metaInfo () {
    return {
      title: this.$t('settings')
    }
  },

  data: () => ({
    form: new Form({
      country: '',
      age: ''
    }),
  }),

  computed: {
    ...mapGetters({
      user: 'auth/user',
      countries: 'lang/countries'
    }),
    ageFrom: function () {
      return this.$moment().subtract(window.config.limitUserAge, 'years').format('YYYY-MM-DD')
    }
  },

  created () {
    // Fill the form with user data.
    this.form.keys().forEach(key => {
      this.form[key] = this.user[key]
    })
  },

  methods: {
    async update () {
      const { data } = await this.form.patch('/api/settings/complete-registration')

      // Update the user.
      await this.$store.dispatch('auth/updateUser', { user: data })

      // Redirect home.
      this.$router.push({ name: 'home' })
    }
  }
}
</script>
