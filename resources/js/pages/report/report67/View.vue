<template>

  <Head title="Report 6.7" />
  <PageBar>
    <p class="m-0 fs-4">Report 6.7</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" icon="bi bi-file-earmark-pdf-fill" @click="openPdf" />
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.id, route().params.client_id, route().params.monthYear)" />
    <Link title="back"
      :href="route('report-67', { client_id: route().params.client_id, monthYear: route().params.monthYear })"
      class="nav-btn">
      <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <div class="row printarea">
    <div class="col-12">
      <div class="card card-body border p-5">
        <div class="row">
          <div class="col-12">
            <div class="fw-bold text-center position-relative">
              <label class="fw-bold border border-secondary border-2 position-absolute p-2 end-0" style="top: 75%">
                Mushak-6.7
              </label>
              <p class="m-0">Government of the People's Republic of Bangladesh</p>
              <p class="m-0">National Board of Revenue</p>
            </div>
          </div>
        </div>
        <div class="row my-3">
          <div class="col-12 text-center">
            <p class="m-0 fw-bold">Credit Note</p>
            <p class="m-0">[See Clauses (C) and (f) of Sub-Rule (1) of Rule 40]</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12 col-sm-9 col-md-9">
            <p class="m-0">Name of Registered Person : {{ props.view?.client?.company_name }}</p>
            <p class="m-0">BIN of Registered Person : {{ props.view?.client?.bin }}</p>
            <p class="m-0">Address of Registered Person: {{ props.view?.client?.address }}</p>
            <p class="m-0">Name of Receiver : {{ props.customer?.customer_name }}</p>
            <p class="m-0">BIN of Receiver : {{ props.customer?.customer_mobile }}</p>
            <p class="m-0">Address of Receiver : {{ props.customer?.customer_address }}</p>
            <p class="m-0">Vehicle Type & Number : </p>
          </div>
          <div class="col-12 col-sm-3 col-md-3">
            <p class="m-0">Credit Note : {{ props.view?.creditnote_invoice }}</p>
            <p class="m-0">Date of Issue : {{ moment(props.view?.issued_date).format('DD/MM/YYYY') }}</p>
            <p class="m-0">Time of Issue : </p>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="table-responsive">
              <table class="table table-bordered border-secondary text-center">
                <thead class="align-middle">
                  <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">Challan Number & Date</th>
                    <th rowspan="2">Reason for Issuance</th>
                    <th colspan="4">Delivery information mentioned in the invoice</th>
                    <th colspan="4">Related to decreasing adjustment</th>
                  </tr>
                  <tr>
                    <th>Value</th>
                    <th>Quantity</th>
                    <th>VAT</th>
                    <th>SD</th>
                    <th>Value</th>
                    <th>Quantity</th>
                    <th>VAT</th>
                    <th>SD</th>
                  </tr>
                </thead>
                <tbody class="align-middle">
                  <tr>
                    <td>1</td>
                    <td>
                      <p class="m-0">{{ props?.view?.lc_or_invoice_no }}</p>
                      <p class="m-0">{{ moment(props?.view?.vat_challan_date).format("DD-MM-YYYY") }} </p>
                    </td>
                    <td>({{ props?.view?.item?.name }}) <br> {{ props?.view?.reason_for_issuance }}</td>
                    <td>{{ props?.view?.value_in_challan }}</td>
                    <td>{{ props?.view?.quantity_in_challan }}</td>
                    <td>{{ props?.view?.vat_in_challan }}</td>
                    <td>{{ props?.view?.sd_in_challan }}</td>
                    <td>{{ props?.view?.value_of_decreasing_adjustment }}</td>
                    <td>{{ props?.view?.quantity_of_decreasing_adjustment }}</td>
                    <td>{{ props?.view?.vat_of_decreasing_adjustment }}</td>
                    <td>{{ props?.view?.sd_of_decreasing_adjustment }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="row my-3">
          <div class="col-12">
            <p class="m-0">Authorised Person:</p>
            <p class="m-0">Designation:</p>
            <p class="m-0">Signature:</p>
            <p class="m-0">Seal:</p>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <p>* Value except all kinds of Tax.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import moment from 'moment-timezone';
import { onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { empty } from '../../../helpers/utils';
import { user, role } from "../../../composables/user";

import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import Button from '../../../components/Button.vue';

const props = defineProps({ view: { type: Object }, customer: { type: Object } });

const openPdf = () => {
  if (typeof window !== 'undefined') {
    const id = route().params.id;
    window.open(route('report-67-pdf', { id: id, bin: props.view?.client?.bin }), '_blank');
  }
};

// reload report 63
const refresh = (id, client_id, monthYear) => router.get(route('report-67-view', { id: id }), { client_id: client_id, monthYear: monthYear }, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['view'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style></style>