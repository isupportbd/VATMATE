<template>

  <Head title="Addition" />
  <PageBar>
    <p class="m-0 fs-4">Addition</p>
  </PageBar>

  <Refresh>
    <div class="d-flex align-items-center">
      <Button class="nav-btn me-1" title="refresh" icon='bi bi-arrow-repeat' @click="refresh(route().params.id)" />
      <Link class="nav-btn" title="back" :href="route('form43')">
      <i class="bi bi-chevron-left"></i>
      </Link>
    </div>
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-4 mb-3 mb-lg-0">
      <AdditionForm ref="formRef" :form43Current="props.form43" />
    </div>
    <div class="col-12 col-lg-8">
      <AdditionTable :data="props.addition" :search="props.search" :form43Current="props.form43" :edit="edit"
        :del="del" />
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
import AdditionForm from "./AdditionForm.vue";
import AdditionTable from "./AdditionTable.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  addition: { type: Object },
  form43: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('addition-single', { id }));
    formRef.value?.setForm(data.addition); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('addition-delete', { id: ids }), {
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

// reload addition
const refresh = (id) => router.get(route('addition', { id: id }), {}, { preserveScroll: true });
</script>