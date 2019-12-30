<template>
  <div>
    <div class="container mt-4">
      <h2>Categorie</h2>
      <div class="row">
        <div class="col" v-for="category in categories" :key="category.id">
          <router-link :to="{ name: 'category.show', params: { category_id: category.id } }" class="category-item">
            <span>{{ category.name }}</span>
            <img :src="category.image" width="150">
          </router-link>

          <a href="#" class="category-item">

          </a>

        </div>
      </div>
      <!--
      <carousel :items="7" :margin="15" :autoplay="true" :nav="true">
      </carousel>
      -->

      <h2>Catalogo</h2>

      <div class="row">
        <div class="col-md-3 mb-4" v-for="sharing in sharings" :key="sharing.id">
          <sharing-card :sharing="sharing" />
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
  .category-item{
    position: relative;
  }
  .category-item span{
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    text-align: center;
  }
</style>

<script>
  import carousel from 'vue-owl-carousel'
  import { mapGetters } from 'vuex'
  import SharingCard from '~/components/SharingCard';

  export default {
    components: {
      SharingCard
    },
    middleware: [
      'auth',
      'registrationCompleted'
    ],
    //components: {
    //  carousel
    //},

    //data(){
    //  return {
    //    added : false
    //  }
    //},

    //metaInfo () {
    //  return { title: this.$t('home') }
    //},

    //mounted(){
    //},

    created() {
      this.$store.dispatch('categories/fetchCategories');
      this.$store.dispatch('sharings/fetchSharings');
    },

    computed: mapGetters({
      categories: 'categories/categories',
      sharings: 'sharings/sharings',
    })
  }
</script>
