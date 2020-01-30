<template>
    <div>
      <img class="mr-2 rounded-circle" :src="member.photo_url" style="width: 32px; height: 32px;">
      <div class="media-body pb-3 mb-0 small">
        <strong class="text-gray-dark">{{member.username}}</strong>
        <div class="d-flex">
          <span class="d-block">Credenziali: {{credentialStatus.state}}</span>
          <fa :class="credentialStatus.class" class="ml-2" icon="key" fixed-width />
        </div>
      </div>
    </div>
</template>

<script>
  import { helperMixin } from '~/mixins/helperMixin'

  export default {
    name: "CredentialMemberStatus",

    mixins: [ helperMixin ],
    props: {
      member: {
        type: Object,
        default: null
      },
    },

    computed: {
      credentialStatus () {
        return this.calcCredentialStatus(this.member ? this.member.credential_status : 0)
      },
    }
  }
</script>

<style scoped>

</style>
