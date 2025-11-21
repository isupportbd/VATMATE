<template>

  <Head title="Form 6.2.1" />
  <PageBar>
    <p class="m-0 fs-4">Form 6.2.1</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12">
      <DataTableSkeleton row="5" col="4" v-if="empty(props.form621)" />
      <DataTable :data="props.form621" :search="props.search" :option="[5, 10, 15]" :removable="false" v-else>
        <template #thead>
          <th>Details</th>
          <th>Contact</th>
          <th>User</th>
          <th class="text-center">Action</th>
        </template>
        <template #tbody="{ td }">
          <td>
            <p class="m-0">{{ td.client?.company_name }}</p>
            <p class="m-0">{{ td.client?.bin }}</p>
          </td>
          <td>
            <p class="m-0">{{ td.client?.email }}</p>
            <p class="m-0">{{ td.client?.mobile }}</p>
          </td>
          <td>
            <p class="m-0">{{ td.client?.user?.email }}</p>
            <p class="m-0">{{ td.client?.user?.mobile }}</p>
          </td>
          <td class="text-center">
            <Link :href="route('form621-client-single-index', { client_id: td.client_id })" class="btn btn-outline-dark"
              title="client individual">
            <i class='bi bi-stack'></i>
            </Link>
          </td>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, reactive, inject } from "vue";
import { router } from "@inertiajs/vue3";
import { empty } from '../../helpers/utils';

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import DataTableSkeleton from "../../components/datatable/DataTableSkeleton.vue";
import DataTable from '../../components/datatable/index.vue';

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  form621: { type: Object },
  search: { type: String }
});

// reload form621
const refresh = () => router.get(route('form621'), {}, { preserveScroll: true });
</script>