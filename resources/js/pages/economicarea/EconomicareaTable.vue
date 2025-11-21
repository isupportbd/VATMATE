<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" :removable="false" v-else>
    <template #thead>
      <th class="col-5">Name</th>
      <th class="col-7 w-100">Economic Area</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0"><i class="bi bi-hospital"></i> : {{ td.company_name }}</p>
        <p class="m-0"><i class="bi bi-database"></i> : {{ td.bin }}</p>
        <p class="m-0"><i class="bi bi-envelope"></i> : {{ td.email }}</p>
        <p class="m-0"><i class="bi bi-telephone"></i> : {{ td.mobile }}</p>
        <p class="m-0"><i class="bi bi-geo-alt"></i> : {{ td.address }}</p>
      </td>
      <td>
        <p class="m-0 border-bottom border-secondary" v-for="item in td.items">
          <i class="bi bi-card-checklist"></i> :
          <span>{{ item?.name }}</span>
          <span :class="`badge rounded-pill ms-2 mb-1 text-bg-${item?.has_form621 ? 'success' : 'danger'}`"
            style="cursor: pointer;" @click="props.del(td?.id, item?.id)">6.2.1</span>
        </p>
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import { empty } from "../../helpers/utils";

import DataTableSkeleton from "../../components/datatable/DataTableSkeleton.vue";
import DataTable from "../../components/datatable/index.vue";

const props = defineProps({
  data: { type: [Object, Array] },
  edit: { type: Function },
  del: { type: Function },
  search: { type: String }
});
</script>