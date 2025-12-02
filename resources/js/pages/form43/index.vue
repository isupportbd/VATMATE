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
    <div class="col-12 col-lg-5 mb-2 mb-lg-0">
      <Form43Form ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <Form43Table :data="props.form43" :search="props.search" :view="view" :edit="edit" :del="del" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, inject, onMounted, onBeforeUnmount, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import { user, role } from "../../composables/user";
import { empty } from "../../helpers/utils";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import Form43Form from "./Form43Form.vue";
import Form43Table from "./Form43Table.vue";
import Modal from "../../components/Modal.vue";
import Form43View from "./Form43View.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  form43: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// modal popup
const modal = ref();
const closeme = () => modal.value.close();

// display single
const formRef = ref(null);
const edit = async (id) => {
  const { data } = await axios.get(route('form43-single', { id }));
  if (data.error) {
    Alert(`<b class='text-danger'>${data.error}</b>`);
  }
  else {
    formRef.value?.setForm(data); // 👈 directly set the form
  }
};

// delete
const del = async (id) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('form43-delete', { id: id }), {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(`<b class='text-danger'>${e.props.flash.err}</b>`);
        } else {
          notify(e.props.flash.msg, 'danger');
          formRef.value?.resetForm(); // 👈 reset the form
        }
      },
      onFinish: () => formRef.value?.resetForm()
    });
  });
};

// for single view popup

const form43Single = ref();
const view = async (id) => {
  const { data } = await axios.get(route('form43-single', { id }));
  if (data.error) {
    modal.value.close();
    Alert(`<b class='text-danger'>${data.error}</b>`);
  } else {
    modal.value.open();
    form43Single.value = data?.form43;
  }
};

// reload form43
const refresh = () => router.get(route('form43'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = async () => {
    router.reload({ only: ['form43'], preserveState: true, preserveScroll: true });
  };

  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>