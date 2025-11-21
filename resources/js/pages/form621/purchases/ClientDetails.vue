<template>
  <div class="card card-body border h-100" :class="props.view ? 'd-block' : 'd-none'">
    <div class="mb-3">
      <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">Company Details</div>
      <p class="text-uppercase">Name: {{ props.view?.company_name }}</p>
      <p>Email: {{ props.view?.email }}</p>
      <p>Mobile: {{ props.view?.mobile }}</p>
      <p>Address: {{ props.view?.address }}</p>
      <p>Reference: {{ props.view?.reference }}</p>
    </div>
    <div :class="props.view?.user ? 'd-block' : 'd-none'">
      <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">User Details</div>
      <p>Email: {{ props.view?.user?.email }}</p>
      <p>Mobile: {{ props.view?.user?.mobile }}</p>
      <p>Status: {{ props.view?.user?.status == 1 ? "Active" : "Inactive" }}</p>
    </div>
  </div>
</template>

<script setup>
import { empty } from '../../../helpers/utils';

const props = defineProps({ view: { type: Object } });
</script>