<template>
    <sharings :type=type :title="title"></sharings>
</template>

<script>
import Sharings from '~/components/Sharings'

export default {
  components: {
    Sharings
  },

  props: {
    type: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: ''
    }
  }

}
</script>
