<template>
  <div>
    <section class="jumbotron text-center" :style="{'background-image': 'url(' + category.image + ')'}">
      <div class="container">
        <h1 class="jumbotron-heading">{{category.name}}</h1>
        <p class="lead text-muted">Something short and leading about the collection below—its contents, the creator, etc. Make it short and sweet, but not too short so folks don’t simply skip over it entirely.</p>
        <p>
          <a href="#" class="btn btn-primary my-2">Main call to action</a>
          <a href="#" class="btn btn-secondary my-2">Secondary action</a>
        </p>
      </div>
    </section>
    <div v-if="category" class="container">
      <div class="mt-5">
        <sharings/>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Sharings from '../../components/Sharings'

export default {
  components: {
    Sharings
  },

  created () {
    this.$store.dispatch('categories/fetchCategory', this.$route.params.category_id).then(() => {
      if (!this.$store.getters['categories/category'].id) this.$router.push({ name: '404' })
    });
  },
  computed: mapGetters({
    category: 'categories/category',
    authUser: 'auth/user'
  })
}
</script>

<style scoped>
  .jumbotron{
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
  }
</style>
