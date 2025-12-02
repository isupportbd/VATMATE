<template>

  <Head title="Vat Factor" />
  <PageBar>
    <p class="m-0 fs-4">Vat Factor</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-4 mb-2 mb-lg-0">
      <VatfactorForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-8">
      <VatfactorTable :data="props.vatfactor" :search="props.search" :edit="edit" :del="del" />
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
import VatfactorForm from "./VatfactorForm.vue";
import VatfactorTable from "./VatfactorTable.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  vatfactor: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('vatfactor-single', { id }));
    formRef.value?.setForm(data.vatfactor); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('vatfactor-delete', { id: ids }), {
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

// reload vatfactor
const refresh = () => router.get(route('vatfactor'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['vatfactor'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>