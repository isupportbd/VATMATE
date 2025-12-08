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
          <!-- First -->
          <li :class="['page-item', { disabled: noPreviousPage }]">
            <Link class="page-link" href="#" role="button" :aria-disabled="noPreviousPage ? 'true' : 'false'"
              :tabindex="noPreviousPage ? -1 : 0" @click.prevent="!noPreviousPage && loadPage(1)">
              <i class="bi bi-chevron-double-left"></i>
            </Link>
          </li>

          <!-- Prev -->
          <li :class="['page-item', { disabled: noPreviousPage }]">
            <Link class="page-link" href="#" :aria-disabled="noPreviousPage ? 'true' : 'false'"
              :tabindex="noPreviousPage ? -1 : 0"
              @click.prevent="!noPreviousPage && loadPage(props.data.current_page - 1)">
              <i class="bi bi-chevron-left"></i>
            </Link>
          </li>

          <li class="page-item">
            <div class="input-group input-group-sm">
              <Input type="text" category="number" topclass="m-0" class="text-center" style="width: 100px"
                v-model="page.page" @keyup.enter="loadPage(page.page)" />
            </div>
          </li>

          <li class="page-item lastpage">
            <Link class="page-link text-nowrap" href="#">
              of {{ nFormatter(props.data.last_page) }}
            </Link>
          </li>

          <!-- Next -->
          <li :class="['page-item', { disabled: noNextPage }]">
            <Link class="page-link" href="#" :aria-disabled="noNextPage ? 'true' : 'false'"
              :tabindex="noNextPage ? -1 : 0" @click.prevent="!noNextPage && loadPage(props.data.current_page + 1)">
              <i class="bi bi-chevron-right"></i>
            </Link>
          </li>

          <!-- Last -->
          <li :class="['page-item', { disabled: noNextPage }]">
            <Link class="page-link" href="#" :aria-disabled="noNextPage ? 'true' : 'false'"
              :tabindex="noNextPage ? -1 : 0" @click.prevent="!noNextPage && loadPage(props.data.last_page)">
              <i class="bi bi-chevron-double-right"></i>
            </Link>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, watchEffect } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import Input from '../../../../components/Input.vue';

const props = defineProps({
  data: { type: [Array, Object] },
  selectedoption: { type: [String, Number] },
  search: { type: String }
});

const page = reactive({ page: '', from: '', to: '', total: '' });

const noPreviousPage = computed(() => props.data.current_page - 1 <= 0);
const noNextPage = computed(() => (props.data.current_page + 1) > props.data.last_page);

const loadPage = (page) => {
  let p = parseInt(page, 10);

  // fallback if NaN
  if (isNaN(p)) p = props.data.current_page;

  const last = Number(props.data.last_page) || 1;

  // clamp between 1 and last
  if (p < 1) p = 1;
  if (p > last) p = last;

  return router.visit(props.data.path, {
    data: { page: p, size: props.selectedoption, search: props.search, },
    preserveState: true,
    preserveScroll: true,
  });
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
ul.pagination {
  margin: 0;

  :deep(.page-item.disabled .page-link),
  :deep(.page-link:disabled),
  :deep(.page-link[aria-disabled="true"]) {
    pointer-events: none;
    cursor: not-allowed;
    opacity: 0.5;
  }

  .page-link {
    border-radius: 5px !important;
    margin-left: 0;
    border-color: #CDCDCD !important;
    color: inherit;
    box-shadow: none;
  }
}
</style>