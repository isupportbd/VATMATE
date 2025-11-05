<template>
  <div class="h-100" v-bind="$attrs">
    <div class="h-100 d-flex align-items-center justify-content-center">
      <div class="spinner-grow"
        :style="`width: ${$attrs.size ? $attrs.size : '3rem'}; height: ${$attrs.size ? $attrs.size : '3rem'};`"
        role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <div class="fw-bold ms-2">
        <span :class="$attrs.textsize" :style="`font-size: ${$attrs.textsize}`" v-if="$attrs.text">
          {{ $attrs.text }}
        </span>
        <span v-else>Loading...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: "Spinner", inheritAttrs: false });
</script>