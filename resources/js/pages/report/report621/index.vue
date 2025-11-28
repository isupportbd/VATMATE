<template>

  <Head title="Report 6.2.1" />
  <PageBar>
    <p class="m-0 fs-4">Report 6.2.1</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh(route().params.client_id)" />
    <Link title="back" :href="route('report-client-data-list', { client_id: route().params.client_id })"
      class="nav-btn">
    <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <TableSkeleton row="5" col="4" v-if="empty(props.Report621)" />
  <TableData name="Form 6.2.1" :data="props.Report621" :item_id="props.item_id" :option="[5, 10, 15]" :removable="false"
    v-else>
    <template #extra>
      <div class="col-12">
        <div class="row gx-1">
          <div class="col-12 col-lg-4 col-xl-6 ms-auto mb-2 mb-lg-0">
            <SelectInfinite parentclass="m-0 p-0" :class="{ 'border border-danger rounded-2': !empty(err.item) }"
              placeholder="Select..." v-model="field.item" :fetched="fetchedItem" />
          </div>
          <div class="col-12 col-sm-8 col-lg-5 col-xl-4 mb-2 mb-lg-0">
            <div class="input-group" :class="{ 'border border-danger': !empty(err.from_date) }" role="group">
              <input type="date" class="form-control" placeholder="Ex: 2025-07" v-model="field.from_date" />
              <input type="date" class="form-control" placeholder="Ex: 2025-10" v-model="field.to_date" />
            </div>
          </div>
          <div class="col-12 col-sm-4 col-lg-3 col-xl-2">
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
                @click="refresh(route().params.client_id)">
                <i class="bi bi-stars"></i>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </template>
    <template #customhead>
      <thead>
        <tr>
          <th colspan="28" class="text-center fw-semibold">
            <p class="m-0">{{ props.clientdetail?.client?.company_name }}</p>
            <p class="m-0">{{ props.clientdetail?.client?.bin }}</p>
          </th>
        </tr>
        <tr class="align-middle text-center fw-normal">
          <th rowspan="2">#</th>
          <th rowspan="2">Date</th>
          <th colspan="2">Opening Stock</th>
          <th colspan="3">Purchase</th>
          <th rowspan="2">Total Stock</th>
          <th colspan="3">Seller Information</th>
          <th colspan="2">Purchase Challan/Bill</th>
          <th colspan="8">Sold/Supplied goods description</th>
          <th colspan="3">Purchaser Information</th>
          <th colspan="2">Sales Invoice</th>
          <th rowspan="2">Closing Stock</th>
          <th rowspan="2">Remarks</th>
        </tr>
        <tr class="align-middle text-center">
          <th>Item</th>
          <th>Quantity</th>
          <th>Quantity</th>
          <th>Rate</th>
          <th>Value (Excluding all type of taxes)</th>
          <th>Name</th>
          <th>Address</th>
          <th>Passport/NID</th>
          <th>Number</th>
          <th>Date</th>
          <th>Item</th>
          <th>Quantity</th>
          <th>Taxable Value (Excluding all type of taxes)</th>
          <th>Rate</th>
          <th>SD Rate(%)</th>
          <th>SD</th>
          <th>VAT Rate(%)</th>
          <th>VAT</th>
          <th>Name</th>
          <th>Address</th>
          <th>BIN/NID</th>
          <th>Number</th>
          <th>Date</th>
        </tr>
      </thead>
    </template>
    <template #custombody>
      <tbody v-for="(td, i) in props.Report621.data" :key="`${td.id}-${i}`">
        <tr class="text-center" v-if="td.importable_type == 'purchase'">
          <td class="text-center align-middle">
            {{
              props.Report621.current_page ? (props.Report621.per_page *
                (props.Report621.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td>{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td>{{ Number(td?.quantity).toFixed(2) }} {{ td?.unit?.name }}</td>
          <td>{{ Number(td?.importable?.unit_price).toFixed(2) }}</td>
          <td>{{ Number(td?.importable?.base_value).toFixed(2) }}</td>
          <td class="text-primary">{{ Number(td?.stock).toFixed(2) }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable?.exporter_name }}</td>
          <td>{{ td?.importable?.address }}</td>
          <td>{{ td?.importable?.bin_or_nid }}</td>
          <td>{{ td?.importable?.boe }}</td>
          <td>{{ moment(td?.importable?.boe_date).format('DD/MM/YYYY') }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.stock }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable_type }}</td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'sale'">
          <td class="text-center align-middle">
            {{
              props.Report621.current_page ? (props.Report621.per_page *
                (props.Report621.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td>{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td class="text-danger">{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.item?.name }} <br /> {{ td?.item?.hs_code }}</td>
          <td>{{ td?.quantity }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable?.total_price }}</td>
          <td>{{ td?.importable?.unit_price }}</td>
          <td>{{ td?.importable?.sd }}%</td>
          <td>{{ td?.importable?.sd_value }}</td>
          <td>{{ td?.importable?.vat }}%</td>
          <td>{{ td?.importable?.vat_value }}</td>
          <td>{{ td?.importable?.customer_name }}</td>
          <td>{{ td?.importable?.address }}</td>
          <td>{{ td?.importable?.bin_or_nid }}</td>
          <td>{{ td?.importable?.invoice_no }}</td>
          <td>{{ moment(td?.importable?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.stock }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable_type }}</td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'mushak67'">
          <td class="text-center align-middle">
            {{
              props.Report621.current_page ? (props.Report621.per_page *
                (props.Report621.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td>{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td>{{ Number(td?.importable?.quantity_of_decreasing_adjustment).toFixed(2) }} {{ td?.unit?.name }}</td>
          <td>{{ Number(td?.importable?.rate_in_challan).toFixed(2) }}</td>
          <td>{{ Number(td?.importable?.value_of_decreasing_adjustment).toFixed(2) }}</td>
          <td class="text-primary">{{ Number(td?.stock).toFixed(2) }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.importable?.creditnote_invoice }}</td>
          <td>{{ moment(td?.importable?.issued_date).format('DD/MM/YYYY') }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.stock }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable_type }}</td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'mushak68'">
          <td class="text-center align-middle">
            {{
              props.Report621.current_page ? (props.Report621.per_page *
                (props.Report621.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td>{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td class="text-danger">{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.item?.name }} <br /> {{ td?.item?.hs_code }}</td>
          <td>{{ td?.importable?.quantity_of_increasing_adjustment }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable?.value_of_increasing_adjustment }}</td>
          <td>{{ td?.importable?.rate_in_challan }}</td>
          <td></td>
          <td>{{ td?.importable?.sd_of_increasing_adjustment }}</td>
          <td></td>
          <td>{{ td?.importable?.vat_of_increasing_adjustment }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.importable?.debitnote_invoice }}</td>
          <td>{{ moment(td?.importable?.issued_date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.stock }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable_type }}</td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'mushak44'">
          <td class="text-center align-middle">
            {{
              props.Report621.current_page ? (props.Report621.per_page *
                (props.Report621.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td>{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td class="text-danger">{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.item?.name }} <br /> {{ td?.item?.hs_code }}</td>
          <td>{{ td?.importable?.quantity }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable?.value }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.importable?.vat }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.importable?.form44_invoice }}</td>
          <td>{{ moment(td?.importable?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.stock }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable_type }}</td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'mushak45'">
          <td class="text-center align-middle">
            {{
              props.Report621.current_page ? (props.Report621.per_page *
                (props.Report621.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td>{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td class="text-danger">{{ td?.opening_stock }} {{ td?.unit?.name }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.item?.name }} <br /> {{ td?.item?.hs_code }}</td>
          <td>{{ td?.importable?.quantity }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable?.value }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.importable?.vat }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.importable?.form45_invoice }}</td>
          <td>{{ moment(td?.importable?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.stock }} {{ td?.unit?.name }}</td>
          <td>{{ td?.importable_type }}</td>
        </tr>
      </tbody>
    </template>
  </TableData>
</template>

<script setup>
import moment from 'moment';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { reactive, ref, inject, onMounted, onBeforeUnmount } from 'vue';
import { empty, downloadExcel } from '../../../helpers/utils';
import { user } from '../../../composables/user';

import PageBar from "../../../components/PageBar.vue";
import Button from '../../../components/Button.vue';
import Refresh from '../../../components/Refresh.vue';
import TableSkeleton from './table/TableSkeleton.vue';
import TableData from "./table/index.vue";
import SelectInfinite from '../../../components/SelectInfinite.vue';
import Alert from '../../../helpers/customAlert';

const notify = inject('showToast'); // taost

const field = reactive({ item: '', from_date: '', to_date: '' });
const err = reactive({ item: '', from_date: '', to_date: '' });

const props = defineProps({
  Report621: { type: [Object, Array] },
  clientdetail: { type: Object },
  item_id: { type: [String, Number] },
  from_date: { type: String },
  to_date: { type: String },
});

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
    from_date: field.from_date ?? "",
    to_date: field.to_date ?? "",
  };
  // Clean out null/''/undefined
  const params = Object.fromEntries(Object.entries(filters).filter(([_, v]) => v !== null && v !== '' && v !== undefined));

  router.get(route('report-621', { client_id: route().params.client_id ?? "" }), filters, {
    onSuccess: (e) => {
      err.item = "";
      err.from_date = "";
      err.to_date = "";
    },
    onError: (errors) => {
      let er = {};
      Object.keys(errors).map((key) => er[key] = errors[key].toString());
      err.item = er.item_id;
      err.from_date = er.from_date;
      err.to_date = er.to_date;
    },
    // only: ['form621-client-single-index'],
    preserveScroll: true,
    preserveState: true,
    replace: true
  });
};

const exportFile = async () => {
  err.item = err.from_date = err.to_date = "";
  field.isLoading = true;

  const param = { filter: true, client_id: route().params.client_id, item_id: field.item?.id };

  // send only if not empty
  if (field.from_date) param.from_date = field.from_date;
  if (field.to_date) param.to_date = field.to_date;

  try {
    await axios.post(route('report-621-excel'), param);
  } catch (e) {
    field.isLoading = false;
    const errors = e.response?.data?.errors || {};

    if (errors.err) {
      Alert(`<b class='text-danger'>${errors.err}</b>`);
    }

    err.item = errors.item_id?.[0] || "";
    err.from_date = errors.from_date?.[0] || "";
    err.to_date = errors.to_date?.[0] || "";

    if (Object.keys(errors).length) {
      err.notfound = true;
      setTimeout(() => (err.notfound = false), 3000);
    }
  }
};

onMounted(() => {
  window.Echo.private(`App.Models.User.${user.id}`).listen('.excelready', (e) => {
    field.isLoading = false;
    downloadExcel('report-621-download-excel', e.fileName);
  });
});

onBeforeUnmount(() => {
  window.Echo.leave(`private-App.Models.User.${user.id}`);
});
// refresh load
const refresh = (id) => router.get(route('report-621', { client_id: id }), {}, { preserveScroll: true });
</script>

<style lang="scss" scoped></style>