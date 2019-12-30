<template>
  <div>

    <card :title="$t('your_info')">

      <input type="file" name="file" @change="selectFile">

      <form @submit.prevent="update" @keydown="form.onKeydown($event)">
        <alert-success :form="form" :message="$t('info_updated')" />

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('phone') }}</label>
          <div class="col-md-7">
            <input v-model="form.phone" :class="{ 'is-invalid': form.errors.has('phone') }" class="form-control" type="text" name="phone">
            <has-error :form="form" field="phone" />
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('street') }}</label>
          <div class="col-md-7">
            <input v-model="form.street" :class="{ 'is-invalid': form.errors.has('street') }" class="form-control" type="text" name="street">
            <has-error :form="form" field="street" />
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('city') }}</label>
          <div class="col-md-7">
            <input v-model="form.city" :class="{ 'is-invalid': form.errors.has('city') }" class="form-control" type="text" name="city">
            <has-error :form="form" field="city" />
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('cap') }}</label>
          <div class="col-md-7">
            <input v-model="form.cap" :class="{ 'is-invalid': form.errors.has('cap') }" class="form-control" type="text" name="cap">
            <has-error :form="form" field="cap" />
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

const objectToFormData = window.objectToFormData;

export default {
  scrollToTop: false,
  metaInfo() {
    return {title: this.$t('settings')}
  },

  data: () => ({
    form: new Form({
      foo: 'bar',
      file: null
    })
  }),

  computed: {
    ...mapGetters({
      user: 'auth/user'
    })
  },

  created () {
    // Fill the form with user data.
    this.form.keys().forEach(key => {
      this.form[key] = this.user[key]
    })
  },

  methods: {
    async update () {
      const { data } = await this.form.patch('/api/settings/profile-needed-info')
      this.$store.dispatch('auth/updateUser', { user: data })
    },

    selectFile (e) {
      const file = e.target.files[0]

      // Do some client side validation...

      this.form.file = file

      this.form.submit('post', '/api/settings/verify-account', {
        // Transform form data to FormData
        transformRequest: [function (data, headers) {
          return objectToFormData(data)
        }],

        onUploadProgress: e => {
          // Do whatever you want with the progress event
          console.log(e)
        }
      })
        .then(({ data }) => {
          // ...
        })
    }
  }
}
</script>
