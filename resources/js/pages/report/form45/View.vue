<template>

  <Head title="Report 4.5" />
  <PageBar>
    <p class="m-0 fs-4">Report 4.5</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.id, route().params.client_id, route().params.monthYear)" />
    <Link title="back"
      :href="route('form-45', { client_id: route().params.client_id, monthYear: route().params.monthYear })"
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
              <label class="fw-bold border border-secondary border-2 position-absolute p-2 end-0">
                Mushak-4.5
              </label>
              <p class="m-0">Government of the People's Republic of Bangladesh</p>
              <p class="m-0">National Board of Revenue</p>
            </div>
          </div>
        </div>
        <div class="row my-3">
          <div class="col-12 text-center">
            <p class="m-0 fw-bold">Tax Challan</p>
            <p class="m-0">[See Clauses (C) and (f) of Sub-Rule (1) of Rule 40]</p>
            <p class="m-0">Name of Registered Person: {{ props.view?.client?.company_name }}</p>
            <p class="m-0">BIN of Registered Person: {{ props.view?.client?.bin }}</p>
            <p class="m-0">Challan Issuing Address: {{ props.view?.client?.address }}</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12 col-sm-9 col-md-9">
            <p class="m-0">Name of Purchaser : {{ props.view?.customer_name }}</p>
            <p class="m-0">BIN of Purchaser : {{ props.view?.bin_or_nid }}</p>
            <p class="m-0">Destination of Supply : {{ props.view?.address }}</p>
          </div>
          <div class="col-12 col-sm-3 col-md-3">
            <p class="m-0">Invoice No : {{ props.view?.invoice_no }}</p>
            <p class="m-0">Date of Issue : {{ moment(props.view?.date).format('DD/MM/YYYY') }}</p>
            <p class="m-0">Time of Issue : </p>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="table-responsive">
              <table class="table table-bordered border-secondary text-center">
                <thead class="align-middle">
                  <tr>
                    <th>Goods/Service Description (Incases with Brand Name)</th>
                    <th>Unit of Supply</th>
                    <th>Quantity</th>
                    <th>Unit Value <sup>1</sup> (TAKA)</th>
                    <th>Total Value <sup>1</sup> (TAKA)</th>
                    <th>SD Rate (TAKA)</th>
                    <th>SD (TAKA)</th>
                    <th>VAT rate/Specific Tax</th>
                    <th>VAT/Specific Tax Amount(TAKA)</th>
                    <th>Value all kinds of Duty & Tax</th>
                  </tr>
                </thead>
                <tbody class="align-middle">
                  <tr>
                    <td>{{ props.view?.item?.name }} <br> ({{ props.view?.item?.hs_code }})</td>
                    <td>{{ props.view?.unit?.name }}</td>
                    <td>{{ +(props.view?.quantity)?.toFixed(2) }}</td>
                    <td>{{ +(props.view?.unit_price)?.toFixed(2) }}</td>
                    <td>{{ +(props.view?.item_price)?.toFixed(2) }}</td>
                    <td>{{ +(props.view?.sd)?.toFixed(2) }}%</td>
                    <td>{{ +(props.view?.sd_value)?.toFixed(2) }}</td>
                    <td>{{ +(props.view?.vat)?.toFixed(2) }}%</td>
                    <td>{{ +(props.view?.vat_value)?.toFixed(2) }}</td>
                    <td>{{ +(props.view?.total_price)?.toFixed(2) }}</td>
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
            <p class="m-0">* Applicable to the supplies made to the withholding entity only and in that case it will be
              used as combined tax invoice cum withholding certificate.</p>
            <p>* Value<sup>1</sup> except all kinds of Tax.</p>
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

// reload report 45
const refresh = (id, client_id, monthYear) => router.get(route('form-45-view', { id: id }), { client_id: client_id, monthYear: monthYear }, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['view'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style></style>