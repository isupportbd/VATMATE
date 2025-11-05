<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del" v-else>
    <template #thead>
      <th class="col-4">Details</th>
      <th class="col-3">Costsheet</th>
      <th class="col-3 text-center">Value | Rate</th>
      <th class="text-center">Action</th>
    </template>
    <template #extra>
      <p class="text-start text-sm-center">
        <span>
          <span>Name: </span>
          <span class="fw-semibold">{{ props.form43Current?.item?.name }}</span>
          <span>{{ props.form43Current?.additionsum[0] ? ` | ` : '' }}</span>
        </span>
        <span :class="props.form43Current?.additionsum.length > 0 ? '' : 'd-none'">
          <span>
            <span>Value: </span>
            <span class="fw-semibold">{{ Number(props.form43Current?.additionsum[0]?.valuesum)?.toFixed(2) }}</span>
            <span>{{ props.form43Current?.additionsum[0] ? ` | ` : '' }}</span>
          </span>
          <span>
            <span>Rate: </span>
            <span class="fw-semibold">{{ Number(props.form43Current?.additionsum[0]?.ratesum)?.toFixed(2) }}%</span>
          </span>
        </span>
      </p>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0">
        <p class="m-0">
          <span><i class="bi bi-building"></i></span> :
          <span>{{ td.form43?.client?.company_name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-database"></i></span> :
          <span>{{ td.form43?.client?.bin }}</span>
        </p>
        </p>
      </td>
      <td>
        <p class="m-0">
          <span><i class="bi bi-card-list"></i></span> :
          <span>{{ td.costsheet?.name }}</span>
        </p>
      </td>
      <td class="text-center">
        {{ Number(td.value)?.toFixed(2) }} <span class="px-1">|</span>
        {{ Number(td.rate)?.toFixed(2) }}%
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
  form43Current: { type: [Object, Array] },
  edit: { type: Function },
  del: { type: Function },
  search: { type: String }
});
</script>