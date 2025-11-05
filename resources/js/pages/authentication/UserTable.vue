<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del" v-else>
    <template #thead>
      <th>Email</th>
      <th :class="`text-center ${$page.props.auth.user.role == 'superadmin' ? '' : 'd-none'}`">Role</th>
      <th>Status</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0">
          <span><i class="bi bi-person"></i></span> :
          <span>{{ td.name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-envelope"></i></span> :
          <span>{{ td.email }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-telephone"></i></span> :
          <span>{{ td.mobile }}</span>
        </p>
      </td>
      <td :class="`text-center ${$page.props.auth.user.role == 'superadmin' ? '' : 'd-none'}`">{{ td.role.name }}</td>
      <td class="text-center">
        <Switch text="Alive" blank="Dead" :checked="Number(td.status)" @change="props.statusUpdate(td.id, td.status)"
          vertical />
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
import Switch from "../../components/Switch.vue";

const props = defineProps({
  data: { type: [Object, Array] },
  edit: { type: Function },
  del: { type: Function },
  statusUpdate: { type: Function },
  search: { type: String }
});
</script>