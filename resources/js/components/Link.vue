<template>
  <a :href="href" @click.prevent="action" :class="{
    [`btn-${type}`]: true,
    'btn-block': block,
    'btn-lg': large,
    'btn-loading': loading,
    'disabled': loading
  }" class="btn"
  >
    <slot />
  </a>
</template>

<script>
export default {
  name: 'VLink',

  props: {
    type: {
      type: String,
      default: 'primary'
    },

    href: {
      type: String,
      default: ''
    },

    loading: {
      type: Boolean,
      default: false
    },

    block: {
      type: Boolean,
      default: false
    },

    large: {
      type: Boolean,
      default: false
    },

    action: {
      type: Function
    }
  }
}
</script>
