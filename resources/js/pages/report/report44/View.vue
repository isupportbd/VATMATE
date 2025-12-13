<template>

  <Head title="Report 4.4" />
  <PageBar>
    <p class="m-0 fs-4">Report 4.4</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" icon="bi bi-file-earmark-pdf-fill" @click="openPdf" />
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.id, route().params.client_id, route().params.monthYear)" />
    <Link title="back"
      :href="route('report-44', { client_id: route().params.client_id, monthYear: route().params.monthYear })"
      class="nav-btn">
      <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <div class="row printarea">
    <div class="col-12">
      <div class="card card-body border p-lg-5">
        <div class="row">
          <div class="col-12">
            <div class="fw-bold text-center position-relative">
              <label class="fw-bold border border-secondary border-2 position-absolute p-2 end-0" style="top: 75%">
                Mushak-4.4
              </label>
              <p class="m-0">Government of the People's Republic of Bangladesh</p>
              <p class="m-0">National Board of Revenue</p>
            </div>
          </div>
        </div>
        <div class="row my-3">
          <div class="col-12 text-center">
            <p class="m-0 fw-bold">Application Form for Disposal of Unused or Unusable Materials</p>
            <p class="m-0">[See Sub-Rule (1) of Rule 24 (A)]</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12 col-sm-9 col-md-9">
            <p class="m-0">Name of Registered Person: {{ props.view?.client?.company_name }}</p>
            <p class="m-0">BIN of Registered Person: {{ props.view?.client?.bin }}</p>
            <p class="m-0">Challan Issuing Address: {{ props.view?.client?.address }}</p>
          </div>
          <div class="col-12 col-sm-3 col-md-3">
            <p class="m-0">Invoice No : {{ props.view?.form44_invoice }}</p>
            <p class="m-0">Date of Issue : {{ moment(props.view?.date).format('DD/MM/YYYY') }}</p>
            <p class="m-0">HS Code : {{ props.view?.item?.hs_code }}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="table-responsive">
              <table class="table table-bordered border-secondary text-center">
                <thead class="align-middle">
                  <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>BoE</th>
                    <th>Serial in Mushak 6.2.1</th>
                    <th>Quantity</th>
                    <th>Value</th>
                    <th>Paid VAT</th>
                    <th>Proposed Value</th>
                    <th>Reason of Ineligibility</th>
                  </tr>
                </thead>
                <tbody class="align-middle">
                  <tr>
                    <td>1</td>
                    <td>{{ props.view?.item?.name }} <br /> {{ props.view?.item?.hs_code }}</td>
                    <td>{{ props.view?.boe }}</td>
                    <td>{{ props.view?.form621_id }}</td>
                    <td>{{ +(props.view?.quantity)?.toFixed(2) }}</td>
                    <td>{{ +(props.view?.value)?.toFixed(2) }}</td>
                    <td>{{ +(props.view?.vat)?.toFixed(2) }}</td>
                    <td>{{ (+(props.view?.proposed_value) * +(props.view?.quantity)).toFixed(2) }}</td>
                    <td>{{ props.view?.reason_ineligibility }}</td>
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
            <p class="m-0">* I declare that all the information provided in this application form is true and correct.
            </p>
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

const props = defineProps({ view: { type: Object } });

const openPdf = () => {
  if (typeof window !== 'undefined') {
    const id = route().params.id;
    window.open(route('report-44-pdf', { id: id, bin: props.view?.client?.bin }), '_blank');
  }
};

// reload report 63
const refresh = (id, client_id, monthYear) => router.get(route('report-44-view', { id: id }), { client_id: client_id, monthYear: monthYear }, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['view'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style></style>