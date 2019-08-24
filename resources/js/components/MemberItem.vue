<template>
  <div class="media text-muted pt-3">
    <img class="mr-2 rounded-circle" :src="user.photo_url" style="width: 32px; height: 32px;">
    <div class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">

      <div v-if="isAdmin">
        <strong class="text-gray-dark">{{user.name}}</strong>
        <span class="d-block">Admin</span>
      </div>
      <div v-else>

        <div class="d-flex justify-content-between align-items-center w-100">
          <strong class="text-gray-dark">{{user.name}}</strong>

          <a v-if="user.manageable" href="#" @click.prevent="" class="btn btn-danger btn-sm">X</a>
        </div>

        <span class="d-block">Membro dal {{ user.created_at | moment("D MMMM YYYY") }} --- DATO FAKE</span>

        <div v-if="user.manageable">
          <small>Il prossimo rinnovo sarà il <strong>{{user.renewalInfo.renewalDate | moment("D MMMM YYYY")}}</strong></small>
          <small>Se vuoi chiedere un rimborso il giorno limite è il <strong>{{user.refundInfo.day_limit | moment("D MMMM YYYY")}}</strong></small>
        </div>

      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'MemberItem',
  props: {
    user: {
      type: Object,
      default: null
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  }
}
</script>
