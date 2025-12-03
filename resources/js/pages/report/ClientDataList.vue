<template>

  <Head title="Month List" />
  <PageBar>
    <p class="m-0 fs-4">Month List</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh(route().params.client_id)" />
    <Link title="back" :href="route('report')" class="nav-btn">
    <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <DataTableSkeleton row="5" col="4" v-if="empty(props.clientdatalist)" />
  <DataTable :data="props.clientdatalist" :option="[5, 10, 15]" :removable="false" v-else>
    <template #thead>
      <th class="col-5">Client Details</th>
      <th class="col-3">Tax Period | Editor</th>
      <th class="col-4 w-100 text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0"><i class="bi bi-person-circle"></i> : {{ td?.client?.company_name }}</p>
        <p class="m-0"><i class="bi bi-database"></i> : {{ td?.client?.bin }}</p>
        <p class="m-0"><i class="bi bi-envelope-at"></i> : {{ td?.client?.email }}</p>
        <p class="m-0"><i class="bi bi-telephone"></i> : {{ td?.client?.mobile }}</p>
      </td>
      <td>
        <i class="bi bi-calendar"></i> : {{ moment(td?.tax_period).format('MMMM YYYY') }}
        <p class="m-0"><i class="bi bi-person-square"></i> : {{ td?.client?.user?.email }}</p>
        <p class="m-0"><i class="bi bi-telephone"></i> : {{ td?.client?.user?.mobile }}</p>
      </td>
      <td>
        <div class="container-fluid">
          <div class="row">
            <div class="container-fluid p-0">
              <div class="row g-1">
                <div class="col-3">
                  <Link :href="route('report-621', { client_id: td.client_id })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>6.2.1</span>
                  </Link>
                </div>
                <div class="col-6">
                  <Link :href="route('report-summary', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>Summary</span>
                  </Link>
                </div>
                <div class="col-3">
                  <Link :href="route('report-boe', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>BoE</span>
                  </Link>
                </div>
                <div class="col-4">
                  <Link :href="route('report-43', { client_id: td?.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2" title="edit">
                  <span>4.3</span>
                  </Link>
                </div>
                <div class="col-4">
                  <Link :href="route('form-44', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>4.4</span>
                  </Link>
                </div>
                <div class="col-4">
                  <Link :href="route('form-45', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>4.5</span>
                  </Link>
                </div>
                <div class="col-3">
                  <Link :href="route('report-63', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>6.3</span>
                  </Link>
                </div>
                <div class="col-3">
                  <Link :href="route('creditnote-67', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>6.7</span>
                  </Link>
                </div>
                <div class="col-3">
                  <Link :href="route('debitnote-68', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>6.8</span>
                  </Link>
                </div>
                <div class="col-3">
                  <Link :href="route('report-91', { client_id: td.client_id, monthYear: td?.tax_period })"
                    class="btn btn-outline-dark w-100 rounded-2">
                  <span>9.1</span>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import moment from 'moment-timezone';
import { onMounted, onBeforeUnmount } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { empty } from '../../helpers/utils';
import { user, role } from "../../composables/user";

import PageBar from '../../components/PageBar.vue';
import Refresh from '../../components/Refresh.vue';
import Button from '../../components/Button.vue';
import DataTableSkeleton from '../../components/datatable/DataTableSkeleton.vue';
import DataTable from '../../components/datatable/index.vue';

const props = defineProps({
  clientdatalist: { type: Object }
});

// reload form621
const refresh = (id) => router.get(route('report-client-data-list', { client_id: id }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['clientdatalist'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style lang="scss" scoped></style>