<template>

  <Head title="Report 4.3" />
  <PageBar>
    <p class="m-0 fs-4">Report 4.3</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.client_id, route().params.monthYear)" />
    <Link title="back" :href="route('report-client-data-list', { client_id: route().params.client_id })"
      class="nav-btn">
      <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <DataTableSkeleton row="5" col="4" v-if="empty(props.report43)" />
  <DataTable :data="props.report43" :search="props.search" :option="[5, 10, 15]" :removable="false" v-else>
    <template #thead>
      <th class="col-5">Client</th>
      <th class="col-4">Item</th>
      <th class="col-4 text-center">Tax Period</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <!-- {{ td }} -->
      <td :class="((td.last == 1) && role('admin')) && 'text-primary'">
        <p class="m-0">
          <span><i class="bi bi-building"></i></span> :
          <span>{{ td?.client?.company_name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-database"></i></span> :
          <span>{{ td?.client?.bin }}</span>
        </p>
      </td>
      <td :class="((td.last == 1) && role('admin')) && 'text-primary'">
        <p class="m-0">
          <span><i class="bi bi-bookmark"></i></span> :
          <span>{{ td?.item?.name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-tag"></i></span> :
          <span>{{ td?.item?.hs_code }}</span>
        </p>
        <div :class="td.additionsum.length > 0 ? 'text-uppercase fw-bold' : 'd-none'" style="font-size: 10px;">
          <p class="m-0">
            value: {{ Number(td.additionsum[0]?.valuesum)?.toFixed(2) }} | rate: {{
              Number(td.additionsum[0]?.ratesum)?.toFixed(2) }}</p>
          <p class="m-0"></p>
        </div>
      </td>
      <td class="text-center" :class="((td.last == 1) && role('admin')) && 'text-primary'">
        {{ moment(td?.boe_date).format("MMM YYYY") }}
      </td>
      <td class="text-center">
        <div class="btn-group" role="group">
          <Link
            :href="route('report-43-view', { id: td?.id, client_id: route().params.client_id, monthYear: route().params.monthYear })"
            class="btn btn-outline-dark w-100 rounded-2" title="view">
            <i class="bi bi-eye"></i>
          </Link>
          <Link
            :href="route('report-43-forwarding', { id: td?.id, client_id: route().params.client_id, monthYear: route().params.monthYear })"
            class="btn btn-outline-dark w-100 rounded-2" title="forwarding">
            <i class="bi bi-file-earmark-text"></i>
          </Link>
        </div>
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import { ref, inject, onMounted, onBeforeUnmount } from 'vue';
import { empty } from '../../../helpers/utils';
import { role, user } from '../../../composables/user';
import { Link, router } from '@inertiajs/vue3';

import DataTableSkeleton from "../../../components/datatable/DataTableSkeleton.vue";
import DataTable from "../../../components/datatable/index.vue";
import Button from "../../../components/Button.vue";
import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import moment from 'moment-timezone';

const props = defineProps({
  report43: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// reload report 43
const refresh = (client_id, monthYear) => router.get(route('report-43', { client_id, monthYear }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['report43'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>