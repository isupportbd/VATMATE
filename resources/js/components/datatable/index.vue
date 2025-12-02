<template>
  <div class="card border h-100">
    <div class="card-header" :class="{ 'd-none': !(props.removable || props.searchable || props.optionable) }">
      <div class="row align-items-center gx-1">
        <div class="col-12 col-md-3">
          <SelectOption :isOptions="props.optionable" :option="props.option" :data="props.data"
            :selectedoption="selectedoption" :search="searchdata" />
        </div>
        <div class="col-12 col-md-9">
          <slot name="extra-tools"></slot>
          <div class="d-flex align-items-center justify-content-center justify-content-md-end">
            <div class="me-1" :class="{ 'd-none': !props.removable }" v-if="checked.checkcolumn.length > 0">
              <Button type="button" class="btn btn-outline-danger" @click.prevent="remove">
                <i class="bi bi-trash"></i>
              </Button>
            </div>
            <div class="d-flex align-items-center" :class="{ 'd-none': !props.searchable }">
              <Button class="btn btn-outline-secondary rounded-0 rounded-start" type="button" @click="searchMe">
                <i class='bi bi-search'></i>
              </Button>
              <Input type="text" class="form-control w-auto rounded-0 rounded-end" topclass="mb-0"
                placeholder="Search..." v-model="searchdata" @keyup.enter="searchMe" @keyup.delete="searchMe" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body">
      <slot name="extra"></slot>
      <div class="table-responsive">
        <table
          class="table table-bordered table-striped border-custom table-sm align-middle m-0 datatable no-shrink-table">

          <slot name="customhead"></slot>
          <thead :class="{ 'd-none': slots.customhead }">
            <tr>
              <th class="align-middle" :class="{ 'd-none': !props.removable || !props.data.current_page }">
                <Checkbox topclass="ms-2" v-model="checked.check" :value="checked.check" @click="checkAll"
                  :disabled="props.disabled" />
              </th>
              <th class="text-center align-middle" :class="{ 'd-none': !props.countable }">#</th>
              <slot name="thead"></slot>
            </tr>
          </thead>

          <slot name="custombody"></slot>
          <tbody :class="{ 'd-none': slots.custombody }">
            <tr v-for="(dt, i) in (props.data.current_page ? (props.loop ? props.loop : props.data.data) : props.data)"
              :key="`${dt.id}-${i}`">
              <td class="align-middle" :class="{ 'd-none': !props.removable || !props.data.current_page }">
                <Checkbox topclass="ms-2" :value="dt.id" v-model="checked.checkcolumn" @change="updateChecked"
                  :disabled="props.disabled" />
              </td>
              <td class="text-center align-middle" :class="{ 'd-none': !props.countable }">
                {{
                  props.data.current_page ? (props.data.per_page * (props.data.current_page - 1)) + (i + 1) : (i + 1)
                }}
              </td>
              <slot name="tbody" :td="dt"></slot>
            </tr>
          </tbody>

        </table>
      </div>
    </div>
    <div class="card-footer" :class="{ 'd-none': props.data.total <= props.data.data?.length }">
      <Pagination :data="props.data" :selectedoption="selectedoption" :search="searchdata" />
      {{ props.data.total <= props.data.data?.length }} </div>
    </div>
</template>

<script setup>
import { reactive, ref, useSlots, watchEffect } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';

import Checkbox from "../Checkbox.vue";
import Button from "../Button.vue";
import SelectOption from "./SelectOpption.vue";
import Pagination from "./Pagination.vue";

import Input from '../Input.vue';

const props = defineProps({
  data: { type: [Array, Object], default: [] },
  search: { type: String, default: "" },
  loop: { type: [Array, Object, Boolean] },
  option: { type: Array, default: [] },
  removable: { type: Boolean, default: true },
  countable: { type: Boolean, default: true },
  searchable: { type: Boolean, default: true },
  optionable: { type: Boolean, default: true },
});

const slots = useSlots();

const emit = defineEmits(['remove']);

// checkbox select
let checked = reactive({ check: false, checkcolumn: [] });
const checkAll = () => {
  if (!checked.check) {
    props.data.data.forEach((dt) => {
      if (!checked.checkcolumn.includes(dt.id)) {
        checked.checkcolumn.push(dt.id);
      }
    });
  } else {
    checked.checkcolumn = [];
  }
};
const updateChecked = () => (checked.checkcolumn.length == props.data.data.length) ? checked.check = true : checked.check = false;

// remove from parent
const remove = () => {
  emit('remove', checked.checkcolumn);
  checked.check = false;
};

// for change data size show
const selectedoption = ref();

// search data
const searchdata = ref(props.search || "");
const searchMe = debounce(() => {
  const isSearching = !!searchdata.value && searchdata.value.trim() !== '';
  router.visit(props.data.path, {
    data: {
      page: isSearching ? 1 : props.data.current_page, // Go to page 1 if searching
      size: selectedoption.value,
      search: searchdata.value
    },
    preserveState: true,
    preserveScroll: true
  });
}, 500);

// watch instantce
watchEffect(() => {
  selectedoption.value = props.option[props.option.indexOf(props.data.per_page)];
});
</script>

<style lang="scss" scoped>
.border-custom {
  border-color: #CCCCCC !important;
}

.no-shrink-table th,
.no-shrink-table td {
  white-space: nowrap;
}

/* By default (large screens and up) do not force a min-width:
   allow table to size normally so no horizontal scrollbar on large screens */
@media (min-width: 992px) {
  .no-shrink-table {
    min-width: 0;
  }
}

@media (max-width: 991.98px) {
  .no-shrink-table {
    min-width: 1000px;
  }
}
</style>