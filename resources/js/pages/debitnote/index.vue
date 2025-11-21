<template>

  <Head title="Debit Note" />
  <PageBar>
    <p class="m-0 fs-4">Debit Note</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="debitnote-view" ref="modal" title="Debit Note Details" size="xl">
    <template #modalbody>
      <DebitnoteView :view="debitnoteSingle" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">close</button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-2 mb-lg-0">
      <DebitForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <DebitTable :data="props.debitnote" :search="props.search" :view="view" :edit="edit" :del="del" />
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
import DebitForm from "./DebitForm.vue";
import DebitTable from "./DebitTable.vue";
import Modal from "../../components/Modal.vue";
import DebitnoteView from "./DebitnoteView.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  debitnote: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  const { data } = await axios.get(route('debitnote-single', { id }));
  if (data.error) {
    Alert(`<b class='text-danger'>${data.error}</b>`);
  } else {
    formRef.value?.setForm(data.debitnote); // 👈 directly set the form
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('debitnote-delete', { id: ids }), {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(`<b class='text-danger'>${e.props.flash.err}</b>`);
        } else {
          ids.length = 0;
          notify(e.props.flash.msg, 'danger');
          formRef.value?.resetForm(); // 👈 reset the form
        }
      },
      onFinish: () => formRef.value?.resetForm()
    });
  });
};

// modal popup
const modal = ref();
const closeme = () => modal.value.close();

// for single view popup
const debitnoteSingle = ref();
const view = async (id) => await axios.get(`/debitnote/${id}`).then((res) => {
  if (res.data.error) {
    modal.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modal.value.open();
    debitnoteSingle.value = res.data.debitnote;
  }
});

// reload debitnote
const refresh = () => router.get(route('debitnote'), {}, { preserveScroll: true });
</script>