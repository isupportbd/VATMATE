<template>

  <Head title="Rate" />
  <PageBar>
    <p class="m-0 fs-4">Rate</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-2 mb-lg-0">
      <RateForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <RateTable :data="props.rate" :search="props.search" :edit="edit" :del="del" />
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
import RateForm from "./RateForm.vue";
import RateTable from "./RateTable.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  rate: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  const { data } = await axios.get(route('rate-single', { id }));
  if (data.error) {
    Alert(`<b class='text-danger'>${data.error}</b>`);
  } else {
    formRef.value?.setForm(data); // 👈 directly set the form
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('rate-delete', { id: ids }), {
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

// reload rate
const refresh = () => router.get(route('rate'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['rate'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>