<template>
  <div>
    <modal name="imageSelector" height="auto" :scrollable="true">
      <div class="text-center">
        <h4>Carica l'immagine di copertina</h4>
        <small>Carica un'immagine che rappresenti il servizio che vuoi condividere</small>
        <label class="btn btn-primary btn-lg btn-block mt-2">
          Carica immagine
          <input type="file" @change="onFileSelect($event)" accept="image/*" style="display: none;">
        </label>
        <div v-if="category.images_archive">
          <small>Oppure seleziona una delle foto qui in basso</small>
          <div v-if="category.images_archive.length" class="img-carousel d-flex mt-2">
            <img v-for="image in category.images_archive" @click.prevent="onFileSelect(image)" :src="image"/>
          </div>
        </div>
      </div>
    </modal>

    <div :class="[{ 'withImg': imagePreview }, 'img-content']">
      <img :src="imagePreview" alt="">
      <a :class="[{ 'btn-edit': imagePreview }, 'btn btn-light']" @click.prevent="$modal.show('imageSelector')" href="#">{{imagePreview ? 'Modifica' : 'Aggiungi immagine'}}</a>
    </div>


  </div>
</template>

<script>
export default {

  name: 'ImageSelector',

  props: {
    category: {
      type: Object,
      default: null
    },
    preview: {
      type: String,
      default: null
    }
  },

  data: () => ({
    image: null
  }),

  computed: {
    imagePreview () {
      return (this.image) ? this.image : this.preview
    }
  },

  methods: {
    onFileSelect (e) {
      this.$modal.hide('imageSelector')
      this.image = (typeof e === 'string' || e instanceof String) ? e : URL.createObjectURL(e.target.files[0])
      this.$emit('finished', e)
    }
  }
}
</script>

<style scoped>
  label{
    cursor: pointer;
  }

  .img-content{
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .img-content a{
    position: absolute;
  }

  .btn-edit{
    right: 20px;
    bottom: 20px;
  }

  .img-content img{
    width: 100%;
    object-fit: cover;
    height: 250px;
    background: #C5C5C5;
  }

  .img-carousel img{
    padding: 5px;
    box-sizing: border-box;
    width: 33.333333%;
    cursor:pointer;
  }
</style>
