<template>
  <div class="row">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
      <div class="py-2 text-black-50 text-center text-md-start">
        Showing {{ page.from }} to {{ page.to }} of {{ nFormatter(page.total) }} entries
      </div>
    </div>
    <div class="col-12 col-md-6">
      <nav class="datatable-pagination" aria-label="Page navigation">
        <ul class="pagination justify-content-center justify-content-md-end">
          <li :disabled="noPreviousPage" :class="{ 'page-item': noPreviousPage }">
            <Link class="page-link" href="#" role="button" @click="loadPage(1)">
            <i class='bx bx-chevrons-left'></i>
            </Link>
          </li>
          <li :disabled="noPreviousPage" :class="{ 'page-item': noPreviousPage }">
            <Link class="page-link" href="#" @click="loadPage(props.data.current_page - 1)">
            <i class='bx bx-chevron-left'></i>
            </Link>
          </li>
          <li class="page-item">
            <Input type="text" class="form-control shadow-none text-center" style="width: 100px;" v-model="page.page"
              @keyup.enter="loadPage(page.page)" />
          </li>
          <li class="page-item lastpage">
            <Link class="page-link text-nowrap" href="#">
            of {{ nFormatter(props.data.last_page) }}
            </Link>
          </li>
          <li :disabled="noNextPage" :class="{ 'page-item': noNextPage }">
            <Link class="page-link" href="#" @click="loadPage(props.data.current_page + 1)">
            <i class='bx bx-chevron-right'></i>
            </Link>
          </li>
          <li :disabled="noNextPage" :class="{ 'page-item': noNextPage }">
            <Link class="page-link" href="#" @click="loadPage(props.data.last_page)">
            <i class='bx bx-chevrons-right'></i>
            </Link>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, watchEffect } from 'vue';
import { router } from '@inertiajs/vue3';
import Input from '../../../components/Input.vue';

const props = defineProps({
  data: { type: [Array, Object] },
  selectedoption: { type: [String, Number] },
  filters: { type: [Array, Object] },
  search: { type: String }
});

const page = reactive({ page: '', from: '', to: '', total: '' });

const noPreviousPage = computed(() => props.data.current_page - 1 <= 0);
const noNextPage = computed(() => props.data.current_page + 1 > props.data.last_page);

const loadPage = (page) => {
  // console.log(page, props.selectedoption, props.area_id, props.tax_period, props.user_id);
  return router.visit(props.data.path, { data: { page: page, size: props.selectedoption, search: props.search, ...props.filters }, preserveState: true, preserveScroll: true });
};

const nFormatter = (num) => {
  if (num < 1e3) return num;
  if (num >= 1e3 && num < 1e6) return +(num / 1e3).toFixed(1) + "K";
  if (num >= 1e6 && num < 1e9) return +(num / 1e6).toFixed(1) + "M";
  if (num >= 1e9 && num < 1e12) return +(num / 1e9).toFixed(1) + "B";
  if (num >= 1e12) return +(num / 1e12).toFixed(1) + "T";
}

watchEffect(() => {
  // "Showing {$from} to {$to} of {$total} entries"
  page.page = props.data.current_page;
  page.from = (props.data.current_page - 1) * props.data.per_page + 1;
  page.to = Math.min(props.data.current_page * props.data.per_page, props.data.total);
  page.total = props.data.total;
});
</script>

<style lang="scss" scoped>
.pagination .page-link {
  margin-left: 0;
  border-color: #CDCDCD !important;
}
</style>