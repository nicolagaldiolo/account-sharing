<template>
  <div>
    <div class="container mt-4">

      <div class="row">
        <div class="col-3" v-for="category in categories" :key="category.id">
          <div v-if="category.custom === 0">
            <router-link :to="{ name: 'category.show', params: { category_id: category.id } }" class="category-item">
              <span>{{ category.name }}</span>
              <img class="img-fluid" :src="category.image">
            </router-link>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <sharings />
      </div>

    </div>

  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Sharings from '~/components/Sharings'

export default {
  components: {
    Sharings
  },
  middleware: [
    'auth',
    'registrationCompleted'
  ],

  computed: mapGetters({
    categories: 'categories/categories',
  }),

  created () {
    this.$store.dispatch('categories/fetchCategories')
  }
}
</script>

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
