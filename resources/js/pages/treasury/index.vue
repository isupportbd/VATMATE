<template>

  <Head title="Treasury" />
  <PageBar>
    <p class="m-0 fs-4">Treasury</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <Modal id="treasury" ref="modal" title="Treasury Details" size="lg">
    <template #modalbody>
      <TreasuryDetails :view="treasurySingle" />
    </template>
    <template #modalfooter>
      <button type="button" class="btn btn-primary" @click="closeme">
        <span class="me-1">Close</span> <i class="bx bx-x-circle"></i>
      </button>
    </template>
  </Modal>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-2 mb-lg-0">
      <TreasuryForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <TreasuryTable :data="props.treasury" :search="props.search" :view="view" :edit="edit" :del="del" />
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
import TreasuryForm from "./TreasuryForm.vue";
import TreasuryTable from "./TreasuryTable.vue";
import Modal from "../../components/Modal.vue";
import TreasuryDetails from "./TreasuryDetails.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  treasury: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  const { data } = await axios.get(route('treasury-single', { id }));
  if (data.error) {
    Alert(`<b class='text-danger'>${data.error}</b>`);
  } else {
    formRef.value?.setForm(data.treasury); // 👈 directly set the form
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('treasury-delete', { id: ids }), {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(`<b class='text-danger'>${e.props.flash.err}</b>`);
        }
        else {
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
const treasurySingle = ref();
const view = async (id) => await axios.get(`/treasury/${id}`).then((res) => {
  if (res.data.error) {
    modal.value.close();
    Alert(`<b class='text-danger'>${res.data.error}</b>`);
  } else {
    modal.value.open();
    treasurySingle.value = res.data.treasury;
  }
});

// reload branch
const refresh = () => router.get(route('treasury'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['treasury'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>