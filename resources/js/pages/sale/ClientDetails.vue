<template>
  <div class="card border h-100" :class="{ 'd-none': empty(props.view) }">
    <div class="card-body">
      <div class="row">
        <div class="col-7">
          <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">Company Details</div>
          <p class="mb-1">Name: {{ props.view?.company_name }}</p>
          <p class="mb-1">Email: {{ props.view?.email }}</p>
          <p class="mb-1">Mobile: {{ props.view?.mobile }}</p>
          <p class="mb-1">Address: {{ props.view?.address }}</p>
          <p class="mb-1">Reference: {{ props.view?.reference }}</p>
        </div>
        <div class="col-5" :class="props.view?.user ? 'd-block' : 'd-none'">
          <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">User Details</div>
          <p class="mb-1">Email: {{ props.view?.user?.email }}</p>
          <p class="mb-1">Mobile: {{ props.view?.user?.mobile }}</p>
          <p class="mb-1">Status: {{ props.view?.user?.status == 1 ? "Active" : "Inactive" }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { empty } from '../../helpers/utils';

const props = defineProps({ view: { type: Object } });
</script>