<template>
  <Modal id="client-view" ref="modal" title="Client Details" size="xl">
    <template #modalbody>
      <ClientView :view="clientSingle" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

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
        <Button type="button" icon="bi bi-eye" class="btn btn-outline-secondary mx-auto" title="view"
          data-bs-toggle="modal" data-bs-target="#client-view" @click="view(td.id)" />
        <Button type="button" class="btn btn-outline-dark mx-auto" title="edit" icon="bi bi-pencil-square"
          @click="props.edit(td.id)" />
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
import Modal from "../../components/Modal.vue";
import Switch from "../../components/Switch.vue";

import ClientView from "./ClientView.vue";

const props = defineProps({
  data: { type: [Object, Array] },
  search: { type: String },
  edit: { type: Function },
  del: { type: Function },
  statusUpdate: { type: Function }
});

// modal popup
const modal = ref();
const closeme = () => modal.value.close();

// for single view popup
const clientSingle = ref();
const view = async (id) => await axios.get(`/client/${id}`).then((res) => clientSingle.value = res.data.client);
</script>