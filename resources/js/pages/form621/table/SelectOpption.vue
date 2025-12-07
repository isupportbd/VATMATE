<template>
  <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2 mb-md-0">
    Show
    <select class="form-select shadow-none w-auto mx-2" v-model="selectedoption" @change="selectOption">
      <option v-for="(val, i) in props.option" :key="i" :value="val">{{ val }}</option>
    </select>
    entries
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  data: { type: [Object, Array] },
  option: { type: Array, default: [] },
  selectedoption: { type: Number },
  filters: { type: [Array, Object] },
  search: { type: String }
});

// select option and pass SelectOption component
const selectedoption = ref(props.selectedoption);
const selectOption = (e) => {
  props.option.value = e.target.value;
  return router.visit(props.data.path, { data: { page: props.data.current_page, size: props.option.value, search: props.search, ...props.filters }, preserveState: true, preserveScroll: true });
};
</script>

<style lang="scss" scoped></style>