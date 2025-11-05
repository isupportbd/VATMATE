<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del" v-else>
    <template #thead>
      <th>Name</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <span><i class="bi bi-flask-florence"></i></span> :
        <span>{{ td.name }}</span>
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