<template>
  <div>

    <card :title="$t('your_info')">

      <form @submit.prevent="selectFile" @keydown="form.onKeydown($event)">
        <alert-success :form="form" :message="$t('info_updated')" />

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('document_front') }}</label>
          <div class="col-md-7">
            <!--<input v-model="form.phone" :class="{ 'is-invalid': form.errors.has('phone') }" class="form-control" type="text" name="phone">
            <has-error :form="form" field="phone" />-->
            <input type="file" name="file" ref="file">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">{{ $t('document_back') }}</label>
          <div class="col-md-7">
            <!--<input v-model="form.phone" :class="{ 'is-invalid': form.errors.has('phone') }" class="form-control" type="text" name="phone">
            <has-error :form="form" field="phone" />-->
            <input type="file" name="file" ref="file2">
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
      file: null,
      file2: null
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
      // Do some client side validation...

      this.form.file = this.$refs.file.files[0];
      this.form.file2 = this.$refs.file2.files[0];

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
