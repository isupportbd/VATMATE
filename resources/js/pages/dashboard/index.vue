<template>

  <Head title="Dashboard" />
  <PageBar>
    <p class="m-0 fs-4">Dashboard</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <h1>Dashboard</h1>
  <!-- <div class="container-fluid px-0 mb-2">
    <div class="row">
      <div class="col-12">
        <div class="card card-body">
          <FilterByUser :filteredwithuser="filteredWithUser" :resetuserbasedfilter="resetUserBasedFilter" :class="{ 'd-none': !role('admin') }" />
          <div class="row g-2">
            <Card title="Total Client" :data="props.total_client" icon="bi bi-people" foot="Clients" />
            <Card title="Jan 2026" :data="props.current_transaction_client" :client="props.current_month_client" icon="bi bi-person" foot="Transaction" />
            <Card title="Dec 2025" :data="props.previous_transaction_client" :client="props.previous_month_client" icon="bi bi-person" foot="Transaction" />
            <Card title="Nov 2025" :data="props.two_previous_transaction_client" :client="props.two_month_ago_client" icon="bi bi-person" foot="Transaction" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="row">
      <div class="col-12">
        <div class="card card-body">
          <FilterByClient ref="client" :filteredwithclient="filteredWithClientMonth" :resetclientbasedfilter="resetClientBasedFilter" />
          <div class="row g-2">
            <div class="col-12 col-md-6 col-lg-7">
              <div class="row g-2 mb-2">
                <div class="col-12 col-lg-6">
                  <div class="card card-body h-100">
                    <p class="m-0"><span>BIN :</span> <span>{{ props.summary?.client?.bin ?? "xxxxxxxxxxxxxx" }}</span></p>
                    <p class="m-0"><span>Client :</span> <span>{{ props.summary?.client?.company_name ?? "xxxxxxxxxxxxxx" }}</span></p>
                    <p class="m-0"><span>Address :</span> <span>{{ props.summary?.client?.address ?? "xxxxxxxxxxxxxx" }}</span></p>
                  </div>
                </div>
                <div class="col-12 col-lg-6">
                  <div class="card card-body h-100">
                    <p class="m-0"><span>Name :</span> <span>{{ props.summary?.client?.name ?? "xxxxxxxxxxxxxx" }}</span></p>
                    <p class="m-0"><span>Mobile :</span> <span>{{ props.summary?.client?.mobile ?? "xxxxxxxxxxxxxx" }}</span></p>
                    <p class="m-0"><span>Email :</span> <span>{{ props.summary?.client?.email ?? "xxxxxxxxxxxxxx" }}</span></p>
                  </div>
                </div>
              </div>
              <div class="card card-body">
                <div class="row">
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>{{ props.summary?.treasury }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Note 52 : </span> <span>{{ props.summary?.prevNetPayable }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Net Payable VAT : </span> <span>{{ props.summary?.netPayableRegular }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Note 65 : </span> <span>{{ props.summary?.closignBalanceCurrentRegular }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>MCU : </span> <span>{{ props.summary?.totalMcu }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Debit Note : </span> <span>{{ props.summary?.totalDebitnote }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Credit Note : </span> <span>{{ props.summary?.totalCreditnote }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Mushak 4.4 : </span> <span>{{ props.summary?.totalForm44 }}</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Mushak 4.5 : </span> <span>{{ props.summary?.totalForm45 }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-5">
              <div class="row g-2">
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> -->
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { role } from "../../composables/user";
import { empty } from '../../helpers/utils';
import PageBar from '../../components/PageBar.vue';
import Refresh from '../../components/Refresh.vue';
import Button from '../../components/Button.vue';
import Card from './Card.vue';
import FilterByUser from './FilterByUser.vue';
import FilterByClient from './FilterByClient.vue';

const props = defineProps({
  total_client: { type: [String, Number], default: 0 },
  current_month_client: { type: [String, Number], default: 0 },
  previous_month_client: { type: [String, Number], default: 0 },
  two_month_ago_client: { type: [String, Number], default: 0 },
  current_transaction_client: { type: [String, Number], default: 0 },
  previous_transaction_client: { type: [String, Number], default: 0 },
  two_previous_transaction_client: { type: [String, Number], default: 0 },
  summary: { type: Object },
});

const filteredWithUser = async (userid) => router.get(route('dashboard'), { user_id: userid }, { preserveScroll: true, preserveState: true, replace: true });

const resetUserBasedFilter = () => {
  router.get(route('dashboard'), {}, {
    only: [
      'total_client',
      'current_month_client',
      'previous_month_client',
      'two_month_ago_client',
      'current_transaction_client',
      'previous_transaction_client',
      'two_previous_transaction_client'
    ],
    preserveScroll: true,
    preserveState: true,
    replace: true
  });
};

const filteredWithClientMonth = async (client_id, monthYear) => router.get(route('dashboard'), { client_id: client_id, monthYear: monthYear }, { preserveScroll: true, preserveState: true, replace: true });

const client = ref(null);
const resetClientBasedFilter = () => {
  router.get(route('dashboard'), {}, {
    onSuccess: () => client.value.field,
    only: ['summary'],
    preserveScroll: true,
    preserveState: true,
    replace: true
  });
};

const refresh = () => router.get(route('dashboard'), {}, { preserveScroll: true });
</script>

<style lang="scss" scoped></style>