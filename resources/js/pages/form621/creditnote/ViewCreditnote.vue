<template>
  <Spinner v-if="empty(props.view)" />
  <div class="row gx-2" v-else>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">Item Details</div>
        </div>
        <div class="card-body p-2">
          <p class="m-0">Item: {{ props.view?.item?.name }} ({{ props?.view?.item?.hs_code }})</p>
          <p class="m-0">Unit: {{ props.view?.unit?.name }}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-8 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">Credit Note (6.7) Details</div>
        </div>
        <div class="card-body p-2">
          <div class="row gx-1">
            <div class="col-6">
              <p class="m-0">Invoice No: {{ props.view?.creditnote_invoice }}</p>
              <p class="m-0">Challan Invoice: {{ props.view?.lc_or_invoice_no }}</p>
              <p class="m-0">Date: {{ moment(props.view?.issued_date).format('DD/MM/YYYY') }}</p>
              <p class="m-0">Opening Stock: {{ props.view?.opening_stock }} {{ props.view?.unit?.name }}</p>
              <p class="m-0">
                Quantity: {{ props.view?.quantity_of_decreasing_adjustment }} {{ props.view?.unit?.name }}
              </p>
              <p class="m-0">Stock: {{ props.view?.stock }} {{ props?.view?.unit?.name }}</p>
              <p class="m-0">Challan Date: {{ moment(props.view?.vat_challan_date).format('DD/MM/YYYY') }}</p>
            </div>
            <div class="col-6">
              <p class="m-0">Challan Value: {{ props.view?.value_in_challan }}</p>
              <p class="m-0">Challan Quantity: {{ props.view?.quantity_in_challan }}</p>
              <p class="m-0">Challan Vat: {{ props.view?.vat_in_challan }}</p>
              <p class="m-0">Challan SD: {{ props.view?.sd_in_challan }}</p>
              <p class="m-0">Decrease Value: {{ props.view?.value_of_decreasing_adjustment }} </p>
              <p class="m-0">Decrease SD: {{ props.view?.sd_of_decreasing_adjustment }} </p>
              <p class="m-0">Decrease VAT: {{ props.view?.vat_of_decreasing_adjustment }} </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">Details</div>
        </div>
        <div class="card-body p-2">
          <p class="m-0 text-uppercase">Name: {{ props.view?.client?.company_name }}</p>
          <p class="m-0 text-capitalize">Name: {{ props.view?.client?.name }}</p>
          <p class="m-0">Email: {{ props.view?.client?.email }}</p>
          <p class="m-0">Mobile: {{ props.view?.client?.mobile }}</p>
          <p class="m-0">Address: {{ props.view?.client?.address }}</p>
          <p class="m-0">Reference: {{ props.view?.client?.reference }}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">BIN Details</div>
        </div>
        <div class="card-body p-2">
          <p class="m-0">BIN: {{ props.view?.client?.bin }}</p>
          <p class="m-0">VAT Username: {{ props.view?.client?.vat_username }}</p>
          <p class="m-0">VAT Password: {{ props.view?.client?.vat_password }}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">User Details</div>
        </div>
        <div :class="`card-body p-2 ${props.view?.client?.user ? 'd-block' : 'd-none'}`">
          <p class="m-0">Email: {{ props.view?.client?.user?.email }}</p>
          <p class="m-0">Mobile: {{ props.view?.client?.user?.mobile }}</p>
          <p class="m-0">Division: {{ props.view?.client?.division?.name }}</p>
          <p class="m-0">Status: {{ props.view?.client?.user?.status == 1 ? "Active" : "Inactive" }}</p>
        </div>
      </div>
    </div>
  </div>
  <!-- {{ props.view }} -->
</template>

<script setup>
import moment from 'moment';
import { empty } from '../../../helpers/utils';
import Spinner from '../../../components/Spinner.vue';
const props = defineProps({ view: { type: Object } });

// onMounted(async () => await props.fetched());
</script>

<style lang="scss" scoped>
.border {
  border-color: #CCCCCC !important;
}
</style>