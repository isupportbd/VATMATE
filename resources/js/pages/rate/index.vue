<template>

  <Head title="Rate" />
  <PageBar>
    <p class="m-0 fs-4">Rate</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-4 mb-3 mb-lg-0">
      <RateForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-8">
      <RateTable :data="props.rate" :search="props.search" :edit="edit" :del="del" />
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
import RateForm from "./RateForm.vue";
import RateTable from "./RateTable.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  rate: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('rate-single', { id }));
    formRef.value?.setForm(data.rate); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('rate-delete', { id: ids }), {
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

// reload rate
const refresh = () => router.get(route('rate'), {}, { preserveScroll: true });
</script>