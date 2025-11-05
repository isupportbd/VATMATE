<template>
  <div class="float-button position-fixed text-center removeprintarea">
    <slot></slot>
  </div>
</template>

<script setup>
let fb = document.querySelector(".float-button");

</script>

<style lang="scss" scoped>
.float-button {
  border-radius: 0 5px 5px 5px;
  top: 200px;
  right: 0;
  z-index: 100;
}
</style>