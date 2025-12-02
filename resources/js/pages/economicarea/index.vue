<template>

  <Head title="Economic Area" />
  <PageBar>
    <p class="m-0 fs-4">Economic Area</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 mb-2">
      <EconomicareaForm ref="formRef" />
    </div>
    <div class="col-12">
      <EconomicareaTable :data="props.economicarea" :search="props.search" :del="del" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, inject, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { user, role } from '../../composables/user';

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import EconomicareaForm from "./EconomicareaForm.vue";
import EconomicareaTable from "./EconomicareaTable.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from '../../helpers/customAlert';

const props = defineProps({
  economicarea: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// delete
const formRef = ref();
const del = async (clientId, itemId) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('economicarea-delete', { client_id: clientId, item_id: itemId }), {
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

// reload economicarea
const refresh = () => router.get(route('economicarea'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['economicarea'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>