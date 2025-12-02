<template>

  <Head title="Summary" />
  <PageBar>
    <p class="m-0 fs-4">Summary</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.client_id, route().params.monthYear)" />
    <Link title="back" :href="route('report-client-data-list', { client_id: route().params.client_id })"
      class="nav-btn">
    <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <div class="card card-body border mb-2">
    <div class="row">
      <div class="col-12">
        <h3 class="text-center">SUMMARY BY BIN</h3>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-lg-9 col-xl-10">
        <p class="m-0"><strong>Tax Period : </strong> {{ moment(route().params.monthYear).format('MMMM YYYY') }}</p>
        <p class="m-0"><strong>BIN : </strong> {{ props.summary?.client?.bin }}</p>
        <p class="m-0" :class="{ 'd-none': !props.summary?.client?.tin }">
          <strong>TIN : </strong> {{ props.summary?.client?.tin }}
        </p>
        <p class="m-0"><strong>Name : </strong> {{ props.summary?.client?.company_name }}</p>
        <p class="m-0"><strong>User : </strong> {{ props.summary?.client?.vat_username }}</p>
        <p class="m-0"><strong>Pass : </strong> {{ props.summary?.client?.vat_password }}</p>
        <p class="m-0" :class="{ 'd-none': !props.summary?.client?.tin }">
          <strong>Circle : </strong>
          {{ props.summary?.client?.circle }}
        </p>
      </div>
    </div>
    <div :class="props.summary.sale.length > 0 ? 'row mt-2' : 'd-none'">
      <div class="col-12">
        <p class="fw-bold mb-1 bg-info bg-opacity-25">Sale Summary</p>
        <div class="table-responsive">
          <table class="table table-sm table-borderless text-center align-middle">
            <thead class="border-0 border-bottom border-secondary">
              <tr>
                <th>#</th>
                <th>VAT Rate</th>
                <th>HS Code</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Value</th>
                <th>SD</th>
                <th>VAT</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(dt, i) in props.summary?.sale" :key="i">
                <td>{{ i + 1 }}</td>
                <td>{{ dt?.vat_rate }}%</td>
                <td>{{ dt?.item?.hs_code }}</td>
                <td>{{ dt?.item?.name }}</td>
                <td>{{ dt?.quantity }} {{ dt?.unit?.name }}</td>
                <td>{{ dt?.item_price }}</td>
                <td>{{ dt?.sd_value }}</td>
                <td>{{ dt?.vat_value }}</td>
              </tr>
              <tr class="border-0 border-top border-secondary">
                <td colspan="5" class="text-end">Total = </td>
                <td>{{ props.summary?.saleSum?.item_price_sum }}</td>
                <td>{{ props.summary?.saleSum?.sd_value_sum }}</td>
                <td>{{ props.summary?.saleSum?.vat_value_sum }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div :class="props.summary.purchase.length > 0 ? 'row mt-2' : 'd-none'">
      <div class="col-12">
        <p class="fw-bold mb-1 bg-info bg-opacity-25">Purchase Summary</p>
        <div class="table-responsive">
          <table class="table table-sm table-borderless text-center align-middle">
            <thead class="border-0 border-bottom border-secondary">
              <tr>
                <th>#</th>
                <th>Note</th>
                <th>HS Code</th>
                <th>Item</th>
                <th>Net Quantity</th>
                <th>AV</th>
                <th>Base value of VAT</th>
                <th>SD</th>
                <th>VAT</th>
                <th>AT</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(dt, i) in props.summary?.purchase" :key="i">
                <td>{{ i + 1 }}</td>
                <td>{{ dt?.mushaknote?.name_note_no }}</td>
                <td>{{ dt?.item?.hs_code }}</td>
                <td>{{ dt?.item?.name }}</td>
                <td>{{ dt?.total_quantity }} {{ dt?.unit?.name }}</td>
                <td>{{ dt?.assessable_value }}</td>
                <td>{{ dt?.base_value }}</td>
                <td>{{ dt?.sd_value }}</td>
                <td>{{ dt?.vat_value }}</td>
                <td>{{ dt?.at_value }}</td>
              </tr>
              <tr class="border-0 border-top border-secondary">
                <td colspan="5" class="text-end">Total = </td>
                <td>{{ props.summary?.purchaseSum?.assessable_value_sum }}</td>
                <td>{{ props.summary?.purchaseSum?.base_value_sum }}</td>
                <td>{{ props.summary?.purchaseSum?.sd_value_sum }}</td>
                <td>{{ props.summary?.purchaseSum?.vat_value_sum }}</td>
                <td>{{ props.summary?.purchaseSum?.at_value_sum }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mt-2">
      <p class="fw-bold mb-1 bg-info bg-opacity-25">Transaction Summary</p>
      <div class="col-12 col-xl-12 mb-3 mb-xl-0">
        <div class="row gy-3">
          <div class="col-12 col-sm-6 col-md-4 col-lg-3"
            v-for="(dt, i) in ['Increase', 'Decrease', 'Penalty'].includes(props.summary?.adjustment)" :key="i">
            <span class="fw-bold">{{ dt?.expense?.name }} : </span> {{ dt?.amount }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Treasury : </span>
            <span>{{ props.summary?.treasury?.amount }}</span>
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 52 : </span>
            {{ props.summary?.prevNetPayable }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Net Payable VAT :</span>
            {{ props.summary?.netPayableRegular }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 65 :</span>
            {{ props.summary?.closignBalanceCurrentRegular ? props.summary?.closignBalanceCurrentRegular : 0 }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">MCU :</span> {{ props.summary?.totalMcu }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Debit Note : </span>
            <span>{{ props.summary?.totalDebitnote?.increase_vat }}</span>
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Credit Note : </span>
            {{ props.summary?.totalCreditnote?.decrease_vat }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Mushak 4.4 :</span>
            {{ props.summary?.totalForm44?.increase_vat }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Mushak 4.5 :</span>
            {{ props.summary?.totalForm45?.increase_vat }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 28 : </span>
            <span>{{ props.summary?.totalIncAdj }}</span>
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 33 : </span>
            {{ props.summary?.totalDecAdj }}
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-3">
      <p class="fw-bold mb-1 bg-info bg-opacity-25">Transaction Summary (Final Settlement)</p>
      <div class="col-12 col-xl-12 mb-3 mb-xl-0">
        <div class="row gy-3">
          <div class="col-12 col-sm-6 col-md-4 col-lg-3"
            v-for="(dt, i) in ['Increase', 'Decrease', 'Penalty'].includes(props.summary?.adjustment)" :key="i">
            <span class="fw-bold">{{ dt?.expense?.name }} : </span> {{ dt?.amount }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Treasury : </span>
            <span>{{ props.summary?.treasury?.amount }}</span>
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 52 : </span>
            {{ props.summary?.prevNetPayable }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Net Payable VAT :</span>
            {{ props.summary?.netPayableSettlement }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 65 :</span>
            {{ props.summary?.closignBalanceCurrentSettlement ? props.summary?.closignBalanceCurrentSettlement : 0 }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">MCU :</span> {{ props.summary?.totalMcu }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Debit Note : </span>
            <span>{{ props.summary?.totalDebitnote?.increase_vat }}</span>
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Credit Note : </span>
            {{ props.summary?.totalCreditnote?.decrease_vat }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Mushak 4.4 :</span>
            {{ props.summary?.totalForm44?.increase_vat }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Mushak 4.5 :</span>
            {{ props.summary?.totalForm45?.increase_vat }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-danger">
            <span class="fw-bold">Note 22 AT :</span>
            {{ props.summary?.note22Purchase }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-danger">
            <span class="fw-bold">Note 27 VAT :</span>
            {{ props.summary?.note22Sale }}
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 28 : </span>
            <span>{{ props.summary?.totalIncAdj }}</span>
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <span class="fw-bold">Note 33 : </span>
            {{ props.summary?.totalDecAdj }}
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card card-body border">
    <div :class="props.summary.purchaseDetails.length > 0 ? 'row' : 'd-none'">
      <div class="col-12">
        <p class="m-0 fw-bold bg-info bg-opacity-25 px-1">
          Purchage Details <span class="text-danger">(Total BoE: {{ props.summary?.totalBoe }})</span>
        </p>
      </div>
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-sm table-borderless text-center align-middle">
            <thead class="border-0 border-bottom border-secondary">
              <tr>
                <th>Note</th>
                <th>Station</th>
                <th>BoE No</th>
                <th>Date</th>
                <th>HS Code</th>
                <th>Item</th>
                <th>Net Quantity</th>
                <th>AV</th>
                <th>Value</th>
                <th>SD</th>
                <th>VAT</th>
                <th>AT</th>
                <th>LC No</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="dt in props.summary?.purchaseDetails" :key="dt.id">
                <td>{{ dt?.mushaknote?.name_note_no }}</td>
                <td>{{ dt?.stationcode?.code }}</td>
                <td>{{ dt?.boe }}</td>
                <td>{{ moment(dt?.boe_date).format('DD/MM/YYYY') }}</td>
                <td>{{ dt?.item?.hs_code }}</td>
                <td>{{ dt?.item?.name }}</td>
                <td>{{ dt?.total_quantity }} {{ dt?.unit?.name }}</td>
                <td>{{ Number(dt?.assessable_value).toFixed(2) }}</td>
                <td>{{ Number(dt?.base_value).toFixed(2) }}</td>
                <td>{{ Number(dt?.sd_value).toFixed(2) }}</td>
                <td>{{ Number(dt?.vat_value).toFixed(2) }}</td>
                <td>{{ Number(dt?.at_value).toFixed(2) }}</td>
                <td>{{ dt?.lc_no }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, onMounted, onBeforeUnmount } from 'vue';
import { empty } from '../../../helpers/utils';
import { user, role } from '../../../composables/user';
import { Link, router } from '@inertiajs/vue3';

import Button from "../../../components/Button.vue";
import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import moment from 'moment-timezone';

const props = defineProps({
  summary: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// reload summary 43
const refresh = (id, monthYear) => router.get(route('report-summary', { client_id: id, monthYear: monthYear }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['summary'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>