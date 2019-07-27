<template>
  <div class="d-flex justify-content-between align-items-center">
    <div class="media align-items-center">
      <img src="https://lorempixel.com/40/40/?28787" class="mr-3 rounded-circle" alt="">
      <div class="media-body align-items-centeri">
        <h6 class="mt-0">{{user.name}}<small class="pl-2 text-muted">Last updated 3 mins ago</small></h6>
      </div>
    </div>
    <div v-if="buttons">
      <a href="" class="btn btn-primary btn-sm">Accetta</a>
      <a href="" class="btn btn-outline-primary btn-sm">Rifiuta</a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ManageSharingUser',
  props: {
    user: {
      type: Object,
      default: null
    },
    buttons: {
      type: Boolean,
      default: false
    }
  }
}
</script>
