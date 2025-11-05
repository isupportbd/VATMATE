<template>
  <div class="card card-body border h-100" :class="props.view ? 'd-block' : 'd-none'">
    <div class="mb-3">
      <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">Company Details</div>
      <p class="text-uppercase">Name: {{ props.view?.client?.company_name }}</p>
      <p>Email: {{ props.view?.client?.email }}</p>
      <p>Mobile: {{ props.view?.client?.mobile }}</p>
      <p>Address: {{ props.view?.client?.address }}</p>
      <p>Reference: {{ props.view?.client?.reference }}</p>
    </div>
    <div :class="props.view?.client?.user ? 'd-block' : 'd-none'">
      <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">User Details</div>
      <p>Email: {{ props.view?.client?.user?.email }}</p>
      <p>Mobile: {{ props.view?.client?.user?.mobile }}</p>
      <p>Status: {{ props.view?.client?.user?.status == 1 ? "Active" : "Inactive" }}</p>
    </div>
  </div>
  <!-- {{ props.view }} -->
</template>

<script setup>
import { empty } from '../../helpers/utils';

const props = defineProps({ view: { type: Object } });
</script>