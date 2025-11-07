<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del" v-else>
    <template #thead>
      <th class="col-4">Details</th>
      <th class="col-4">Item | Date</th>
      <th class="col-4">Reason</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <!-- {{ td }} -->
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
      </td>
      <td>
        <span><i class="bi bi-chat-left"></i></span> :
        <span>{{ td?.reason_for_issuance }}</span>
      </td>
      <td class="text-center">
        <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark" title="view" data-bs-toggle="modal"
          data-bs-target="#creditnote-view" @click="props.view(td.id)" />
        <Button type="button" class="btn btn-outline-dark" title="edit" icon="bi bi-pencil-square"
          @click="props.edit(td.id)" />
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import { empty } from '../../helpers/utils';
import { ref } from 'vue';

import DataTableSkeleton from "../../components/datatable/DataTableSkeleton.vue";
import DataTable from "../../components/datatable/index.vue";
import Button from "../../components/Button.vue";

const props = defineProps({
  data: { type: [Object, Array] },
  edit: { type: Function },
  del: { type: Function },
  view: { type: Function },
  search: { type: String }
});
</script>