<template>

  <Head title="Client" />
  <PageBar>
    <p class="m-0 fs-4">Client</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="client-view" ref="modal" title="Client Details" size="xl">
    <template #modalbody>
      <ClientView :view="clientSingle" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-4 mb-2 mb-lg-0">
      <ClientForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-8">
      <ClientTable :data="props.client" :search="props.search" :view="view" :edit="edit" :del="del"
        :statusUpdate="statusUpdate" :release="release" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, inject, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { user, role } from "../../composables/user";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import ClientForm from "./ClientForm.vue";
import ClientTable from "./ClientTable.vue";
import Modal from "../../components/Modal.vue";
import ClientView from "./ClientView.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  client: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  const { data } = await axios.get(route('client-single', { id: id }));
  if (data.error) {
    Alert(`<b class='text-danger'>${data.error}</b>`);
  } else {
    formRef.value?.setForm(data.client); // 👈 directly set the form
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('client-delete', { id: ids }), {
      preserveScroll: true,
      onSuccess: (e) => {
        ids.length = 0;
        notify(e.props.flash.msg, 'danger');
        formRef.value?.resetForm(); // 👈 reset the form
      },
      onFinish: () => formRef.value?.resetForm()
    });
  });
};

// status update, user is active or inactive
const statusUpdate = async (id, status) => {
  if (status == "1") {
    router.put(route('client-status', id), { id: id, status: "0" }, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(e.props.flash.err);
        } else {
          notify(e.props.flash.msg, 'success');
        }
      }
    });
  } else {
    router.put(route('client-status', id), { id: id, status: "1" }, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(e.props.flash.err);
        } else {
          notify(e.props.flash.msg, 'success');
        }
      }
    });
  }
};

// modal popup
const modal = ref();
const closeme = () => modal.value.close();

// for single view popup
const clientSingle = ref();
const view = async (id) => await axios.get(`/client/${id}`).then((res) => {
  if (res.data.error) {
    modal.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modal.value.open();
    clientSingle.value = res.data.client;
  }
});

// release
const release = async (id) => {
  Confirm('Do you want to release ?', (ok) => {
    if (!ok) return;
    router.put(route('client-release', { id: id }), {}, {
      preserveScroll: true,
      onSuccess: (e) => {
        console.log(e);
        notify(e.props.flash.msg, 'info');
        formRef.value?.resetForm(); // 👈 reset the form
      }
    });
  });
};

// reload client
const refresh = () => router.get(route('client'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['client'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>