<template>

  <Head title="Form 4.6" />
  <PageBar>
    <p class="m-0 fs-4">Form 4.6</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="form46-view" ref="modal" title="Client Details" size="xl">
    <template #modalbody>
      <Form46View :view="form45Single" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-2 mb-lg-0">
      <Form46Form ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <Form46Table :data="props.form46" :search="props.search" :view="view" :edit="edit" :del="del" />
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
import Form46Form from "./Form46Form.vue";
import Form46Table from "./Form46Table.vue";
import Form46View from "./Form46View.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  form46: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('form46-single', { id }));
    formRef.value?.setForm(data.form46); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('form46-delete', { id: ids }), {
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
const form46Single = ref();
const view = async (id) => await axios.get(`/form46/${id}`).then((res) => form46Single.value = res.data.form46);

// reload form46
const refresh = () => router.get(route('form46'), {}, { preserveScroll: true });
</script>