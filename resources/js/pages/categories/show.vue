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
    <div class="container">
      <div class="row">
        <div class="col-md-3 mb-4" v-for="sharing in category.sharings" :key="sharing.id">
          <sharing-card :sharing="sharing" />
        </div>
      </div>
    </div>

  </div>
  <!--<card :title="$t('home')">
    <h1>Ciao</h1>
    {{ $t('you_are_logged_in') }}
  </card>
  -->
</template>

<script>
  import { mapGetters } from 'vuex'
  import SharingCard from "../../components/SharingCard";

  export default {
    components: {SharingCard},
    middleware: 'auth',

    created() {
      this.$store.dispatch('categories/fetchCategory', this.$route.params.category_id);
    },

    computed: mapGetters({
      category: 'categories/category',
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
