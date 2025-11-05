<template>

  <Head title="Form 4.3" />
  <PageBar>
    <p class="m-0 fs-4">Form 4.3</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="form43-view" ref="modal" title="Form 4.3 Details" size="lg">
    <template #modalbody>
      <Form43View :view="form43Single" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-3 mb-lg-0">
      <Form43Form ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <Form43Table :data="props.form43" :search="props.search" :view="view" :edit="edit" :del="del" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, inject } from "vue";
import { router, Link } from "@inertiajs/vue3";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import Form43Form from "./Form43Form.vue";
import Form43Table from "./Form43Table.vue";
import Modal from "../../components/Modal.vue";
import Form43View from "./Form43View.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  form43: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('form43-single', { id }));
    formRef.value?.setForm(data.form43); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('form43-delete', { id: ids }), {
      preserveScroll: true,
      onSuccess: (e) => {
        // ids.length = 0;
        notify(e.props.flash.msg, 'danger');
        formRef.value?.resetForm(); // 👈 reset the form
      },
      onFinish: () => formRef.value?.resetForm()
    });
  });
};

// for single view popup
const form43Single = ref();
const view = async (id) => await axios.get(route('form43-single', { id: id })).then((res) => form43Single.value = res.data?.form43);

// modal popup
const modal = ref();
const closeme = () => modal.value.close();

// reload form43
const refresh = () => router.get(route('form43'), {}, { preserveScroll: true });
</script>