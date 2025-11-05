<template>

  <Head title="User Profile" />
  <PageBar>
    <p class="m-0 fs-4">User Profile</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-5 mb-3 mb-lg-0">
      <div class="card card-body h-100">
        <form @submit.prevent="submitData">
          <div class="row gx-1">
            <div class="col-12">
              <Input type="hidden" v-model="field.id" readonly />
              <Input type="hidden" v-model="field.role_id" readonly />
              <Input type="hidden" v-model="field.email" readonly />
            </div>
            <div class="col-6">
              <Input type="text" id="name" label="name" maxlength="100" placeholder="Ex: Jhon Doe" autocomplete="off"
                v-model="field.name" :err="err.name" must focus />
            </div>
            <div class="col-6">
              <Input type="text" id="mobile" category="mobile" label="mobile" maxlength="14"
                placeholder="Ex: 5648796546" v-model="field.mobile" :err="err.mobile" autocomplete="off" must />
            </div>
            <div class="col-6">
              <Input type="password" id="password" label="password" maxlength="255" placeholder="Ex: ********"
                v-model="field.password" :err="err.password" autocomplete="off" />
            </div>
            <div class="col-6">
              <Input type="password" id="confirmpassword" label="confirm password" maxlength="255"
                placeholder="Ex: ********" v-model="field.confirm_password" :err="err.confirm_password"
                autocomplete="off" />
            </div>
          </div>
          <div class="d-flex align-items-center">
            <Button type="submit" class="btn btn-primary me-1" label="Save" icon="bi bi-save"
              :disabled="field.isLoading" />
            <Button type="button" class="btn btn-secondary ms-1" label="Reset" icon="bi bi-stars" @click="resetForm" />
          </div>
        </form>
      </div>
    </div>
    <div class="col-12 col-lg-7">
      <div class="card h-100">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between py-2">
          <h5 class="m-0">Profile</h5>
          <Button class="btn btn-primary px-3" title="edit" icon="bi bi-pencil-square" @click="edit"
            :disabled="field.isLoading" />
        </div>
        <div class="card-body py-3">
          <div class="row g-2">
            <div class="col-12 col-lg-6">
              <div class="card card-body border">
                <h5 class="m-0 fw-bold">Name</h5>
                <p class="m-0">{{ props.loginuser.name }}</p>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="card card-body border">
                <h5 class="m-0 fw-bold">Email</h5>
                <p class="m-0">{{ props.loginuser.email }}</p>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="card card-body border">
                <h5 class="m-0 fw-bold">Mobile</h5>
                <p class="m-0">{{ props.loginuser.mobile }}</p>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="card card-body border">
                <h5 class="m-0 fw-bold">Status</h5>
                <p class="m-0">{{ props.loginuser.status ? 'Active' : 'Inactive' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, inject } from "vue";
import { router } from "@inertiajs/vue3";
import { user, role } from "../../composables/user";

import PageBar from "../../components/PageBar.vue";
import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import Refresh from "../../components/Refresh.vue";
import Alert from '../../helpers/customAlert';

const props = defineProps({
  loginuser: { type: [Object, Array] }
});

const notify = inject('showToast'); // toast message
const field = reactive({ id: '', role_id: '', email: '', name: '', password: '', confirm_password: '', isLoading: false });
const err = reactive({ name: '', mobile: '', password: '', confirm_password: '' });

const resetForm = () => {
  // Object.keys(field).map((key) => field[key] = '');
  // Object.keys(err).map((key) => err[key] = '');
  field.id = field.email = field.role_id = field.name = field.password = field.confirm_password = field.mobile = err.name = err.mobile = err.password = err.confirm_password = "";
};

const edit = () => {
  field.id = props.loginuser.id;
  field.role_id = props.loginuser.role_id;
  field.email = props.loginuser.email;
  field.name = props.loginuser.name ? props.loginuser.name : "";
  field.mobile = props.loginuser.mobile;
}

const submitData = () => {
  const form = {
    id: user.id,
    role_id: user.role_id,
    email: user.email,
    name: field.name,
    mobile: field.mobile,
    password: field.password,
    confirm_password: field.confirm_password
  };

  router.put(route('user-profile'), form, {
    onSuccess: (e) => {
      if (e.props.flash.err) {
        Alert(e.props.flash.err);
      } else {
        notify(e.props.flash.msg, 'success');
        resetForm();
      }
    },
    onError: (e) => {
      err.name = e.name;
      err.mobile = e.mobile;
      err.password = e.password;
      err.confirm_password = e.confirm_password;
    },
    onStart: () => field.isLoading = true,
    onFinish: () => field.isLoading = false
  });
};

// reload profile
const refresh = () => router.get(route('profile-get'));
</script>