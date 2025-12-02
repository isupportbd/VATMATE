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
  <!-- creditnote view -->
  <Modal id="viewcreditnote" ref="modalviewcreditnote" title="Credit Note (6.7) Details" size="xl">
    <template #modalbody>
      <ViewCreditnote :view="singleCreditnote" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeViewCreditnote">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- creditnote edit -->
  <Modal id="editcreditnote" ref="modaleditcreditnote" title="Edit Credit Note 6.7" size="xl">
    <template #modalbody>
      <EditCreditnote ref="creditnoteRef" :modal="modaleditcreditnote" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeEditCreditnote">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- debitnote view -->
  <Modal id="viewdebitnote" ref="modalviewdebitnote" title="Debit Note (6.8) Details" size="xl">
    <template #modalbody>
      <ViewDebitnote :view="singleDebitnote" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeViewDebitnote">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- debitnote edit -->
  <Modal id="editdebitnote" ref="modaleditdebitnote" title="Edit Debit Note 6.8" size="xl">
    <template #modalbody>
      <EditDebitnote ref="debitnoteRef" :modal="modaleditdebitnote" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeEditDebitnote">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- form44 view -->
  <Modal id="viewform44" ref="modalviewform44" title="Form 4.4 Details" size="xl">
    <template #modalbody>
      <ViewForm44 :view="singleForm44" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeViewForm44">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- form44 edit -->
  <Modal id="editform44" ref="modaleditform44" title="Edit Form 4.4" size="xl">
    <template #modalbody>
      <EditForm44 ref="form44Ref" :modal="modaleditform44" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeEditForm44">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- form45 view -->
  <Modal id="viewform45" ref="modalviewform45" title="Form 4.5 Details" size="xl">
    <template #modalbody>
      <ViewForm45 :view="singleForm45" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeViewForm45">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>
  <!-- form45 edit -->
  <Modal id="editform45" ref="modaleditform45" title="Edit Form 4.5" size="xl">
    <template #modalbody>
      <EditForm45 ref="form45Ref" :modal="modaleditform45" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeEditForm45">
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
              <Button type="button" class="btn btn-outline-secondary me-1" title="filter" @click="filterData">
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
          <th rowspan="2">Action</th>
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
          <td>
            <div class="btn-group">
              <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark rounded-2" title="details"
                @click="viewPurchase(td.importable_id)" />
              <Button type="button" icon="bi bi-person-vcard" class="btn btn-outline-dark rounded-2"
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
        <tr class="text-center" v-if="td.importable_type == 'mushak67'">
          <td class="text-center align-middle">
            {{
              props.Form621ClientTable.current_page ? (props.Form621ClientTable.per_page *
                (props.Form621ClientTable.current_page - 1)) + (i + 1) : (i + 1)
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
          <td>
            <div class="btn-group">
              <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark rounded-2" title="details"
                @click="viewCreditnote(td?.importable_id)" />
            </div>
            <div class="btn-group" :class="{ 'd-none': !td?.last }" role="group">
              <Button type="button" icon="bi bi-pencil-square" class="btn btn-outline-dark rounded-2" title="edit"
                @click="editCreditnote(td?.importable_id)" />
              <Button type="button" class="btn btn-outline-danger rounded-2" icon="bi bi-trash"
                @click="delCreditnote(td?.id)" />
            </div>
          </td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'mushak68'">
          <td class="text-center align-middle">
            {{
              props.Form621ClientTable.current_page ? (props.Form621ClientTable.per_page *
                (props.Form621ClientTable.current_page - 1)) + (i + 1) : (i + 1)
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
          <td>
            <div class="btn-group">
              <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark rounded-2" title="details"
                @click="viewDebitnote(td?.importable_id)" />
            </div>
            <div class="btn-group" :class="{ 'd-none': !td?.last }" role="group">
              <Button type="button" icon="bi bi-pencil-square" class="btn btn-outline-dark rounded-2" title="edit"
                @click="editDebitnote(td?.importable_id)" />
              <Button type="button" class="btn btn-outline-danger rounded-2" icon="bi bi-trash"
                @click="delDebitnote(td?.id)" />
            </div>
          </td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'mushak44'">
          <td class="text-center align-middle">
            {{
              props.Form621ClientTable.current_page ? (props.Form621ClientTable.per_page *
                (props.Form621ClientTable.current_page - 1)) + (i + 1) : (i + 1)
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
          <td>
            <div class="btn-group">
              <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark rounded-2" title="details"
                @click="viewForm44(td?.importable_id)" />
            </div>
            <div class="btn-group" :class="{ 'd-none': !td?.last }" role="group">
              <Button type="button" icon="bi bi-pencil-square" class="btn btn-outline-dark rounded-2" title="edit"
                @click="editForm44(td?.importable_id)" />
              <Button type="button" class="btn btn-outline-danger rounded-2" icon="bi bi-trash"
                @click="delForm44(td?.id)" />
            </div>
          </td>
        </tr>
        <tr class="text-center" v-if="td.importable_type == 'mushak45'">
          <td class="text-center align-middle">
            {{
              props.Form621ClientTable.current_page ? (props.Form621ClientTable.per_page *
                (props.Form621ClientTable.current_page - 1)) + (i + 1) : (i + 1)
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
          <td>
            <div class="btn-group">
              <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark rounded-2" title="details"
                @click="viewForm45(td?.importable_id)" />
            </div>
            <div class="btn-group" :class="{ 'd-none': !td?.last }" role="group">
              <Button type="button" icon="bi bi-pencil-square" class="btn btn-outline-dark rounded-2" title="edit"
                @click="editForm45(td?.importable_id)" />
              <Button type="button" class="btn btn-outline-danger rounded-2" icon="bi bi-trash"
                @click="delForm45(td?.id)" />
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
import { reactive, ref, inject, onMounted, onBeforeUnmount } from 'vue';
import { empty } from '../../helpers/utils';
import { user, role } from "../../composables/user";

import PageBar from "../../components/PageBar.vue";
import Button from '../../components/Button.vue';
import Refresh from '../../components/Refresh.vue';
import TableSkeleton from './table/TableSkeleton.vue';
import TableData from "./table/index.vue";
import SelectInfinite from '../../components/SelectInfinite.vue';
import Modal from '../../components/Modal.vue';
import ViewPurchase from './purchases/ViewPurchase.vue';
import EditPurchase from './purchases/EditPurchase.vue';
import ExporterEdit from './purchases/ExporterEdit.vue';
import ViewSale from './sales/ViewSale.vue';
import EditSale from './sales/EditSale.vue';
import CustomerEdit from './sales/CustomerEdit.vue';
import ViewCreditnote from './creditnote/ViewCreditnote.vue';
import EditCreditnote from './creditnote/EditCreditnote.vue';
import ViewDebitnote from './debitnote/ViewDebitnote.vue';
import EditDebitnote from './debitnote/EditDebitnote.vue';
import ViewForm44 from './form44/ViewForm44.vue';
import EditForm44 from './form44/EditForm44.vue';
import ViewForm45 from './form45/ViewForm45.vue';
import EditForm45 from './form45/EditForm45.vue';

import Alert from '../../helpers/customAlert';
import Confirm from '../../helpers/customConfirm';

const notify = inject('showToast'); // taost

const field = reactive({ item: '' });
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

// --- Purchase functionality ---
// purchase modal pop
const modalviewpurchase = ref();
const modaleditpurchase = ref();
const modalexporteredit = ref();
// purchase ref
const singlePurchase = ref();
const purchaseRef = ref(null);
const exporterRef = ref(null);
// purchase modal close
const closeViewPurchase = () => modalviewpurchase.value.close();
const closeEditPurchase = () => modaleditpurchase.value.close();
const closeExporterEdit = () => modalexporteredit.value.close();

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
// delete purchase from form621
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
// --- end purchase functionality ---

// --- Sale functionality ---
// sale modal pop
const modalviewsale = ref();
const modaleditsale = ref();
const modalcustomeredit = ref();
// sale view edit
const singleSale = ref();
const saleRef = ref(null);
const customerRef = ref(null);
// sale modal close
const closeViewSale = () => modalviewsale.value.close();
const closeEditSale = () => modaleditsale.value.close();
const closeCustomerEdit = () => modalcustomeredit.value.close();

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
// delete sale from form621
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
// --- end sale functionality ---

// --- Credit note functionality ---
// creditnote modal popup
const modalviewcreditnote = ref();
const modaleditcreditnote = ref();
// creditnote ref
const singleCreditnote = ref();
const creditnoteRef = ref(null);
// creditnote modal pop close
const closeViewCreditnote = () => modalviewcreditnote.value.close();
const closeEditCreditnote = () => modaleditcreditnote.value.close();

const viewCreditnote = async (id) => await axios.get(route('creditnote-single', { id: id })).then((res) => {
  if (res.data?.error) {
    modalviewcreditnote.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalviewcreditnote.value.open();
    singleCreditnote.value = res.data.creditnote;
  }
});
const editCreditnote = async (id) => await axios.get(route('creditnote-single', { id })).then((res) => {
  if (res.data?.error) {
    modaleditcreditnote.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modaleditcreditnote.value.open();
    creditnoteRef.value?.setForm(res.data);
  }
});
// delete mushak67 from form621
const delCreditnote = async (id) => {
  Confirm('Do you want to delete ?', async (res) => {
    if (res) {
      router.delete(route('creditnote-delete', { id: id }), {
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
// --- end creditnote functionality ---

// --- debit note functionality ---
// debitnote modal popup
const modalviewdebitnote = ref();
const modaleditdebitnote = ref();
// debitnote ref
const singleDebitnote = ref();
const debitnoteRef = ref(null);
// debitnote modal pop close
const closeViewDebitnote = () => modalviewdebitnote.value.close();
const closeEditDebitnote = () => modaleditdebitnote.value.close();

const viewDebitnote = async (id) => await axios.get(route('debitnote-single', { id: id })).then((res) => {
  if (res.data?.error) {
    modalviewdebitnote.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalviewdebitnote.value.open();
    singleDebitnote.value = res.data.debitnote;
  }
});
const editDebitnote = async (id) => await axios.get(route('debitnote-single', { id })).then((res) => {
  if (res.data?.error) {
    modaleditdebitnote.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modaleditdebitnote.value.open();
    debitnoteRef.value?.setForm(res.data);
  }
});
// delete mushak67 from form621
const delDebitnote = async (id) => {
  Confirm('Do you want to delete ?', async (res) => {
    if (res) {
      router.delete(route('debitnote-delete', { id: id }), {
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
// --- end debitnote functionality ---

// --- form 4.4 functionality ---
// form 4.4 modal popup
const modalviewform44 = ref();
const modaleditform44 = ref();
// form 4.4 ref
const singleForm44 = ref();
const form44Ref = ref(null);
// form 4.4 modal pop close
const closeViewForm44 = () => modalviewform44.value.close();
const closeEditForm44 = () => modaleditform44.value.close();

const viewForm44 = async (id) => await axios.get(route('form44-single', { id: id })).then((res) => {
  if (res.data?.error) {
    modalviewform44.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalviewform44.value.open();
    singleForm44.value = res.data.form44;
  }
});
const editForm44 = async (id) => await axios.get(route('form44-single', { id })).then((res) => {
  if (res.data?.error) {
    modaleditform44.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modaleditform44.value.open();
    form44Ref.value?.setForm(res.data);
  }
});
// delete mushak44 from form621
const delForm44 = async (id) => {
  Confirm('Do you want to delete ?', async (res) => {
    if (res) {
      router.delete(route('form44-delete', { id: id }), {
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
// --- end form44 functionality ---

// --- form 4.5 functionality ---
// form 4.5 modal popup
const modalviewform45 = ref();
const modaleditform45 = ref();
// form 4.5 ref
const singleForm45 = ref();
const form45Ref = ref(null);
// form 4.5 modal pop close
const closeViewForm45 = () => modalviewform45.value.close();
const closeEditForm45 = () => modaleditform45.value.close();

const viewForm45 = async (id) => await axios.get(route('form45-single', { id: id })).then((res) => {
  if (res.data?.error) {
    modalviewform45.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modalviewform45.value.open();
    singleForm45.value = res.data.form45;
  }
});
const editForm45 = async (id) => await axios.get(route('form45-single', { id })).then((res) => {
  if (res.data?.error) {
    modaleditform45.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modaleditform45.value.open();
    form45Ref.value?.setForm(res.data);
  }
});
// delete mushak45 from form621
const delForm45 = async (id) => {
  Confirm('Do you want to delete ?', async (res) => {
    if (res) {
      router.delete(route('form45-delete', { id: id }), {
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
// --- end form45 functionality ---

// refresh load
const refresh = (id) => router.get(route('form621-client-single-index', { client_id: id }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['Form621ClientTable'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style lang="scss" scoped></style>