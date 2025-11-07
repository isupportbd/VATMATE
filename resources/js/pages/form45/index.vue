<template>

  <Head title="Form 4.5" />
  <PageBar>
    <p class="m-0 fs-4">Form 4.5</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="form45-view" ref="modal" title="Client Details" size="xl">
    <template #modalbody>
      <Form45View :view="form45Single" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-6 mb-3 mb-lg-0">
      <Form45Form ref="formRef" />
    </div>
    <div class="col-12 col-lg-6">
      <Form45Table :data="props.form45" :search="props.search" :view="view" :edit="edit" :del="del" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, inject } from "vue";
import { router } from "@inertiajs/vue3";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import Modal from "../../components/Modal.vue";
import Form45Form from "./Form45Form.vue";
import Form45Table from "./Form45Table.vue";
import Form45View from "./Form45View.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  form45: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('form45-single', { id }));
    formRef.value?.setForm(data.form45); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('form45-delete', { id: ids }), {
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

// modal popup
const modal = ref();
const closeme = () => modal.value.close();

// for single view popup
const form44Single = ref();
const view = async (id) => await axios.get(`/form45/${id}`).then((res) => form44Single.value = res.data.form44);

// reload form45
const refresh = () => router.get(route('form45'), {}, { preserveScroll: true });
</script>