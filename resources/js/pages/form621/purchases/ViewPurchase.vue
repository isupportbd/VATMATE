<template>
  <Spinner v-if="empty(props.view)" />
  <div class="row gx-2" v-else>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">Item Details</div>
        </div>
        <div class="card-body p-2">
          <p class="m-0">Item : {{ props.view?.item?.name }} ({{ props?.view?.item?.hs_code }})</p>
          <p class="m-0">Unit : {{ props.view?.unit?.name }}</p>
          <p class="m-0">Mushak Note : {{ props.view?.mushaknote?.name_note_no }}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-8 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">Purchase Details</div>
        </div>
        <div class="card-body p-2">
          <div class="row gx-1">
            <div class="col-12 col-lg-4">
              <p class="m-0">LC No : {{ props.view?.lc_no }}</p>
              <p class="m-0">BoE No : {{ props.view?.boe }}</p>
              <p class="m-0">BoE Date : {{ moment(props.view?.boe_date).format('DD/MM/YYYY') }}</p>
              <p class="m-0">Entry Date : {{ moment(props.view?.entry_date).format('DD/MM/YYYY') }}</p>
              <p class="m-0">LC Date : {{ props.view?.lc_date ? moment(props.view?.lc_date).format('DD/MM/YYYY') : "" }}
              </p>
              <p class="m-0">Wastage : {{ Number(props.view?.wastage).toFixed(2) }}%</p>
              <p class="m-0">Unit Price : {{ Number(props.view?.unit_price).toFixed(2) }}</p>
            </div>
            <div class="col-12 col-lg-4">
              <p class="m-0">Opening Stock : {{ Number(props.view?.opening_stock).toFixed(2) }} {{
                props.view?.unit?.name }}
              </p>
              <p class="m-0">Quantity : {{ Number(props.view?.quantity).toFixed(2) }} {{ props.view?.unit?.name }}</p>
              <p class="m-0">Excess Quantity : {{ Number(props.view?.excess_quantity).toFixed(2) }} {{
                props.view?.unit?.name
              }}</p>
              <p class="m-0">
                Total Quantity :
                {{ Number(props.view?.total_quantity).toFixed(2) }} {{ props.view?.unit?.name }}
              </p>
              <p class="m-0">Assessable Value : {{ Number(props.view?.assessable_value).toFixed(2) }}</p>
              <p class="m-0">
                <span>CD : </span>
                <span>{{ Number(props.view?.cd).toFixed(2) }}%</span> |
                <span>{{ Number(props.view?.cd_value).toFixed(2) }}</span>
              </p>
              <p class="m-0">
                <span>RD : </span>
                <span>{{ Number(props.view?.rd).toFixed(2) }}%</span> |
                <span>{{ Number(props.view?.rd_value).toFixed(2) }}</span>
              </p>
            </div>
            <div class="col-12 col-lg-4">
              <p class="m-0">
                <span>SD : </span>
                <span>{{ Number(props.view?.sd).toFixed(2) }}%</span> |
                <span>{{ Number(props.view?.sd_value).toFixed(2) }}</span>
              </p>
              <p class="m-0">
                <span>VAT : </span>
                <span>{{ Number(props.view?.vat).toFixed(2) }}%</span> |
                <span>{{ Number(props.view?.vat_value).toFixed(2) }}</span>
              </p>
              <p class="m-0">
                <span>AT : </span>
                <span>{{ Number(props.view?.at).toFixed(2) }}%</span> |
                <span>{{ Number(props.view?.at_value).toFixed(2) }}</span>
              </p>
              <p class="m-0">
                <span>MCU : </span>
                <span>{{ Number(props.view?.mcu) }}</span> |
                <span>{{ Number(props.view?.mcu_amount).toFixed(2) }}</span>
              </p>
              <p class="m-0">Base Value : {{ Number(props.view?.base_value).toFixed(2) }}</p>
              <p class="m-0">Exporter Name : {{ props.view?.exporter_name }}</p>
              <p class="m-0">Exporter address : {{ props.view?.address }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">Details</div>
        </div>
        <div class="card-body p-2">
          <p class="m-0 text-uppercase">Name : {{ props.view?.client?.company_name }}</p>
          <p class="m-0 text-capitalize">Name : {{ props.view?.client?.name }}</p>
          <p class="m-0">Email : {{ props.view?.client?.email }}</p>
          <p class="m-0">Mobile : {{ props.view?.client?.mobile }}</p>
          <p class="m-0">Address : {{ props.view?.client?.address }}</p>
          <p class="m-0">Reference : {{ props.view?.client?.reference }}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">BIN Details</div>
        </div>
        <div class="card-body p-2">
          <p class="m-0">BIN : {{ props.view?.client?.bin }}</p>
          <p class="m-0">VAT Username : {{ props.view?.client?.vat_username }}</p>
          <p class="m-0">VAT Password : {{ props.view?.client?.vat_password }}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4 mb-2">
      <div class="card border h-100 mb-0">
        <div class="card-header border-bottom p-2">
          <div class="fw-bold text-uppercase">User Details</div>
        </div>
        <div :class="`card-body p-2 ${props.view?.client?.user ? 'd-block' : 'd-none'}`">
          <p class="m-0">Email : {{ props.view?.client?.user?.email }}</p>
          <p class="m-0">Mobile : {{ props.view?.client?.user?.mobile }}</p>
          <p class="m-0">Division : {{ props.view?.client?.division?.name }}</p>
          <p class="m-0">Status : {{ props.view?.client?.user?.status == 1 ? "Active" : "Inactive" }}</p>
        </div>
      </div>
    </div>
  </div>
  <!-- {{ props.view }} -->
</template>

<script setup>
import { empty } from '../../../helpers/utils';
import moment from 'moment';
import Spinner from '../../../components/Spinner.vue';

const props = defineProps({ view: { type: Object } });

// onMounted(async () => await props.fetched());
</script>

<style lang="scss" scoped>
.border {
  border-color: #CCCCCC !important;
}
</style>