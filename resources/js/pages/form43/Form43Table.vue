<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" :removable="false" v-else>
    <template #thead>
      <th class="col-7">Client</th>
      <th class="col-5">Item</th>
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
            value: {{
              Number(td.additionsum[0]?.valuesum)?.toFixed(2) }} | rate: {{ Number(td.additionsum[0]?.ratesum)?.toFixed(2)
            }}</p>
          <p class="m-0"></p>
        </div>
      </td>
      <td class="text-center">
        <div class="btn-group" role="group">
          <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark" title="view" data-bs-toggle="modal"
            data-bs-target="#form43-view" @click="props.view(td.id)" />
          <Button type="button" class="btn btn-outline-dark" title="edit" icon="bi bi-pencil-square"
            @click="props.edit(td.id)" />
        </div>
        <div class="btn-group" role="group">
          <Link :href="route('addition', { id: td.id })" class="btn btn-outline-dark" title="addition">
          <i class="bi bi-plus-circle"></i>
          </Link>
          <Button type="button" class="btn btn-outline-danger" title="delete" icon="bi bi-trash"
            @click="props.del(td.id)" />
        </div>
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import { empty } from '../../helpers/utils';
import { role } from '../../composables/user';
import { Link, router } from '@inertiajs/vue3';

import DataTableSkeleton from "../../components/datatable/DataTableSkeleton.vue";
import DataTable from "../../components/datatable/index.vue";
import Button from "../../components/Button.vue";

const props = defineProps({
  data: { type: [Object, Array] },
  view: { type: Function },
  edit: { type: Function },
  del: { type: Function },
  search: { type: String }
});
</script>