<template>
  <button class="d-flex justify-content-center align-items-center custom-btn shadow-none" v-bind="$attrs">
    <slot></slot>
    <div v-if="!slots.default">
      <span :class="`text-capitalize ${(props.label && props.icon) ? 'me-1' : ''}`">{{ props.label }}</span>
      <span><i :class="props.icon"></i></span>
    </div>
  </button>
</template>

<script setup>
import { useAttrs, useSlots } from "vue";

defineOptions({ name: "Button", inheritAttrs: false });

const $attrs = useAttrs();
const slots = useSlots();
const props = defineProps({
  label: { type: String },
  icon: { type: String },
});
</script>

<style lang="scss" scoped></style>