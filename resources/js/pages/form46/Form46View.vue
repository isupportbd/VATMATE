<template>
  <Spinner v-if="empty(props.view)" />
  <div class="row g-2" v-else>
    <!-- {{ props.view }} -->
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-body h-100 border">
        <p class="fw-semibold m-0">
          Manager ({{ props.view?.client?.user?.status == "1" ? 'Active' : 'Inactive' }})
        </p>
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
  </div>
</template>

<script setup>
import { empty } from '../../helpers/utils';
import Spinner from '../../components/Spinner.vue';

const props = defineProps({ view: { type: Object } });
</script>