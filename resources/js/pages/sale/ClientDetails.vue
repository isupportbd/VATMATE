<template>
  <div class="card mb-2 border" :class="{ 'd-none': empty(props.view) }" style="min-height: calc(100% - .5rem);">
    <div class="card-body">
      <div class="row">
        <div class="col-6">
          <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">Company Details</div>
          <p class="text-uppercase">Name: {{ props.view?.company_name }}</p>
          <p>Email: {{ props.view?.email }}</p>
          <p>Mobile: {{ props.view?.mobile }}</p>
          <p>Address: {{ props.view?.address }}</p>
          <p class="m-0">Reference: {{ props.view?.reference }}</p>
        </div>
        <div class="col-6" :class="props.view?.user ? 'd-block' : 'd-none'">
          <div class="fw-bold text-uppercase border-bottom d-inline-block mb-2">User Details</div>
          <p>Email: {{ props.view?.user?.email }}</p>
          <p>Mobile: {{ props.view?.user?.mobile }}</p>
          <p>Status: {{ props.view?.user?.status == 1 ? "Active" : "Inactive" }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { empty } from '../../helpers/utils';

const props = defineProps({ view: { type: Object } });
</script>