<template>

  <Head title="Credit Note" />
  <PageBar>
    <p class="m-0 fs-4">Credit Note</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="creditnote-view" ref="modal" title="Client Details" size="xl">
    <template #modalbody>
      <CreditnoteView :view="creditnoteSingle" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-3 mb-lg-0">
      <CreditForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <CreditTable :data="props.creditnote" :search="props.search" :view="view" :edit="edit" :del="del" />
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
import CreditForm from "./CreditForm.vue";
import CreditTable from "./CreditTable.vue";
import Modal from "../../components/Modal.vue";
import CreditnoteView from "./CreditnoteView.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  creditnote: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('creditnote-single', { id }));
    formRef.value?.setForm(data.creditnote); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('creditnote-delete', { id: ids }), {
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
const creditnoteSingle = ref();
const view = async (id) => await axios.get(`/creditnote/${id}`).then((res) => creditnoteSingle.value = res.data.creditnote);

// reload creditnote
const refresh = () => router.get(route('creditnote'), {}, { preserveScroll: true });
</script>