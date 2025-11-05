<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del" v-else>
    <template #thead>
      <th class="col-3">Details</th>
      <th class="col-4">Item | Date | Unit Price</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0">
          <span><i class="bi bi-person-bounding-box"></i></span> :
          <span>{{ td?.client?.user?.email }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-buildings"></i></span> :
          <span>{{ td?.client?.company_name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-database"></i></span> :
          <span>{{ td?.client?.bin }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-geo-alt"></i></span> :
          <span>{{ td.client?.address }}</span>
        </p>
      </td>
      <td>
        <p class="m-0">
          <span><i class="bi bi-bookmark"></i></span> :
          <span>{{ td?.item?.name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-tag"></i></span> :
          <span>{{ td?.item?.hs_code }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-cash"></i></span> :
          <span>{{ td?.unit_price }}</span>
        </p>
      </td>
      <td class="text-center">
        <div class="btn-group" role="group">
          <Button type="button" class="btn btn-outline-dark" title="edit" icon="bi bi-pencil-square"
            @click="props.edit(td.id)" />
        </div>
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import { empty } from '../../helpers/utils';

import DataTableSkeleton from "../../components/datatable/DataTableSkeleton.vue";
import DataTable from "../../components/datatable/index.vue";
import Button from "../../components/Button.vue";

const props = defineProps({
  data: { type: [Object, Array] },
  edit: { type: Function },
  del: { type: Function },
  search: { type: String }
});
</script>