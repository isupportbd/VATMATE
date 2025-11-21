<template>

  <Head title="From 6.2.1" />
  <PageBar>
    <p class="m-0 fs-4">From 6.2.1</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh(route().params.client_id)" />
    <Link title="back" :href="route('form621')" class="nav-btn">
    <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <!-- purchase view -->
  <Modal id="viewpurchase" ref="modalviewpurchase" title="Purchase Details" size="xl">
    <template #modalbody>
      <ViewPurchase :view="singlePurchase" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeViewPurchase">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- purchase edit -->
  <Modal id="editpurchase" ref="modaleditpurchase" title="Edit Purchase" size="xl">
    <template #modalbody>
      <EditPurchase ref="purchaseRef" :modal="modaleditpurchase" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeEditPurchase">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- exporter edit for sale -->
  <Modal id="editexporter" ref="modalexporteredit" title="Edit Exporter Info" size="md">
    <template #modalbody>
      <ExporterEdit ref="exporterRef" :modal="modalexporteredit" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeExporterEdit">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- sale view -->
  <Modal id="viewsale" ref="modalviewsale" title="Sale Details" size="xl">
    <template #modalbody>
      <ViewSale :view="singleSale" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeViewSale">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- sale edit -->
  <Modal id="editsale" ref="modaleditsale" title="Edit Sale" size="xl">
    <template #modalbody>
      <EditSale ref="saleRef" :modal="modaleditsale" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeEditSale">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- customer edit for sale -->
  <Modal id="customeredit" ref="modalcustomeredit" title="Edit Customer Info" size="lg">
    <template #modalbody>
      <CustomerEdit ref="customerRef" :modal="modalcustomeredit" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeCustomerEdit">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>

  <TableSkeleton row="5" col="4" v-if="empty(props.Form621ClientTable)" />
  <TableData name="Form 6.2.1" :data="props.Form621ClientTable" :item_id="props.item_id" :option="[5, 10, 15]"
    :removable="false" v-else>
    <template #extra>
      <div class="col-12">
        <div class="row gx-1">
          <div class="col-12 col-sm-10 col-lg-10 col-xl-6 ms-auto mb-3 mb-sm-0">
            <SelectInfinite parentclass="m-0 p-0" :class="{ 'border border-danger': err.item }" placeholder="Select..."
              v-model="field.item" :fetched="fetchedItem" :err="err.item" />
          </div>
          <div class="col-2 col-sm-2 col-lg-2 col-xl-1">
            <div class="d-flex">
              <Button type="button" class="btn btn-outline-secondary me-1" title="reset" @click="filterData">
                <i class="bi bi-filter"></i>
              </Button>
              <Button type="button" class="btn btn-outline-secondary" title="reset"
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
          <th colspan="24" class="text-center fw-semibold">
            <p class="m-0">{{ props.clientdetail?.client?.company_name }}</p>
            <p class="m-0">{{ props.clientdetail?.client?.bin }}</p>
          </th>
        </tr>
        <tr class="align-middle text-center fw-normal">
          <th rowspan="2">#</th>
          <th rowspan="2">Date</th>
          <th rowspan="2">Item</th>
          <th colspan="3">Purchase</th>
          <th colspan="2">Total Stock</th>
          <th colspan="3">Seller Information</th>
          <th colspan="2">Purchase Challan/Bill</th>
          <th colspan="5">Sold/Supplied goods description</th>
          <th colspan="3">Purchaser Information</th>
          <th colspan="2">Sales Invoice</th>
          <th rowspan="2">Action</th>
        </tr>
        <tr class="align-middle text-center">
          <th>Quantity</th>
          <th>Rate</th>
          <th>Value (Excluding all type of taxes)</th>
          <th>Quantity</th>
          <th>Value (Excluding all type of taxes)</th>
          <th>Name</th>
          <th>Address</th>
          <th>Passport/NID</th>
          <th>Number</th>
          <th>Date</th>
          <th>Quantity</th>
          <th>Taxable Value (Excluding all type of taxes)</th>
          <th>Rate</th>
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
      <tbody v-for="(td, i) in props.Form621ClientTable.data" :key="`${td.id}-${i}`">
        <tr class="text-center" v-if="td.importable_type == 'purchase'">
          <td class="text-center align-middle">
            {{
              props.Form621ClientTable.current_page ? (props.Form621ClientTable.per_page *
                (props.Form621ClientTable.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td>{{ Number(td?.importable?.total_quantity).toFixed(2) }} {{ td?.importable?.unit?.name }}</td>
          <td>{{ Number(td?.importable?.unit_price).toFixed(2) }}</td>
          <td>{{ Number(td?.importable?.base_value).toFixed(2) }}</td>
          <td class="text-primary">{{ Number(td?.stock).toFixed(2) }} {{ td?.importable?.unit?.name }}</td>
          <td class="text-primary">{{ Number(Number(td?.stock) * Number(td?.importable?.unit_price)).toFixed(3) }}</td>
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
          <td>
            <div class="btn-group">
              <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark rounded-2" title="details"
                @click="viewPurchase(td.importable_id)" />
              <Button type="button" icon="bi bi-person-square" class="btn btn-outline-dark rounded-2"
                title="edit exporter" @click="editExporter(td.importable_id)" />
            </div>
            <div class="btn-group" :class="{ 'd-none': !td?.last }" role="group">
              <Button type="button" icon="bi bi-pencil-square" class="btn btn-outline-dark rounded-2" title="edit"
                @click="editPurchase(td.importable_id)" />
              <Button type="button" class="btn btn-outline-danger rounded-2" icon="bi bi-trash"
                @click="delPurchase(td.id)" />
            </div>
          </td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'sale'">
          <td class="text-center align-middle">
            {{
              props.Form621ClientTable.current_page ? (props.Form621ClientTable.per_page *
                (props.Form621ClientTable.current_page - 1)) + (i + 1) : (i + 1)
            }}
          </td>
          <td>{{ moment(td?.date).format('DD/MM/YYYY') }}</td>
          <td>{{ td?.item?.name }}<br>{{ td?.item?.hs_code }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td class="text-danger">{{ td?.stock }} {{ td?.importable?.unit?.name }}</td>
          <td class="text-danger">{{ (+(td?.stock) * +(td?.importable?.last_purchase_unit_price)).toFixed(2) }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{ td?.importable?.quantity }} {{ td?.importable?.unit?.name }}</td>
          <td>{{ td?.importable?.total_price }}</td>
          <td>{{ td?.importable?.unit_price }}</td>
          <td>{{ td?.importable?.vat }}%</td>
          <td>{{ td?.importable?.vat_value }}</td>
          <td>{{ td?.importable?.customer_name }}</td>
          <td>{{ td?.importable?.address }}</td>
          <td>{{ td?.importable?.bin_or_nid }}</td>
          <td>{{ td?.importable?.invoice_no }}</td>
          <td>{{ moment(td?.importable?.date).format('DD/MM/YYYY') }}</td>
          <td>
            <div class="btn-group" role="group">
              <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark rounded-2" title="details"
                @click="viewSale(td.importable_id)" />
              <Button type="button" icon="bi bi-person-bounding-box" class="btn btn-outline-dark rounded-2"
                title="edit customer" @click="editCustomer(td.importable_id)" />
            </div>
            <div class="btn-group" role="group" :class="{ 'd-none': !td?.last }">
              <Button type="button" icon="bi bi-pencil-square" class="btn btn-outline-dark rounded-2" title="edit"
                @click="editSale(td.importable_id)" />
              <Button type="button" class="btn btn-outline-danger rounded-2" icon="bi bi-trash"
                @click="delSale(td.id)" />
            </div>
          </td>
        </tr>
      </tbody>
    </template>
  </TableData>
</template>

<script setup>
import moment from 'moment';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { reactive, ref, inject } from 'vue';
import { empty } from '../../helpers/utils';

import PageBar from "../../components/PageBar.vue";
import Button from '../../components/Button.vue';
import Refresh from '../../components/Refresh.vue';
import TableSkeleton from './table/TableSkeleton.vue';
import TableData from "./table/index.vue";
import SelectInfinite from '../../components/SelectInfinite.vue';
import Modal from '../../components/Modal.vue';
import Spinner from '../../components/Spinner.vue';
import ViewPurchase from './purchases/ViewPurchase.vue';
import EditPurchase from './purchases/EditPurchase.vue';
import ExporterEdit from './purchases/ExporterEdit.vue';
import ViewSale from './sales/ViewSale.vue';
import EditSale from './sales/EditSale.vue';
import CustomerEdit from './sales/CustomerEdit.vue';

import Alert from '../../helpers/customAlert';
import Confirm from '../../helpers/customConfirm';

const notify = inject('showToast'); // taost

const field = reactive({ item: '', from_date: '', to_date: '' });
const err = reactive({ item: '' });

const props = defineProps({
  Form621ClientTable: { type: [Object, Array] },
  size: { type: [String, Number] },
  item_id: { type: [String, Number] },
  clientdetail: { type: Object },
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
    client_id: route().params.client_id ?? "",
    item_id: field.item?.id ?? "",
  };
  // Clean out null/''/undefined
  const params = Object.fromEntries(Object.entries(filters).filter(([_, v]) => v !== null && v !== '' && v !== undefined));

  router.get(route('form621-client-single-index', { client_id: filters.client_id }), { filter: true, item_id: filters.item_id }, {
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

// modal popup
const modalviewpurchase = ref();
const modaleditpurchase = ref();
const modalexporteredit = ref();
const modalviewsale = ref();
const modaleditsale = ref();
const modalcustomeredit = ref();
const closeViewPurchase = () => modalviewpurchase.value.close();
const closeEditPurchase = () => modaleditpurchase.value.close();
const closeExporterEdit = () => modalexporteredit.value.close();
const closeViewSale = () => modalviewsale.value.close();
const closeEditSale = () => modaleditsale.value.close();
const closeCustomerEdit = () => modalcustomeredit.value.close();

// purchase view and edit
const singlePurchase = ref();
const purchaseRef = ref(null);
const exporterRef = ref(null);
const singleSale = ref();
const saleRef = ref(null);
const customerRef = ref(null);
const viewPurchase = async (id) => await axios.get(route('purchase-single', { id: id })).then((res) => {
  if (res.data?.error) {
    modalviewpurchase.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalviewpurchase.value.open();
    singlePurchase.value = res.data.purchase;
  }
});
const editPurchase = async (id) => await axios.get(route('purchase-single', { id })).then((res) => {
  if (res.data?.error) {
    modaleditpurchase.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modaleditpurchase.value.open();
    purchaseRef.value?.setForm(res.data);
  }
});
const editExporter = async (id) => await axios.get(route('purchase-single', { id })).then((res) => {
  if (res.data?.error) {
    modalexporteredit.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalexporteredit.value.open();
    exporterRef.value?.setForm(res.data?.purchase);
  }
});
const viewSale = async (id) => await axios.get(route('sale-single', { id: id })).then((res) => {
  if (res.data?.error) {
    modalviewsale.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalviewsale.value.open();
    singleSale.value = res.data.sale;
  }
});
const editSale = async (id) => await axios.get(route('sale-single', { id: id })).then((res) => {
  if (res.data?.error) {
    modaleditsale.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modaleditsale.value.open();
    saleRef.value?.setForm(res.data?.sale);
  }
});
const editCustomer = async (id) => await axios.get(route('sale-single', { id })).then((res) => {
  if (res.data?.error) {
    modalcustomeredit.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalcustomeredit.value.open();
    customerRef.value?.setForm(res.data?.sale);
  }
});

// delete purchase from localpurchase and local621 table
const delPurchase = async (ids) => {
  Confirm('Do you want to delete ?', async (res) => {
    if (res) {
      router.delete(route('purchase-delete', { id: ids }), {
        preserveScroll: true,
        onSuccess: (e) => {
          if (e.props.flash.err) {
            Alert(`<b class='text-danger'>${e.props.flash.err}</b>`);
          } else {
            notify(e.props.flash.msg, 'danger');
          }
        }
      });
    }
  });
};
// delete purchase from localsale and local621 table
const delSale = async (ids) => {
  Confirm('Do you want to delete ?', async (res) => {
    if (res) {
      router.delete(route('sale-delete', { id: ids }), {
        preserveScroll: true,
        onSuccess: (e) => {
          if (e.props.flash.err) {
            Alert(`<b class='text-danger'>${e.props.flash.err}</b>`);
          } else {
            notify(e.props.flash.msg, 'danger');
          }
        }
      });
    }
  });
};

// after update notification
// const notification = (notification) => notify(notification, 'success');

// refresh load
const refresh = (id) => router.get(route('form621-client-single-index', { client_id: id }), {}, { preserveScroll: true });
</script>

<style lang="scss" scoped></style>