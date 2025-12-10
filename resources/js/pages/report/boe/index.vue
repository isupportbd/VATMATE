<template>

  <Head title="BoE" />
  <PageBar>
    <p class="m-0 fs-4">BoE</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" icon="bi bi-file-earmark-pdf-fill" @click="openPdf" />
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.client_id, route().params.monthYear)" />
    <Link title="back" :href="route('report-client-data-list', { client_id: route().params.client_id })"
      class="nav-btn">
      <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <div class="card card-body border-custom">
    <div class="row mb-2">
      <div class="col-12 text-center fw-semibold">
        <h3>Bill of Entry</h3>
        <p class="m-0">{{ props.boe?.client?.company_name }}</p>
        <p class="mb-2">{{ props.boe?.client?.bin }}</p>
        <p class="m-0 fs-5">{{ moment(route().params.monthYear).format('MMMM YYYY') }}</p>
      </div>
    </div>
    <div class="card card-body border">
      <div :class="props.boe?.purchase.length > 0 ? 'row' : 'd-none'">
        <div class="col-12">
          <p class="m-0 fw-bold bg-info bg-opacity-25 px-1">
            Purchage Details <span class="text-danger">(Total BoE: {{ props?.boe?.totalBoe }})</span>
          </p>
        </div>
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-sm table-borderless text-center align-middle">
              <thead class="border-0 border-bottom border-dark">
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
                <tr v-for="dt in props?.boe?.purchase" :key="dt.id">
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
  boe: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

const openPdf = () => {
  if (typeof window !== 'undefined') {
    const id = route().params.id;
    window.open(route('boe-pdf', { client_id: route().params.client_id, monthYear: route().params.monthYear }), '_blank');
  }
};

// reload summary 43
const refresh = (client_id, monthYear) => router.get(route('report-boe', { client_id, monthYear }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['boe'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>