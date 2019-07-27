<template>
  <div>
    <section class="jumbotron text-center">
      <div class="container">
        <h1 class="jumbotron-heading">Crea condivisione</h1>
        <p class="lead text-muted">Something short and leading about the collection below—its contents, the creator, etc. Make it short and sweet, but not too short so folks don’t simply skip over it entirely.</p>
      </div>
    </section>
    <div class="container">
      <div v-if="!Object.keys(category).length" class="list-group text-center">
        <a v-for="category in categories" :key="category.id" href="#" @click.prevent="setCategory(category)" class="list-group-item list-group-item-action">{{ category.name }}</a>
      </div>

      <div v-if="Object.keys(category).length">
        <card :title="`Nuovo ${category.name}`">
          <form @submit.prevent="create" @keydown="form.onKeydown($event)">
            <!--<alert-success :form="form" :message="$t('info_updated')" />-->

            <!-- Renewal frequency -->
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-md-right">{{ $t('renewal_frequency') }}</label>
              <div class="col-md-7">
                <select v-model="form.renewal_frequency_id" :class="{ 'is-invalid': form.errors.has('renewal_frequency_id') }" class="form-control" name="renewal_frequency_id">
                  <option value="">Scegli una frequenza di rinnovo</option>
                  <option v-for="renewal_frequency in renewal_frequencies" :key="renewal_frequency.id" :value="renewal_frequency.id">{{renewal_frequency.frequency}}</option>
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
                <input v-model="form.price" :class="{ 'is-invalid': form.errors.has('price') }" class="form-control" type="text" name="price">
                <has-error :form="form" field="price" />
              </div>
            </div>

            <!-- Capacity -->
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-md-right">Posti disponibili</label>
              <div class="col-md-7">
                <input v-model="form.capacity" :class="{ 'is-invalid': form.errors.has('capacity') }" class="form-control" type="text" name="capacity">
                <has-error :form="form" field="capacity" />
              </div>
            </div>

            <!-- Visibility -->
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-md-right">Visibilità</label>
              <div class="col-md-7">
                <select v-model="form.visibility" :class="{ 'is-invalid': form.errors.has('visibility') }" class="form-control" name="visibility">
                  <option value="">Scegli la visibilità</option>
                  <option v-for="(visibility, index) in sharings_visibility" :key="index" :value="index">{{visibility}}</option>
                </select>
                <has-error :form="form" field="visibility" />
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
</template>

<script>
  import Form from 'vform'
  import { mapGetters } from 'vuex'

  export default {
    middleware: 'auth',

    data: () => ({
      category : {},
      form: new Form({
        name: '',
        description: '',
        price: '',
        capacity: '',
        visibility: '',
        renewal_frequency_id: '',
        category_id: ''
      })
    }),

    methods: {
      setCategory (category) {
        this.category = category;

        this.form.keys().forEach(key => {
          this.form[key] = this.category[key]
        });
        this.form.category_id = this.category.id;
      },
      async create () {
        const { data } = await this.form.post('/api/sharings')

        // Redirect to sharing.
        this.$router.push({ name: 'sharing.show', params: { category_id: this.form.category_id, sharing_id: data.id }})
      }
    },

    created() {
      this.$store.dispatch('categories/fetchCategories', ['renewal_frequencies', 'sharings_visibility']);
      console.log(this.embeds);
    },

    computed: mapGetters({
      categories: 'categories/categories',
      renewal_frequencies: 'categories/renewal_frequencies',
      sharings_visibility: 'categories/sharings_visibility'
    })
  }
</script>
