<template>
  <Spinner v-if="empty(props.view)" />
  <div class="row g-2" v-else>
    <div class="col-12 col-lg-4">
      <div class="card card-body h-100">
        <h6 class="fw-bold m-0">
          Manager ({{ props.view?.client?.user?.status == "1" ? 'Active' : 'Inactive' }})
        </h6>
        <p>{{ props.view?.client?.user?.email }}</p>
        <h6 class="fw-bold m-0">Mobile</h6>
        <p>{{ props.view?.client?.user?.mobile }}</p>
        <h6 class="fw-bold m-0">Reference</h6>
        <p class="m-0">{{ props.view?.client?.reference }}</p>
      </div>
    </div>
    <div class="col-12 col-lg-8">
      <div class="card card-body h-100">
        <div class="row">
          <div class="col-12 col-sm-6 mb-2 mb-sm-0">
            <h6 class="fw-bold m-0">Company</h6>
            <p class="text-uppercase">{{ props.view?.client?.company_name }}</p>
            <h6 class="fw-bold m-0">Name</h6>
            <p>{{ props.view?.client?.name }}</p>
            <h6 class="fw-bold m-0">Address</h6>
            <p class="m-0">{{ props.view?.client?.address }}</p>
          </div>
          <div class="col-12 col-sm-6">
            <h6 class="fw-bold m-0">BIN</h6>
            <p>{{ props.view?.client?.bin }}</p>
            <h6 class="fw-bold m-0">VAT User</h6>
            <p>{{ props.view?.client?.vat_username }}</p>
            <h6 class="fw-bold m-0">VAT Pass</h6>
            <p class="m-0">{{ props.view?.client?.vat_password }}</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12">
      <div class="card card-body h-100">
        <div class="row">
          <div class="col-12 col-sm-6 col-lg-3 mb-2 m-lg-0">
            <h6 class="fw-bold m-0">Submission Date</h6>
            <p>{{ moment(props.view?.submission_date).format('DD.MM.YYYY') }}</p>
            <h6 class="fw-bold m-0">First Delivery Date</h6>
            <p>{{ moment(props.view?.first_delivery_date).format('DD.MM.YYYY') }}</p>
            <h6 class="fw-bold m-0">BoE Date</h6>
            <p class="m-0 text-capitalize">{{ moment(props.view?.boe_date).format('DD.MM.YYYY') }}</p>
          </div>
          <div class="col-12 col-sm-6 col-lg-3 mb-2 m-lg-0">
            <h6 class="fw-bold m-0">BoE Number</h6>
            <p>{{ props.view?.boe_no }}</p>
            <h6 class="fw-bold m-0">Purchase Unit Quantity ({{ props.view?.purchaseunit?.name }})</h6>
            <p>{{ props.view?.purchase_unit_total }}</p>
            <h6 class="fw-bold m-0">Wastage(%)</h6>
            <p class="m-0 text-capitalize">{{ props.view?.wastage }}</p>
          </div>
          <div class="col-12 col-sm-6 col-lg-3 mb-2 m-lg-0">
            <h6 class="fw-bold m-0">Item</h6>
            <p>{{ props.view?.item?.name }}</p>
            <h6 class="fw-bold m-0">HS Code</h6>
            <p>{{ props.view?.item?.hs_code }}</p>
          </div>
          <div class="col-12 col-sm-6 col-lg-3 mb-2 m-lg-0">
            <h6 class="fw-bold m-0">Sale Unit Quantity ({{ props.view?.saleunit?.name }})</h6>
            <p>{{ props.view?.sale_unit_total }}</p>
            <h6 class="fw-bold m-0">Base Value</h6>
            <p>{{ props.view?.base_value }}</p>
            <h6 class="fw-bold m-0">Unit Price</h6>
            <p class="m-0 text-capitalize">{{ props.view?.unit_price }}</p>
          </div>
          <div class="col-12 col-sm-6 col-lg-3 mb-2 m-lg-0">
            <h6 class="fw-bold m-0">LC No</h6>
            <p class="m-0 text-capitalize">{{ props.view?.lc_no }}</p>
          </div>
        </div>
      </div>
    </div>
    <div :class="props.view?.addition.length > 0 ? 'col-12' : 'd-none'">
      <div class="card border m-0">
        <div class="card-header text-uppercase fw-bold">
          <span>{{ props.view?.item?.name }}</span><span class="px-1">|</span>
          <span>4.3 unit price: {{ props.view?.unit_price }}</span><span class="px-1">|</span>
          <span>rate : {{ (props.view?.additionsum[0]?.ratesum)?.toFixed(2) }}%</span>
          <span class="px-1">|</span> <span>value : {{ (props.view?.additionsum[0]?.valuesum)?.toFixed(2) }}</span>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered border mb-0">
              <thead>
                <tr>
                  <th class="col-6">Costsheet</th>
                  <th class="col-6 text-center">Rate | Value</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="addition in props.view?.addition" :key="addition.id">
                  <td>{{ addition?.costsheet?.name }}</td>
                  <td class="text-center">{{ addition?.rate }}% <span class="px-1">|</span> {{ addition?.value }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import moment from 'moment';
import Spinner from '../../components/Spinner.vue';
import { role } from '../../composables/user';
import { emptyObj, empty } from '../../helpers/utils';

const props = defineProps({ view: { type: Object } });
</script>

<style lang="scss" scoped>
.border {
  border-color: #CCCCCC !important;
}
</style>