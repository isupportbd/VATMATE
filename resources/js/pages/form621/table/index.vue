<template>
  <div class="card border h-100">
    <div class="card-header">
      <!-- <div class="row align-items-center gx-1">
        <div class="col-12 col-md-6">
          <SelectOption :option="props.option" :data="props.data" :selectedoption="selectedoption" :search="searchdata"
            :filters="props.filters" />
        </div>
        <div class="col-12 col-md-6">
          <div class="d-flex align-items-center justify-content-center justify-content-md-end">
            <Button class="btn btn-outline- secondary rounded-0 rounded-start" type="button" @click="searchMe">
              <i class='bi bi-search'></i>
            </Button>
            <Input type="text" class="form-control w-auto rounded-0 rounded-end" topclass="mb-0" placeholder="Search..."
              v-model="searchdata" @keyup.enter="searchMe" @keyup.delete="searchMe" />
          </div>
        </div>
      </div> -->
      <div class="row align-items-center gx-1">
        <div class="col-12 col-md-4 col-lg-3 col-xl-2">
          <SelectOption :option="props.option" :data="props.data" :selectedoption="selectedoption"
            :item_id="props.item_id" />
        </div>
        <div class="col-12 col-md-8 col-lg-9 col-xl-10">
          <slot name="extra"></slot>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped table-sm align-middle m-0 datatable-general">
          <!-- <thead>
            <tr>
              <th class="text-center" :class="props.countable ? 'align-middle' : 'd-none'">#</th>
              <slot name="customhead"></slot>
            </tr>
          </thead> -->
          <slot name="customhead"></slot>
          <!-- <tbody>
            <tr v-for="(dt, i) in (props.data.current_page ? (props.loop ? props.loop : props.data.data) : props.data)"
              :key="`${dt.id}-${i}`">
              <td class="text-center" :class="props.countable ? 'align-middle' : 'd-none'">
                {{
                  props.data.current_page ? (props.data.per_page * (props.data.current_page - 1)) + (i + 1) : (i + 1)
                }}
              </td>
              <slot name="custombody" :td="dt"></slot>
            </tr>
          </tbody> -->
          <slot name="custombody"></slot>
        </table>
      </div>
    </div>
    <div class="card-footer" :class="{ 'd-none': props.data.total <= props.data.data?.length }">
      <Pagination :data="props.data" :selectedoption="selectedoption" :search="searchdata" :filters="props.filters" />
    </div>
  </div>
</template>

<script setup>
import { ref, watchEffect } from 'vue';
import { debounce } from 'lodash-es';
import { router } from '@inertiajs/vue3';

import SelectOption from "./SelectOpption.vue";
import Pagination from "./Pagination.vue";
import Input from '../../../components/Input.vue';
import Button from '../../../components/Button.vue';

const props = defineProps({
  data: { type: [Array, Object], default: [] },
  loop: { type: [Array, Object, Boolean] },
  option: { type: Array, default: [] },
  countable: { type: Boolean, default: true },
  filters: { type: [Array, Object] },
  search: { type: String }
});

// for change data size show
const selectedoption = ref();

const searchdata = ref(props.search || "");
const searchMe = debounce(() => {
  const isSearching = !!searchdata.value && searchdata.value.trim() !== '';
  return router.get(route('form621-client-single-index'), {
    page: isSearching ? 1 : props.data.current_page, // Go to page 1 if searching
    size: selectedoption.value,
    search: searchdata.value,
    ...props.filters
  }, {
    preserveState: true,
    preserveScroll: true
  })
}, 500);

// watch instantce
watchEffect(() => {
  selectedoption.value = props.option[props.option.indexOf(props.data.per_page)];
});
</script>

<style lang="scss" scoped>
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