<template>
  <Spinner v-if="empty(props.view)" />
  <div class="row g-2" v-else>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-body h-100 border">
        <p class="fw-bold m-0">Company</p>
        <p>{{ props.view?.company_name }}</p>
        <p class="fw-bold m-0">Name</p>
        <p>{{ props.view?.name }}</p>
        <p class="fw-bold m-0">Address</p>
        <p>{{ props.view?.address }}</p>
        <p class="fw-bold m-0">Division</p>
        <p>{{ props.view?.division?.name }}</p>
        <p class="fw-bold m-0">TIN</p>
        <p class="m-0">{{ props.view?.tin }}</p>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-body h-100 border">
        <p class="fw-bold m-0">Email</p>
        <p>{{ props.view?.email }}</p>
        <p class="fw-bold m-0">Mobile</p>
        <p>{{ props.view?.mobile }}</p>
        <p class="fw-bold m-0">Reference</p>
        <p>{{ props.view?.reference }}</p>
        <p class="fw-bold m-0">Circle</p>
        <p class="m-0">{{ props.view?.circle }}</p>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-body h-100 border">
        <p class="fw-bold m-0">Manager</p>
        <p>{{ props.view?.user?.email }}</p>
        <p class="fw-bold m-0">Mobile</p>
        <p>{{ props.view?.user?.mobile }}</p>
        <p class="fw-bold m-0">Status</p>
        <p class="m-0">{{ props.view?.user?.status == "1" ? "Active" : "Inactive" }}</p>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-body h-100 border">
        <p class="fw-bold m-0">BIN</p>
        <p>{{ props.view?.bin }}</p>
        <p class="fw-bold m-0">Vat Username</p>
        <p>{{ props.view?.vat_username }}</p>
        <p class="fw-bold m-0">Vat Password</p>
        <p class="m-0">{{ props.view?.vat_password }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { empty } from '../../helpers/utils';
import Spinner from '../../components/Spinner.vue';

const props = defineProps({ view: { type: Object } });
</script>