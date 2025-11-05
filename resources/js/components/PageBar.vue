<template>
  <Teleport v-if="target" :to="target">
    <slot />
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue';

defineOptions({ name: "PageBar", inheritAttrs: false });

const target = ref(null);
onMounted(() => {
  // grab the header container once it's in the DOM
  target.value = document.getElementById('pagebar')
});
</script>

<style lang="scss" scoped></style>