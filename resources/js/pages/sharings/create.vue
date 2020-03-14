<template>
  <div>
    <section class="jumbotron text-center">
      <div class="container">
        <h1 class="jumbotron-heading">Crea condivisione</h1>
        <p class="lead text-muted">Seleziona la categoria di interesse.<br><strong>Non potrai scegliere categorie per cui hai già una condivisione attiva.</strong></p>
      </div>
    </section>
    <div class="container">
      <div v-if="categories" class="list-group text-center">
        <div v-for="category in categories" :key="category.id">
          <router-link v-if="!category.forbidden" :to="{ name: 'sharing.create.category', params: { category_id: category.id } }" class="list-group-item list-group-item-action">{{ category.name }}</router-link>
          <a v-else href="#" class="list-group-item list-group-item-action disabled">{{ category.name }}</a>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    middleware: 'auth',

    created () {
      this.$store.dispatch('categories/fetchCategories');
    },

    computed: {
      ...mapGetters({
         user: 'auth/user',
         categories: 'categories/categories'
       })
    },
  }
</script>
