<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" topclass="mb-0" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <Checkbox label="Release Client" id="release" v-model="field.release_client" />
            <div class="col-12 mt-1" v-if="field.release_client">
              <SelectInfinite ref="releaseDD" parentclass="m-0" placeholder="Select..." v-model="field.client"
                :fetched="fetchedClient" />
            </div>
          </div>
          <div :class="`${role('admin') ? 'col-12 d-block' : 'd-none'}`">
            <SelectInfinite id="user" ref="userDD" placeholder="Select..." title="Manager" v-model="field.user"
              :fetched="fetchedUser" :err="err.user" />
          </div>
          <div class="col-6">
            <Input type="text" category="bin" id="bin" label="BIN" maxlength="14" placeholder="Ex: 004101788-0701"
              v-model="field.bin" :err="err.bin" focus must :disabled="field.id ? true : false" />
          </div>
          <div class="col-6">
            <SelectInfinite id="division" ref="divisionDD" placeholder="Select..." title="Division"
              v-model="field.division" :fetched="fetchedDivision" :err="err.division" must />
          </div>
          <div class="col-12">
            <Input type="text" id="company" label="company" maxlength="200" placeholder="Ex: M/S SHAKIL ENTERPRISE"
              v-model="field.company_name" :err="err.company_name" must />
          </div>
          <div class="col-12">
            <TextArea id="address" type="text" label="address" style="height: 112px !important;" maxlength="150"
              placeholder="Ex: Assampara, Jaintapur PS, Sylhet" v-model="field.address" />
          </div>
          <div class="col-6">
            <Input id="email" type="text" label="email" maxlength="200" placeholder="Ex: jhondoe@gmail.com"
              v-model="field.email" :err="err.email" must :readonly="field.id ? true : false" />
          </div>
          <div class="col-6">
            <Input id="mobile" type="text" label="mobile" maxlength="14" placeholder="Ex: +8801711000000"
              v-model="field.mobile" :err="err.mobile" must />
          </div>
          <div class="col-6">
            <Input id="vat_username" type="text" label="VAT Username" maxlength="30" placeholder="Ex: 01709936097"
              v-model="field.vat_username" :err="err.vat_username" must />
          </div>
          <div class="col-6">
            <Input id="vat_password" type="text" label="VAT Password" maxlength="30" placeholder="Ex: 9UEUGP@K1"
              v-model="field.vat_password" :err="err.vat_password" must />
          </div>
          <div class="col-6">
            <Input id="owner" type="text" label="owner name" maxlength="100" placeholder="Ex: Jhon Doe"
              v-model="field.name" :err="err.name" />
          </div>
          <div class="col-6">
            <Input id="reference" type="text" label="reference" maxlength="100" placeholder="Ex: Jane Doe"
              v-model="field.reference" :err="err.reference" />
          </div>
          <div class="col-6">
            <Input id="tin" type="text" category="number" label="TIN" maxlength="20" placeholder="Ex: 222555888"
              v-model="field.tin" :err="err.tin" />
          </div>
          <div class="col-6">
            <Input id="circle" type="text" label="Circle" maxlength="50" placeholder="Ex: Circle 1"
              v-model="field.circle" :err="err.circle" />
          </div>
        </div>
        <div class="d-flex align-items-center">
          <Button type="submit" class="btn btn-primary me-1" label="Save" icon="bi bi-save" :disabled="field.isLoading" />
          <Button type="button" class="btn btn-secondary ms-1" label="Reset" icon="bi bi-stars" @click="resetForm" />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, reactive, inject, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { user, role } from '../../composables/user';
import { empty } from "../../helpers/utils";

import Input from "../../components/Input.vue";
import TextArea from "../../components/TextArea.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";
import Alert from '../../helpers/customAlert';
import Checkbox from '../../components/Checkbox.vue';

const notify = inject('showToast'); // toast message

// client field
const field = reactive({ id: '', client: '', user: '', company_name: '', division: '', email: '', mobile: '', bin: '', vat_username: '', vat_password: '', address: '', name: '', reference: '', tin: '', circle: '', release_client: false, isLoading: false });
const err = reactive({ user: '', company_name: '', division: '', email: '', mobile: '', bin: '', vat_username: '', vat_password: '' });

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// bin dropdown
const fetchedClient = async () => {
  return {
    url: route('release-client-dropdown'),
    data: 'client',
    mapFn: (rbin) => ({ id: rbin.id, title: rbin.bin, val: rbin }),
    option: async () => await releaseClientSet()
  };
};

const releaseClientSet = async () => {
  if (!field.client?.id) {
    resetForm();
    return;
  }

  const { data } = await axios.get(route('client-single', { id: field.client?.id }));
  field.id = field.client?.id ?? "";
  field.bin = data.client?.bin ?? "";
  field.division = !empty(data.client.division) ? { id: data.client.division?.id, title: data.client.division?.name } : "";
  field.company_name = data.client?.company_name ?? "";
  field.address = data.client?.address ?? "";
  field.email = data.client?.email ?? "";
  field.mobile = data.client?.mobile ?? "";
  field.vat_username = data.client?.vat_username ?? "";
  field.vat_password = data.client?.vat_password ?? "";
  field.name = data.client?.name ?? "";
  field.reference = data.client?.reference ?? "";
  field.tin = data.client?.tin ?? "";
  field.circle = data.client?.circle ?? "";

  // console.log(data);
};

// user infinite scroll dropdown
const fetchedUser = async () => {
  return {
    url: route('user-dropdown'),
    data: 'user',
    mapFn: (user) => ({ id: user.id, title: user.email })
  };
};

// division
const fetchedDivision = async () => {
  return {
    url: route('division-dropdown'),
    data: 'division',
    mapFn: (division) => ({ id: division.id, title: division.name })
  };
};

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.user = !empty(row.user) ? { id: row.user?.id, title: row.user?.email } : "";
  field.division = !empty(row.division) ? { id: row.division?.id, title: row.division?.name } : "";
  field.company_name = row.company_name ?? "";
  field.email = row.email ?? "";
  field.mobile = row.mobile ?? "";
  field.bin = row.bin ?? "";
  field.vat_username = row.vat_username ?? "";
  field.vat_password = row.vat_password ?? "";
  field.address = row.address ?? "";
  field.name = row.name ?? "";
  field.reference = row.reference ?? "";
  field.tin = row.tin ?? "";
  field.circle = row.circle ?? "";
  // clear previous errors when setting a record
  err.company_name = err.division = err.user = err.email = err.mobile = err.bin = err.vat_username = err.vat_password = err.address = err.name = err.reference = err.tin = err.circle = "";
};

// reset form
const resetForm = async () => {
  field.id = field.company_name = field.division = field.email = field.mobile = field.bin = field.vat_username = field.vat_password = field.address = field.name = field.reference = field.tin = field.circle = field.client = "";
  err.company_name = err.division = err.email = err.mobile = err.bin = err.vat_username = err.vat_password = "";
  field.user = err.user = "";
  field.release_client = false;
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update client
const submitData = async () => {
  const form = {
    id: field.id,
    user_id: role('manager') ? user?.id : (role('admin') && field.user) ? field.user?.id : "",
    company_name: field.company_name,
    division_id: field.division?.id,
    email: field.email,
    mobile: field.mobile,
    bin: field.bin,
    vat_username: field.vat_username,
    vat_password: field.vat_password,
    address: field.address,
    name: field.name,
    reference: field.reference,
    tin: field.tin,
    circle: field.circle
  };

  // console.log(form);

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.company_name = er.company_name;
    err.division = er.division_id;
    err.email = er.email;
    err.mobile = er.mobile;
    err.bin = er.bin;
    err.vat_username = er.vat_username;
    err.vat_password = er.vat_password;
  };

  if (field.id) {
    router.put(route('client-update', { id: form.id }), form, {
      preserveScroll: true,
      onSuccess: async (e) => {
        notify(e.props.flash.msg, 'success');
        resetForm();
      },
      onError: onErr,
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  } else {
    delete form['id'];
    router.post(route('client-create'), form, {
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

// const divisionDD = ref(null);
// const releaseDD = ref(null);
// const userDD = ref(null);
// onMounted(() => {
//   const reload = async () => {
//     const jobs = [];

//     if (userDD.value?.reload) {
//       jobs.push(userDD.value.reload());
//     }

//     if (releaseDD.value?.reload) {
//       jobs.push(releaseDD.value.reload());
//     }

//     if (divisionDD.value?.reload) {
//       jobs.push(divisionDD.value.reload());
//     }

//     // run all reloads in parallel
//     await Promise.all(jobs);
//   };

//   window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
// });

// onBeforeUnmount(() => {
//   window.Echo.leave(`App.Models.User.${user.id}`);
// });
</script>