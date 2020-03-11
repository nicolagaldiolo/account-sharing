<template>
  <div v-if="sharing.owner.id === authUser.id">

    <modal name="editSharing" height="auto" :scrollable="true">
      <form @submit.prevent="updateSharing" @keydown="form.onKeydown($event)">

        <!-- Cover image -->
        <div class="mb-4">
          <ImageSelector :category="sharing.category" :preview="sharing.image" @finished="finished"/>
          <div :class="{ 'is-invalid': form.errors.has('img_file') }" class="form-control d-none"></div>
          <has-error :form="form" field="img_file" />
        </div>

        <!-- Slot -->
        <div v-if="!sharing.category.custom" class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">Posti disponibili</label>
          <div class="col-md-7">
            <select v-model="form.slot" :class="{ 'is-invalid': form.errors.has('slot') }" class="form-control" name="slot">
              <option v-for="slot in slotOptions">{{ slot }}</option>
            </select>
            <has-error :form="form" field="slot" />
          </div>
        </div>

        <!-- Visibility -->
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-md-right">Visibilità</label>
          <div class="col-md-7">
            <select v-model="form.visibility" :class="{ 'is-invalid': form.errors.has('visibility') }" class="form-control" name="visibility">
              <option v-for="(visibility, index) in sharingsVisibility" :key="index" :value="index">{{visibility}}</option>
            </select>
            <has-error :form="form" field="visibility" />
          </div>
        </div>

        <!-- Submit Button -->
        <v-button :loading="form.busy" type="success" class="btn-block">
          {{ $t('edit_sharing') }}
        </v-button>

      </form>
    </modal>

    <a @click.prevent="$modal.show('editSharing')" href="#">Modifica gruppo</a>

  </div>
</template>

<script>
import { helperMixin } from '~/mixins/helperMixin'
import { mapGetters } from 'vuex'
import ImageSelector from './ImageSelector'
import Form from 'vform'
const objectToFormData = window.objectToFormData

export default {
  components: {
    ImageSelector
  },

  name: 'UpdateSharingForm',
  mixins: [ helperMixin ],
  props: {
    sharing: {
      type: Object,
      default: null
    },
  },

  data: () => ({
    form: new Form({
      slot: 0,
      visibility: 0
    })
  }),

  computed: {
    ...mapGetters({
      authUser: 'auth/user',
      sharingsVisibility: 'config/sharingsVisibility'
    }),

    slotOptions() {

      const start = this.sharing.min_slot_available
      const end = this.sharing.max_slot_capacity
      return Array(end - start + 1).fill().map((_, idx) => start + idx).reverse()
    }

  },

  created() {
    this.form.keys().forEach(key => {
      this.form[key] = this.sharing[key]
    })
  },

  methods: {
    async finished (e) {
      if (typeof e === 'string' || e instanceof String) {
        this.form.img_string = e
      } else {
        this.form.img_file = e.target.files[0]
      }
    },

    async updateSharing () {
      this.form._method = 'PATCH';

      // remove the slot key if custom sharing
      if (this.sharing.category.custom) delete this.form.slot

      const { data } = await this.form.submit('post', `/api/sharings/${this.sharing.id}/update`, {
        // Transform form data to FormData
        transformRequest: [function (data, headers) {
          return objectToFormData(data)
        }],
        onUploadProgress: e => {
          // Do whatever you want with the progress event
          //console.log(e)
        }
      });

      this.$store.dispatch('sharings/updateSharing', { sharing: data.data })
      this.$modal.hide('editSharing')
      this.showSimpleToast('Condivisione aggiornata con successo')
    },
  }
}
</script>
