<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <Input type="hidden" v-model="field.id" readonly />
        <div class="row gx-1">
          <div class="col-6">
            <Input type="text" id="name" label="Name" placeholder="Ex: Jhon Doe" maxlength="100" v-model="field.name"
              focus />
          </div>
          <div class="col-6">
            <Input type="text" id="email" label="Email" placeholder="Ex: jhondoe@gmail.com" maxlength="255"
              v-model="field.email" :err="err.email" must :disabled="field.id ? true : false" />
          </div>
          <div class="col-6">
            <SelectInfinite id="role" placeholder="Select..." title="role" v-model="field.role" :fetched="fetchedRole"
              :err="err.role" must />
          </div>
          <div class="col-6">
            <Input type="text" id="mobile" label="Mobile" placeholder="Ex: +8801711000000" maxlength="14"
              v-model="field.mobile" :err="err.mobile" must />
          </div>
          <div class="col-6">
            <Input type="password" id="password" label="Password" placeholder="Ex: ********" maxlength="255"
              v-model="field.password" :err="err.password" :must="field.id ? false : true" />
          </div>
          <div class="col-6">
            <Input type="password" id="confirmpassword" label="Confirm Password" placeholder="Ex: ********"
              maxlength="255" v-model="field.confirm_password" :err="err.confirm_password"
              :must="field.id ? false : true" />
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
</template>

<script setup>
import axios from "axios";
import { ref, reactive, inject } from "vue";
import { router } from "@inertiajs/vue3";
import { emptyObj } from "../../helpers/utils";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";
import Alert from '../../helpers/customAlert';

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] },
  modify: { type: Object, default: {} },
  isEdited: { type: Boolean },
  isDeleted: { type: Boolean },
});

// user field
const field = reactive({ id: '', name: '', email: '', role: '', mobile: '', password: '', confirm_password: '', isLoading: false });
const err = reactive({ email: '', role: '', mobile: '', password: '', confirm_password: '', });

// role
const fetchedRole = async () => {
  return {
    url: route('role-dropdown'),
    data: 'role',
    mapFn: (role) => ({ id: role.id, title: role.name })
  };
};

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.name = row.name ?? "";
  field.email = row.email ?? "";
  field.mobile = row.mobile ?? "";
  field.role = !emptyObj(row.role) ? { id: row.role?.id, title: row.role?.name } : "";
  // clear previous errors when setting a record
  err.name = err.email = err.mobile = err.password = err.confirm_password = err.role = "";
};

// reset form
const resetForm = () => {
  field.id = field.name = err.name = field.email = err.email = field.mobile = err.mobile = field.password = err.password = field.confirm_password = err.confirm_password = field.role = err.role = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update user
const submitData = async () => {
  const form = {
    id: field.id,
    role_id: field.role?.id,
    name: field.name,
    email: field.email,
    mobile: field.mobile,
    password: field.password,
    confirm_password: field.confirm_password
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.role = er.role_id;
    err.email = er.email;
    err.password = er.password;
    err.mobile = er.mobile;
    err.confirm_password = er.confirm_password;
  };

  if (field.id) {
    router.put(route('user-update', field.id), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(e.props.flash.err);
        } else {
          notify(e.props.flash.msg, 'success');
          resetForm();
        }
      },
      onError: onErr,
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  } else {
    delete form['id'];
    router.post(route('user-create'), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(e.props.flash.err);
        } else {
          notify(e.props.flash.msg, 'primary');
          resetForm();
        }
      },
      onError: onErr,
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  }
};
</script>