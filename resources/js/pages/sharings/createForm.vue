<template>
  <div>
    <div v-if="category">
      <section class="jumbotron text-center">
        <div class="container">
          <h1 class="jumbotron-heading">Crea nuovo gruppo {{category.name}}</h1>
          <alert-error :form="form" :message="form.message"></alert-error>
        </div>
      </section>
      <div class="container">
        <neededinfo v-if="user.additional_data_needed"></neededinfo>
        <div v-else>
          <router-link :to="{ name: 'sharing.create' }" class="btn btn-link">Torna indietro</router-link>
          <card>
            <form @submit.prevent="create" @keydown="form.onKeydown($event)">

              <!-- Cover image -->
              <div class="mb-4">
                <ImageSelector :category="category" @finished="finished"/>
                <div :class="{ 'is-invalid': form.errors.has('img_file') }" class="form-control d-none"></div>
                <has-error :form="form" field="img_file" />
              </div>

              <!-- Renewal frequency -->
              <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">{{ $t('renewal_frequency') }}</label>
                <div class="col-md-7">
                  <select v-model="form.renewal_frequency_id" :class="{ 'is-invalid': form.errors.has('renewal_frequency_id') }" class="form-control" name="renewal_frequency_id">
                    <option value="">Scegli una frequenza di rinnovo</option>
                    <option v-for="frequency in renewalFrequency" :key="frequency.id" :value="frequency.id">{{frequency.frequency}}</option>
                  </select>
                  <has-error :form="form" field="renewal_frequency_id" />
                </div>
              </div>

              <!-- Name -->
              <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">{{ $t('service_name') }}</label>
                <div class="col-md-7">
                  <input v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" class="form-control" type="text" name="name">
                  <has-error :form="form" field="name" />
                </div>
              </div>

              <!-- Description -->
              <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">Breve descrizione</label>
                <div class="col-md-7">
                  <input v-model="form.description" :class="{ 'is-invalid': form.errors.has('description') }" class="form-control" type="text" name="description">
                  <has-error :form="form" field="description" />
                </div>
              </div>

              <!-- Price -->
              <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">Prezzo del servizio</label>
                <div class="col-md-7">
                  <currency-input :disabled="!category.custom" v-model="form.price" :currency="user.currency" :locale="user.country" :distraction-free="false" :class="{ 'is-invalid': form.errors.has('price') }" class="form-control" type="text"/>
                  <has-error :form="form" field="price" />
                </div>
              </div>

              <!-- Slot -->
              <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">Posti disponibili</label>
                <div class="col-md-7">
                  <select v-model="form.slot" :class="{ 'is-invalid': form.errors.has('slot') }" class="form-control" name="slot">
                    <option value="">Seleziona i posti disponibili</option>
                    <option v-for="index in category.slot" :key="index" :value="index">{{ index }}</option>
                  </select>
                  <has-error :form="form" field="slot" />
                </div>
              </div>

              <!-- Visibility -->
              <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">Visibilità</label>
                <div class="col-md-7">
                  <select v-model="form.visibility" :class="{ 'is-invalid': form.errors.has('visibility') }" class="form-control" name="visibility">
                    <option value="">Scegli la visibilità</option>
                    <option v-for="(visibility, index) in sharingsVisibility" :key="index" :value="index">{{visibility}}</option>
                  </select>
                  <has-error :form="form" field="visibility" />
                </div>
              </div>

              <!-- Service Igree -->
              <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">Termini di servizio</label>
                <div class="col-md-7">
                  <div class="form-check">
                    <input type="checkbox" v-model="form.service_igree" :class="{ 'is-invalid': form.errors.has('service_igree') }" class="form-control form-check-input" name="service_igree" id="service_igree" value="1">
                    <label class="form-check-label" for="service_igree">Accetto i termini di servizi</label>
                    <has-error :form="form" field="service_igree" />
                  </div>

                </div>
              </div>

              <!-- Submit Button -->
              <div class="form-group row">
                <div class="col-md-9 ml-md-auto">
                  <v-button :loading="form.busy" type="success">
                    {{ $t('create_sharing') }}
                  </v-button>
                </div>
              </div>
            </form>
          </card>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import Form from 'vform'
import { mapGetters } from 'vuex'
import Neededinfo from '../settings/neededinfo'
import Swal from 'sweetalert2'
import ImageSelector from '../../components/ImageSelector'

const objectToFormData = window.objectToFormData

export default {
  components: {
    ImageSelector,
    Neededinfo
  },
  middleware: 'auth',

  data: () => ({
    form: new Form({
      name: '',
      img_file: null,
      img_string: '',
      description: '',
      price: 0,
      slot: 0,
      visibility: 0,
      renewal_frequency_id: 0,
      category_id: 0,
      service_igree: 0
    })
  }),

  methods: {

    async finished (e) {
      if (typeof e === 'string' || e instanceof String) {
        this.form.img_string = e
      } else {
        const file = e.target.files[0]
        this.form.img_file = file
      }
    },

    async create () {
      const { data } = await this.form.submit('post', '/api/sharings', {
        // Transform form data to FormData
        transformRequest: [function (data, headers) {
          return objectToFormData(data)
        }],
        onUploadProgress: e => {
          // Do whatever you want with the progress event
        }
      })

      // Redirect to sharing.
      this.$router.push({ name: 'sharing.show', params: { category_id: data.data.category_id, sharing_id: data.data.id } })
      if (data.data.status === 0) {
        Swal.fire({
          type: 'warning',
          title: 'Condivisione in fase di approvazione',
          text: 'Attendere comunicazione da parte dello staff per iniziare a condividere'
        })
      } else {
        Swal.fire({
          type: 'success',
          title: 'Condivisione creata con successo',
          text: 'Inizia a condividere'
        })
      }
    }
  },

  created () {
    //const obj = {
    //  id: this.$route.params.category_id,
    //  params: ['renewal_frequencies']
    //}

    this.$store.dispatch('categories/fetchCategory', this.$route.params.category_id).then(() => {
      if (!this.$store.getters['categories/category'].id) {
        this.$router.push({ name: '404' })
      } else {
        this.form.keys().forEach(key => {
          this.form[key] = (key === 'price') ? parseFloat(this.category[key]) : this.category[key]
        })
        this.form.category_id = this.category.id
      }
    })
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
      sharingsVisibility: 'config/sharingsVisibility',
      category: 'categories/category',
      renewalFrequency: 'config/renewalFrequency'
    })
  }
}
</script>
