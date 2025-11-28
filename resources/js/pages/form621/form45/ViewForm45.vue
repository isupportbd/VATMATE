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
          <div class="fw-bold text-uppercase">Form 4.5 Details</div>
        </div>
        <div class="card-body p-2">
          <div class="row gx-1">
            <div class="col-6">
              <p class="m-0">Invoice No: {{ props.view?.form44_invoice }}</p>
              <p class="m-0">LC: {{ props.view?.lc_or_invoice_no }}</p>
              <p class="m-0">Date: {{ moment(props.view?.date).format('DD/MM/YYYY') }}</p>
              <p class="m-0">Opening Stock: {{ props.view?.opening_stock }} {{ props.view?.unit?.name }}</p>
              <p class="m-0">
                Quantity: {{ props.view?.quantity }} {{ props.view?.unit?.name }}
              </p>
              <p class="m-0">Stock: {{ props.view?.stock }} {{ props?.view?.unit?.name }}</p>
            </div>
            <div class="col-6">
              <p class="m-0">Value: {{ props.view?.value }}</p>
              <p class="m-0">Vat: {{ props.view?.vat }}</p>
              <p class="m-0">Proposed Value: {{ props.view?.proposed_value }}</p>
              <p class="m-0">Inc. Adj. VAT: {{ props.view?.increasing_adjustment_vat }}</p>
              <p class="m-0">Net Increase: {{ props.view?.net_increase }}</p>
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