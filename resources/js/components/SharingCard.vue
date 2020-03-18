<template>
  <div class="card">
    <router-link :to="{ name: 'sharing.show', params: { category_id: sharing.category_id, sharing_id: sharing.id } }">
      <img :src="sharing.image" class="card-img-top" alt="...">
    </router-link>
    <div class="card-body">
      <div v-if="sharing.owner" class="card-text d-flex align-items-center">
        <img class="rounded-circle mr-2" :src="sharing.owner.photo_url" width="30">
        <small class="text-muted">
          <span><strong>{{sharing.owner.username}}</strong></span><br>
          <span>Membro dal <strong>{{sharing.owner.created_at | moment("D MMMM YYYY")}}</strong></span>

        </small>
      </div>
      <div class="pt-3">
        <h5 class="card-title p-0 m-0">{{sharing.name}}</h5>
        <span class="d-flex">
          <money-format :value="sharing.price_with_fee" :locale="authUser.country" :currency-code="authUser.currency" :subunit-value=false :hide-subunits=false></money-format>
          <span class="pl-1">({{sharing.renewal_frequency.frequency}})</span>
        </span>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
      <small class="text-muted">
        <strong>{{sharing.availability}}/{{sharing.max_slot_available}} Posti disponibili</strong>
      </small>
      <fa :class="calcCredentialStatus(sharing ? sharing.credential_status : 0).class" icon="key" fixed-width />
    </div>
  </div>
</template>

<script>
import { helperMixin } from '~/mixins/helperMixin'
import MoneyFormat from 'vue-money-format'

export default {
  name: 'SharingCard',
  props: {
    sharing: {
      type: Object,
      default: null
    },
    authUser: {
      type: Object,
      default: null
    }
  },

  components: {
    MoneyFormat
  },
  mixins: [ helperMixin ],
}
</script>
