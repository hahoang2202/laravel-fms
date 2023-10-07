<template>
  <div v-show="!isRenderReady">
    <div class="d-flex justify-content-center">
      <div class="spinner-border" style="width: 8rem; height: 8rem" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isRenderReady: {
      type: Boolean,
      default: false,
    },
  },
};
</script>

<style>
</style>