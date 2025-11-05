<template>
  <div class="mb-2">
    <label for="select" :class="$attrs.title ? 'form-label text-capitalize d-flex' : 'd-none'">
      <span class="text-capitalize">{{ $attrs.title }}</span>
      <span :class="props.must ? 'text-danger rounded-circle bg-danger must' : 'd-none'"></span>
    </label>
    <v-select id="select" v-model="model" v-bind="$attrs" />
    <div :class="err ? 'form-text text-danger' : 'd-none'" style="font-size: 0.8rem;">{{ err }}</div>
  </div>
</template>

<script setup>
import { watchEffect, computed } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

defineOptions({ name: "Select", inheritAttrs: false });

const props = defineProps({
  must: { type: Boolean },
  err: { type: [String, Boolean], required: false },
  fetched: { type: Function },
});

// vue3.4
const model = defineModel();

// vue3.3
/**
 * const emit = defineEmits(['fetched', 'update:modelValue']);
 * const model = computed({
 *   get() {return props.modelValue},
 *   set(value) {emit('update:modelValue', value)}
 * });
 */

watchEffect(async () => {
  await props.fetched();
})
</script>

<style lang="scss" scoped>
.vs__dropdown-toggle {
  padding: .27rem 0;
  margin-bottom: 3px;
}

.vs__dropdown-menu {
  max-height: 225px;
  min-width: 100px !important;
  right: 0 !important;
  left: auto !important;
}

.must {
  width: 4px;
  height: 4px;
  margin-left: 2px;
  margin-top: 5px;
}

.vs__dropdown-toggle {
  border-radius: 5px;
}
</style>
