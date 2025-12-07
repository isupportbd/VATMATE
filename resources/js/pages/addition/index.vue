<template>

  <Head title="Addition" />
  <PageBar>
    <p class="m-0 fs-4">Addition</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh(route().params.id)" />
    <Link class="nav-btn" title="back" :href="route('form43')">
      <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-4 mb-2 mb-lg-0">
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
import { ref, inject, onMounted, onBeforeUnmount } from "vue";
import { router, Link } from "@inertiajs/vue3";
import { user, role } from "../../composables/user";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import AdditionForm from "./AdditionForm.vue";
import AdditionTable from "./AdditionTable.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  addition: { type: Object },
  form43: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  const { data } = await axios.get(route('addition-single', { id }));
  if (data.error) {
    Alert(`<b class='text-danger'>${data.error}</b>`);
    return;
  } else {
    formRef.value?.setForm(data.addition); // 👈 directly set the form
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('addition-delete', { id: ids }), {
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

// reload addition
const refresh = (id) => router.get(route('addition', { id: id }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = (e) => router.reload({ only: ['addition', 'form43'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>