<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del" v-else>
    <template #thead>
      <th>Name | HS Code</th>
      <th>Description</th>
      <th class="text-center">CD</th>
      <th class="text-center">RD</th>
      <th class="text-center">SD</th>
      <th class="text-center">VAT</th>
      <th class="text-center">AT</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0">
          <span><i class="bi bi-bookmark"></i></span> :
          <span>{{ td?.name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-tag"></i></span> :
          <span>{{ td?.hs_code }}</span>
        </p>
      </td>
      <td>
        <p class="m-0">
          <span><i class="bi bi-blockquote-left"></i></span> :
          <span>{{ td?.description }}</span>
        </p>
      </td>
      <td class="text-center">{{ td.cd }}%</td>
      <td class="text-center">{{ td.rd }}%</td>
      <td class="text-center">{{ td.sd }}%</td>
      <td class="text-center">{{ td.vat }}%</td>
      <td class="text-center">{{ td.at }}%</td>
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
import { empty } from "../../helpers/utils";

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