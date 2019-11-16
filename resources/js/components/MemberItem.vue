<template>
  <div class="media text-muted pt-3">

    <img class="mr-2 rounded-circle" :src="user.photo_url" style="width: 32px; height: 32px;">
    <div class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">

      <div v-if=user.sharing_status.owner>
        <strong class="text-gray-dark">{{user.name}}</strong>
        <span class="d-block">Admin</span>
      </div>
      <div v-else>

        <div class="d-flex justify-content-between align-items-center w-100">
          <strong class="text-gray-dark">{{user.name}}</strong>

          <a v-if="(owner || authUser.id == user.id) && user.sharing_status.subscription" href="#" @click.prevent="renewalAction"
             :class="user.sharing_status.subscription.cancel_at_period_end ? 'btn btn-secondary btn-sm' : 'btn btn-danger btn-sm'">
            {{user.sharing_status.subscription && user.sharing_status.subscription.cancel_at_period_end ? 'torna nel gruppo' : 'lascia il gruppo'}}
          </a>

        </div>

        <span v-if="user.sharing_status.subscription && user.sharing_status.subscription.created_at" class="d-block">Membro dal {{ user.sharing_status.subscription.created_at | moment("D MMMM YYYY") }}</span>

        <div v-if="(owner || authUser.id == user.id) && user.sharing_status.subscription">
          <div v-if="user.sharing_status.subscription.cancel_at_period_end">
            <small>Verrà rimosso il {{user.sharing_status.subscription.current_period_end_at | moment("D MMMM YYYY")}}
              <!--(Giorno limite per rimborso il <strong>{{user.renewalInfo.refund_day_limit | moment("D MMMM YYYY")}}</strong>)-->
            </small>
          </div>
          <div v-else>
            <small>Il prossimo rinnovo sarà il <strong>{{user.sharing_status.subscription.current_period_end_at | moment("D MMMM YYYY")}}</strong></small>
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
    authUser: {
      type: Object,
      default: null
    },
    owner: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    renewalInfo: function () {
      return this.user.renewalInfo && Object.keys(this.user.renewalInfo).length > 0
    }
  },

  methods: {
    renewalAction (action) {
      axios.patch(`/api/sharings/${this.user.sharing_status.sharing_id}/user/${this.user.sharing_status.user_id}/update`).then((response) => {
        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
      })
    },
  }
}
</script>
