<template>

  <Head title="Report 6.3" />
  <PageBar>
    <p class="m-0 fs-4">Report 6.3</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.client_id, route().params.monthYear)" />
    <Link title="back" :href="route('report-client-data-list', { client_id: route().params.client_id })"
      class="nav-btn">
    <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <DataTableSkeleton row="5" col="4" v-if="empty(props.Report63)" />
  <DataTable :data="props.Report63" :search="props.search" :option="[5, 10, 15]" :removable="false" :searchable="false"
    v-else>
    <template #extra-tools>
      <div class="row">
        <div class="col-12">
          <div class="row gx-1">
            <div class="col-12 col-md-8 col-lg-4 col-xl-5 ms-auto mb-2 mb-lg-0">
              <SelectInfinite parentclass="m-0 p-0" :class="{ 'border border-danger rounded-2': !empty(err.item) }"
                placeholder="Select..." v-model="field.item" :fetched="fetchedItem" />
            </div>
            <div class="col-12 col-sm-4 col-md-4 col-lg-3 col-xl-2">
              <div class="btn-group gap-1 w-100">
                <Button type="button" class="btn btn-outline-secondary rounded-2" title="filter" @click="filterData">
                  <i class="bi bi-filter"></i>
                </Button>
                <Button type="button" class="btn btn-outline-secondary rounded-2" :disabled="field.isLoading"
                  title="Download" @click="exportFile">
                  <span v-if="field.isLoading">
                    <div class="spinner-border spinner-border-sm me-1" role="status"></div>
                  </span>
                  <span v-else>
                    <i class="bi bi-download"></i>
                  </span>
                </Button>
                <Button type="button" class="btn btn-outline-secondary rounded-2" title="reset"
                  @click="refresh(route().params.client_id, route().params.monthYear)">
                  <i class="bi bi-stars"></i>
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
    <template #thead>
      <th class="col-4">Details</th>
      <th class="col-2">Item | Quantity</th>
      <th class="col-3">Invoice | Date | Unit Price</th>
      <th class="col-2">Value | Vat | Total</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0" title="company">
          <span><i class="bi bi-buildings"></i> : </span>
          {{ td?.client?.company_name }}
        </p>
        <p class="m-0" title="bin">
          <span><i class="bi bi-database"></i> : </span>
          {{ td?.client?.bin }}
        </p>
      </td>
      <td>
        <p class="m-0" title="item">
          <span><i class="bi bi-bookmarks"></i> : </span>
          {{ td?.item?.name }}
        </p>
        <p class="m-0" title="hs code">
          <span><i class="bi bi-tag"></i> : </span>
          {{ td?.item?.hs_code }}
        </p>
        <p class="m-0" title="quantity">
          <span><i class="bi bi-beaker"></i> : </span>
          {{ td?.quantity }} {{ td?.unit?.name }}
        </p>
      </td>
      <td>
        <p class="m-0" title="invoice">
          <span><i class="bi bi-receipt"></i> : </span>
          {{ td?.invoice_no }}
        </p>
        <p class="m-0" title="date">
          <span><i class="bi bi-calendar"></i> : </span>
          {{ moment(td?.date).format("DD/MM/YYYY") }}
        </p>
        <p class="m-0" title="unit price">
          <span><i class="bi bi-coin"></i> : </span>
          {{ td?.unit_price }}
        </p>
      </td>
      <td>
        <p class="m-0" title="item price">
          <span><i class="bi bi-cash-coin"></i> : </span>
          {{ td?.item_price }}
        </p>
        <p class="m-0" title="vat">
          <span><i class="bi bi-cash"></i> : </span>
          {{ td?.vat_value }}
        </p>
        <p class="m-0" title="total price">
          <span><i class="bi bi-cash-stack"></i> : </span>
          {{ td?.total_price }}
        </p>
      </td>
      <td class="text-center">
        <div class="btn-group" role="group">
          <Link
            :href="route('report-63-view', { id: td?.id, client_id: route().params.client_id, monthYear: route().params.monthYear })"
            class="btn btn-outline-dark w-100 rounded-2">
          <i class="bi bi-eye"></i>
          </Link>
        </div>
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import moment from 'moment-timezone';
import { ref, reactive, inject, onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { empty, downloadExcel } from '../../../helpers/utils';
import { user, role } from '../../../composables/user';

import DataTableSkeleton from '../../../components/datatable/DataTableSkeleton.vue';
import DataTable from '../../../components/datatable/index.vue';
import Button from "../../../components/Button.vue";
import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import SelectInfinite from '../../../components/SelectInfinite.vue';
import Input from '../../../components/Input.vue';

const props = defineProps({
  Report63: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

const field = reactive({ item: '', monthYear: '' });
const err = reactive({ item: '', monthYear: '' });

// item dropdown
const fetchedItem = async () => {
  return {
    url: route('client-item-dropdown'),
    data: 'item',
    params: { client_id: route().params.client_id },
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` })
  };
};

// error show for filter data and download
const filterData = () => {
  const filters = {
    filter: true,
    item_id: field.item?.id ?? "",
  };
  // Clean out null/''/undefined
  const params = Object.fromEntries(Object.entries(filters).filter(([_, v]) => v !== null && v !== '' && v !== undefined));

  router.get(route('report-63', { client_id: route().params.client_id ?? "", monthYear: route().params.monthYear }), filters, {
    onSuccess: (e) => {
      err.item = "";
    },
    onError: (errors) => {
      let er = {};
      Object.keys(errors).map((key) => er[key] = errors[key].toString());
      err.item = er.item_id;
    },
    // only: ['form621-client-single-index'],
    preserveScroll: true,
    preserveState: true,
    replace: true
  });
};

const exportFile = async () => {
  err.item = "";
  field.isLoading = true;

  const param = { filter: true, client_id: route().params.client_id, item_id: field.item?.id, month_year: route().params.monthYear };

  try {
    await axios.post(route('report-63-excel'), param);
  } catch (e) {
    field.isLoading = false;
    const errors = e.response?.data?.errors || {};

    if (errors.err) {
      Alert(`<b class='text-danger'>${errors.err}</b>`);
    }

    err.item = errors.item_id?.[0] || "";

    if (Object.keys(errors).length) {
      err.notfound = true;
      setTimeout(() => (err.notfound = false), 3000);
    }
  }
};

// reload report 63
const refresh = (id, monthYear) => router.get(route('report-63', { client_id: id, monthYear: monthYear }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = (e) => {
    field.isLoading = false;
    e.fileName && downloadExcel('report-63-download-excel', e.fileName);
    router.reload({ only: ['Report63'], preserveState: true, preserveScroll: true })
  };

  window.Echo.private(`App.Models.User.${user.id}`).listen('.excelready', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>