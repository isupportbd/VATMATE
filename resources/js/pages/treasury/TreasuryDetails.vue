<template>
  <div class="card card-body border h-100">
    <div class="row g-2" :class="{ 'd-none': !props.view }">
      <div class="col-12 col-lg-4">
        <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">Treasury Details</div>
        <p>Challan: {{ props.view?.challan_no }}</p>
        <p>Amount: {{ props.view?.amount }}</p>
        <p>Date: {{ props.view?.tax_period ? moment(props.view?.tax_period).format('MMM YYYY') : '' }}</p>
        <p>Bank: {{ props.view?.branch?.bank?.name }}, {{ props.view?.branch?.name }}, {{ props.view?.branch?.division
          ?.name }}
        </p>
        <p>Remarks: {{ props.view?.remarks }}</p>
      </div>
      <div class="col-12 col-lg-4">
        <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">Company Details</div>
        <p class="text-uppercase">Name: {{ props.view?.client?.company_name }}</p>
        <p>Email: {{ props.view?.client?.email }}</p>
        <p>Mobile: {{ props.view?.client?.mobile }}</p>
        <p>Address: {{ props.view?.client?.address }}</p>
        <p>Reference: {{ props.view?.client?.reference }}</p>
      </div>
      <div :class="props.view?.client?.user ? 'col-12 col-lg-4' : 'd-none'">
        <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">User Details</div>
        <p>Email: {{ props.view?.client?.user?.email }}</p>
        <p>Mobile: {{ props.view?.client?.user?.mobile }}</p>
        <p>Status: {{ props.view?.client?.user?.status == 1 ? "Active" : "Inactive" }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import moment from 'moment-timezone';
import { empty } from '../../helpers/utils';

const props = defineProps({ view: { type: Object } });
</script>