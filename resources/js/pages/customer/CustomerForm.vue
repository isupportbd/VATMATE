<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12 col-sm-7 col-md-6 col-lg-12">
            <SelectInfinite id="bin" placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" must />
          </div>
          <div class="col-12 col-sm-5 col-md-6 col-lg-12">
            <Input type="text" id="customer-name" label="name" placeholder="Ex: Jhon Doe" maxlength="100"
              v-model="field.name" :err="err.name" must />
          </div>
          <div class="col-6 col-md-3 col-lg-6">
            <Input type="text" category="mobile" id="mobile" label="mobile" placeholder="Ex: 01711000000" maxlength="11"
              v-model="field.mobile" :err="err.mobile" must />
          </div>
          <div class="col-6 col-md-3 col-lg-6">
            <Input type="text" id="bin-or-nid" label="BIN/NID" placeholder="Ex: 52142354587413657" maxlength="17"
              v-model="field.bin_or_nid" :err="err.bin_or_nid" />
          </div>
          <div class="col-12 col-md-6 col-lg-12">
            <Input type="text" id="address" label="address" placeholder="Ex: Tamabil, Sylhet" maxlength="150"
              v-model="field.address" :err="err.address" />
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
import { ref, reactive, inject } from "vue";
import { router } from "@inertiajs/vue3";
import { empty } from "../../helpers/utils";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// branch field
const field = reactive({ id: '', bin: '', name: '', mobile: '', bin_or_nid: '', address: '', isLoading: false });
const err = reactive({ bin: '', name: '', mobile: '', bin_or_nid: '', address: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.bin = !empty(row.client) ? { id: row.client?.id, title: row.client?.bin } : "";
  field.name = row.customer_name ?? "";
  field.mobile = row.mobile ?? "";
  field.bin_or_nid = row.bin_or_nid ?? "";
  field.address = row.address ?? "";
  // clear previous errors when setting a record
  err.bin = err.name = err.mobile = err.bin_or_nid = err.address = "";
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.name = field.mobile = field.bin_or_nid = field.address = err.bin = err.name = err.mobile = err.bin_or_nid = err.address = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// bin
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin })
  };
};

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    customer_name: field.name,
    mobile: field.mobile,
    bin_or_nid: field.bin_or_nid,
    address: field.address
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.name = er.customer_name;
    err.mobile = er.mobile;
    err.bin_or_nid = er.bin_or_nid;
    err.address = er.address;
  };

  if (field.id) {
    router.put(route('customer-update', field.id), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        notify(e.props.flash.msg, 'success');
        resetForm();
      },
      onError: onErr,
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  } else {
    delete form['id'];
    router.post(route('customer-create'), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        notify(e.props.flash.msg, 'primary');
        resetForm();
      },
      onError: onErr,
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  }
};
</script>