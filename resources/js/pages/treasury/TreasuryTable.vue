<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del" v-else>
    <template #thead>
      <th class="col-3">Details</th>
      <th class="col-5">Branch</th>
      <th class="col-4">Amount</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0">
          <span><i class="bi bi-person-bounding-box"></i></span> :
          <span>{{ td.client?.company_name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-database"></i></span> :
          <span>{{ td.client?.bin }}</span>
        </p>
      </td>
      <td>
        <p class="m-0">
          <span><i class="bi bi-map"></i></span> :
          <span>{{ td.branch?.name }}, {{ td.branch?.bank?.name }}, {{ td.branch?.division?.name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-calendar"></i></span> :
          <span>{{ moment(td.tax_period).format('MMM YYYY') }}</span>
        </p>
      </td>
      <td>
        <p class="m-0">
          <span><i class="bi bi-receipt"></i></span> :
          <span>{{ td.challan_no }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-coin"></i></span> :
          <span>{{ Number(td.amount)?.toFixed(2) }}</span>
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
import moment from 'moment-timezone';
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