<template>

  <Head title="Mushak Note" />
  <PageBar>
    <p class="m-0 fs-4">Mushak Note</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-4 mb-2 mb-lg-0">
      <MushaknoteForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-8">
      <MushaknoteTable :data="props.mushaknote" :search="props.search" :edit="edit" />
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
import MushaknoteForm from "./MushaknoteForm.vue";
import MushaknoteTable from "./MushaknoteTable.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  mushaknote: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('mushaknote-single', { id }));
    formRef.value?.setForm(data.mushaknote); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// reload mushaknote
const refresh = () => router.get(route('mushaknote'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['mushaknote'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>