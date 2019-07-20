<template>
  <div class="card">
    <router-link :to="{ name: 'sharing.show', params: { category_id: sharing.category_id, sharing_id: sharing.id } }">
      <img :src="sharing.image" class="card-img-top" alt="...">
    </router-link>
    <div class="card-body">
      <p class="card-text">
        <a href="">
          <img class="rounded-circle" :src="sharing.owner.photo_url" width="40">
          <small class="text-muted">{{sharing.owner.name}}</small>
        </a>
      </p>
      <h5 class="card-title">{{sharing.name}}</h5>
    </div>
    <div class="card-footer">
      <small class="text-muted">{{sharing.availability}}/{{sharing.capacity}} disponibili</small>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SharingCard',
  props: {
    sharing: {
      type: Object,
      default: null
    }
  }
}
</script>
