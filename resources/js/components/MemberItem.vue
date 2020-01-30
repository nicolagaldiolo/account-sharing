<template>
  <div class="media text-muted pt-3">
    <img class="mr-2 rounded-circle" :src="member.photo_url" style="width: 32px; height: 32px;">
    <div class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">

      <div v-if="isOwner">
        <strong class="text-gray-dark">{{member.username}}</strong>
        <span class="d-block">Admin</span>
      </div>
      <div v-else>
        <div>
          <strong class="text-gray-dark">{{member.username}}</strong>
        </div>

        <span v-if="member.subscription && member.subscription.created_at" class="d-block">Membro dal {{ member.subscription.created_at | moment("D MMMM YYYY") }}</span>

        <div v-if="(isAuthUserOwner || isAuthUser) && member.subscription">
          <div v-if="member.subscription.cancel_at_period_end">
            <small>Verrà rimosso il {{member.subscription.current_period_end_at | moment("D MMMM YYYY")}}
              <br>
              (Giorno limite per rimborso il <strong>{{member.subscription.refund_day_limit | moment("D MMMM YYYY")}}</strong>)
            </small>
          </div>
          <div v-else>
            <small>Il prossimo rinnovo sarà il <strong>{{member.subscription.current_period_end_at | moment("D MMMM YYYY")}}</strong></small>
          </div>
        </div>

        <a v-if="(isAuthUserOwner || isAuthUser) && member.subscription" href="#" @click.prevent="renewalAction"
           :class="member.subscription.cancel_at_period_end ? 'btn btn-secondary btn-sm' : 'btn btn-danger btn-sm'">
          {{member.subscription && member.subscription.cancel_at_period_end ? 'torna nel gruppo' : 'lascia il gruppo'}}
        </a>

      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'MemberItem',
  props: {
    sharingId: {
      type: Number,
      default: 0
    },
    member: {
      type: Object,
      default: null
    },
    authUser: {
      type: Object,
      default: null
    },
    owner: {
      type: Object,
      default: null
    }
  },

  computed: {

    isOwner: function () {
      return this.member.id === this.owner.id
    },
    isAuthUser: function () {
      return this.authUser.id === this.member.id
    },
    isAuthUserOwner: function () {
      return this.authUser.id === this.owner.id
    }
  },

  methods: {
    renewalAction (action) {
      //axios.patch(`/api/sharings/${this.sharingId}/user/${this.member.id}/update`).then(response => {
      //  this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
      //})
    },
  }
}
</script>
