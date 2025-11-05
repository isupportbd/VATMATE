<template>
  <a :href="to" class="d-flex align-items-center" v-bind="$attrs">
    <slot></slot>
    <div v-if="!slots.default">
      <span :class="`text-capitalize ${(props.label && props.icon) ? 'me-1' : ''}`">{{ props.label }}</span>
      <span><i :class="props.icon"></i></span>
    </div>
  </a>
</template>

<script setup>
import { useSlots } from "vue";

defineOptions({ name: "Href", inheritAttrs: false });

const slots = useSlots();
const props = defineProps({
  label: { type: String },
  icon: { type: String },
  to: { type: [String, Number, Object] }
});
</script>

<style lang="scss" scoped></style>