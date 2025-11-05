<template>

  <Head title="Treasury" />
  <PageBar>
    <p class="m-0 fs-4">Treasury</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-4 mb-3 mb-lg-0">
      <TreasuryForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-8">
      <TreasuryTable :data="props.treasury" :search="props.search" :edit="edit" :del="del" />
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
import TreasuryForm from "./TreasuryForm.vue";
import TreasuryTable from "./TreasuryTable.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  treasury: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('treasury-single', { id }));
    formRef.value?.setForm(data.treasury); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('treasury-delete', { id: ids }), {
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

// reload branch
const refresh = () => router.get(route('treasury'), {}, { preserveScroll: true });
</script>