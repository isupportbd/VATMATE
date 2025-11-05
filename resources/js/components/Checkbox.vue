<template>
  <div :class="`form-check mb-0 ${$attrs.topclass}`" style="min-height: 0;">
    <input class="form-check-input shadow-none border-secondary" type="checkbox" :id="$attrs.id" v-model="model"
      v-bind="$attrs" />
    <label class="form-check-label" :for="$attrs.id"> {{ $attrs.label }} </label>
  </div>
</template>

<script setup>
defineOptions({ name: "Checkbox", inheritAttrs: false });

// vue3.4 way
const model = defineModel();

// vue3.3
/** 
 * const emit = defineEmits(['update:modelValue']);
 * const model = computed({
 *   get: () => props.modelValue,
 *   set: (val) => { emit("update:modelValue", val) },
 * });
 */
</script>

<style lang="scss" scoped></style>