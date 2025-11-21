<template>

  <Head title="Form 4.4" />
  <PageBar>
    <p class="m-0 fs-4">Form 4.4</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="form44-view" ref="modal" title="Client Details" size="xl">
    <template #modalbody>
      <Form44View :view="form44Single" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-2 mb-lg-0">
      <Form44Form ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <Form44Table :data="props.form44" :search="props.search" :view="view" :edit="edit" :del="del" />
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
import Form44Form from "./Form44Form.vue";
import Form44Table from "./Form44Table.vue";
import Form44View from "./Form44View.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  form44: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('form44-single', { id }));
    formRef.value?.setForm(data.form44); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('form44-delete', { id: ids }), {
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
const view = async (id) => await axios.get(`/form44/${id}`).then((res) => form44Single.value = res.data.form44);

// reload form44
const refresh = () => router.get(route('form44'), {}, { preserveScroll: true });
</script>