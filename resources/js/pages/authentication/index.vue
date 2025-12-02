<template>

  <Head title="User" />
  <PageBar>
    <p class="m-0 fs-4">Users</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-3 mb-lg-0">
      <UserForm ref="formRef" />
    </div>
    <div class="col-12 col-lg-7">
      <UserTable :data="props.users" :search="props.search" :edit="edit" :del="del" :statusUpdate="statusUpdate" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, inject, onMounted, onBeforeUnmount } from "vue";
import { router } from '@inertiajs/vue3';
import { user } from "../../composables/user";

import PageBar from '../../components/PageBar.vue';
import Refresh from '../../components/Refresh.vue';
import Button from '../../components/Button.vue';
import UserForm from "./UserForm.vue";
import UserTable from "./UserTable.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  users: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// display single
const formRef = ref(null);
const edit = async (id) => {
  try {
    const { data } = await axios.get(route('user-single', { id }));
    formRef.value?.setForm(data.user); // 👈 directly set the form
  } catch (e) {
    console.error(e);
  }
};

// delete
const del = async (ids) => {
  Confirm('Do you want to delete ?', (ok) => {
    if (!ok) return;
    router.delete(route('user-delete', { id: ids }), {
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

// status update, user is active or inactive
const statusUpdate = async (id, status) => {
  if (status == "1") {
    router.put(route('user-status', id), { id: id, status: "0" }, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(e.props.flash.err);
        } else {
          notify(e.props.flash.msg, 'success');
        }
      }
    });
  } else {
    router.put(route('user-status', id), { id: id, status: "1" }, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(e.props.flash.err);
        } else {
          notify(e.props.flash.msg, 'success');
        }
      }
    });
  }
};

// reload user
const refresh = () => router.get(route('user'), {}, { preserveScroll: true });

onMounted(() => {
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', () => {
    router.reload({ only: ['users'], preserveState: true, preserveScroll: true });
  });
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style lang="scss" scoped></style>