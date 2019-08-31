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

          <a v-if="user.manageable && user.renewalInfo.renewal_status != 2" href="#" @click.prevent="renewalAction('left')" class="btn btn-danger btn-sm">Lascia il gruppo</a>
          <a v-if="user.manageable && user.renewalInfo.renewal_status == 2" href="#" @click.prevent="renewalAction('restore')" class="btn btn-secondary btn-sm">Torna nel gruppo</a>

        </div>

        <span class="d-block">Membro dal {{ user.created_at | moment("D MMMM YYYY") }} --- DATO FAKE</span>

        <div v-if="user.manageable">
          <div v-if="user.renewalInfo.renewal_status == 2">
            <small>Verrà rimosso il {{user.renewalInfo.renewal_date | moment("D MMMM YYYY")}} (Giorno limite per rimborso il <strong>{{user.renewalInfo.refund_day_limit | moment("D MMMM YYYY")}}</strong>)</small>
          </div>
          <div v-else>
            <small>Il prossimo rinnovo sarà il <strong>{{user.renewalInfo.renewal_date | moment("D MMMM YYYY")}}</strong></small>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'MemberItem',
  props: {
    user: {
      type: Object,
      default: null
    },
    sharing: {
      type: Object,
      default: null
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    renewalAction (action) {
      axios.patch(`/api/sharings/${this.sharing.id}/user/${this.user.id}/action/${action}`).then((response) => {
        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
      })
    },
  }
}
</script>
