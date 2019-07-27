<template>
  <div>
    <section class="jumbotron" :style="{'background-image': 'url(' + sharing.image + ')'}">
      <div class="container">
        <div class="row">
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <img :src="sharing.owner.photo_url">
                <h5 class="card-title">{{sharing.owner.name}}</h5>
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">{{sharing.name}}</h5>
                <p class="card-text">{{sharing.description}}</p>
                <!--<a href="#" class="btn btn-primary">Go somewhere</a>-->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="container">
      <a @click="transition" class="btn btn-primary btn-lg btn-block">Entra nel gruppo</a>
      <!--<div class="row">
      </div>-->
    </div>

  </div>
</template>

<script>
  import { mapGetters } from 'vuex'
  import axios from 'axios'

  export default {
    middleware: 'auth',

    created() {
      this.$store.dispatch('sharings/fetchSharing', this.$route.params.sharing_id);
    },

    computed: mapGetters({
      sharing: 'sharings/sharing',
    }),

    methods: {

      transition() {
        console.log("Eccolo");
        axios.patch(`/api/sharings/${this.sharing.id}/transitions/0`).then((response) => {
          console.log(response);
        });
      }
      /*async create () {
        const { data } = await this.form.post('/api/sharings')

        console.log(data);
        return;
        // Redirect to sharing.
        //this.$router.push({ name: 'sharing.show', params: { category_id: this.form.category_id, sharing_id: data.id }})
      }*/
    }
  }


  /*
  e.preventDefault();

let currentObj = this;

this.axios.post('http://localhost:8000/yourPostApi', {

name: this.name,

description: this.description

})

.then(function (response) {

currentObj.output = response.data;

})

.catch(function (error) {

currentObj.output = error;

});
   */
</script>

<style scoped>
  .jumbotron{
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
  }
</style>
