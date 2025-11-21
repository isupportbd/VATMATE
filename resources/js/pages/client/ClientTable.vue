<template>
  <DataTableSkeleton row="5" col="4" v-if="empty(props.data)" />
  <DataTable :data="props.data" :search="props.search" :option="[5, 10, 15]" @remove="props.del"
    :removable="role('admin') ? true : false" v-else>
    <template #thead>
      <th>Details</th>
      <th>Contact</th>
      <th :class="{ 'd-none': !role('admin') }">Status</th>
      <th class="text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>
        <p class="m-0" :class="{ 'd-none': !td.user }">
          <span><i class="bi bi-person-bounding-box"></i></span> :
          <span>{{ td?.user?.email }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-database"></i></span> :
          <span>{{ td.bin }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-hospital"></i></span> :
          <span>{{ td.company_name }}</span>
        </p>
        <p class="m-0">
          <span><i class="bi bi-geo-alt"></i></span> :
          <span>{{ td.address }}</span>
        </p>
      </td>
      <td>
        <p class="m-0">
          <span><i class="bi bi-person-badge"></i></span> :
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
      <td class="text-center" :class="{ 'd-none': !role('admin') }">
        <Switch text="Alive" blank="Dead" :checked="Number(td.status)" @change="props.statusUpdate(td.id, td.status)"
          vertical />
      </td>
      <td class="text-center">
        <Button type="button" icon="bi bi-eye" class="btn btn-outline-dark mx-auto" title="view"
          @click="props.view(td.id)" />
        <Button type="button" class="btn btn-outline-dark mx-auto" title="edit" icon="bi bi-pencil-square"
          @click="props.edit(td.id)" />
        <Button type="button" class="btn btn-outline-dark mx-auto" title="release"
          :icon="empty(td?.user_id) ? 'bi bi-heartbreak-fill' : 'bi bi-link-45deg'"
          @click="!empty(td?.user_id) && props.release(td.id)" />
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import axios from "axios";
import { ref } from "vue";
import { role } from '../../composables/user';
import { empty } from '../../helpers/utils';

import DataTableSkeleton from "../../components/datatable/DataTableSkeleton.vue";
import DataTable from "../../components/datatable/index.vue";
import Button from "../../components/Button.vue";
import Switch from "../../components/Switch.vue";

const props = defineProps({
  data: { type: [Object, Array] },
  search: { type: String },
  view: { type: Function },
  edit: { type: Function },
  del: { type: Function },
  release: { type: Function },
  statusUpdate: { type: Function }
});
</script>