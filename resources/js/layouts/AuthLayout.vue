<template>
  <div class="wrapper">
    <slot></slot>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  history.pushState(null, null, window.location.href);
  window.onpopstate = (e) => history.pushState(null, null, window.location.href);
});
</script>