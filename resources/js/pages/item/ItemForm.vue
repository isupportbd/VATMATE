<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row g-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <Input type="text" id="item" label="name" maxlength="250" placeholder="Ex: Coal" v-model="field.name"
              :err="err.name" focus must />
          </div>
          <div class="col-12">
            <Input type="text" label="hs code" maxlength="20" placeholder="Ex: 2701.19.00" v-model="field.hs_code"
              :err="err.hs_code" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" label="CD" maxlength="6" placeholder="Ex: 5" v-model="field.cd"
              :err="err.cd" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" label="RD" maxlength="6" placeholder="Ex: 10" v-model="field.rd"
              :err="err.rd" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" label="SD" maxlength="6" placeholder="Ex: 15" v-model="field.sd"
              :err="err.sd" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" label="VAT" maxlength="6" placeholder="Ex: 15" v-model="field.vat"
              :err="err.vat" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" label="AIT" maxlength="6" placeholder="Ex: 15" v-model="field.ait"
              :err="err.ait" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" label="AT" maxlength="6" placeholder="Ex: 15" v-model="field.at"
              :err="err.at" must />
          </div>
        </div>
        <TextArea type="text" label="description" style="height: 112px !important;" maxlength="250"
          placeholder="Ex: Coal" v-model="field.description" />
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

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import TextArea from "../../components/TextArea.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// item field
const field = reactive({ id: '', name: '', hs_code: '', description: '', cd: '', rd: '', sd: '', vat: '', ait: '', at: '', isLoading: false });
const err = reactive({ name: '', hs_code: '', cd: '', rd: '', sd: '', vat: '', ait: '', at: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.name = row.name ?? "";
  field.hs_code = row.hs_code ?? "";
  field.description = row.description ?? "";
  field.cd = row.cd ?? "";
  field.rd = row.rd ?? "";
  field.sd = row.sd ?? "";
  field.vat = row.vat ?? "";
  field.ait = row.ait ?? "";
  field.at = row.at ?? "";
  // clear previous errors when setting a record
  err.name = err.hs_code = err.cd = err.rd = err.sd = err.vat = err.ait = err.at = "";
};

// reset form
const resetForm = () => {
  field.id = field.name = field.hs_code = field.description = field.cd = field.rd = field.sd = field.vat = field.ait = field.at = err.name = err.hs_code = err.cd = err.rd = err.sd = err.vat = err.ait = err.at = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update division
const submitData = async () => {
  const form = { id: field.id, name: field.name, hs_code: field.hs_code, description: field.description, cd: field.cd, rd: field.rd, sd: field.sd, vat: field.vat, ait: field.ait, at: field.at };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.name = er.name;
    err.hs_code = er.hs_code;
    err.cd = er.cd;
    err.rd = er.rd;
    err.sd = er.sd;
    err.vat = er.vat;
    err.ait = er.ait;
    err.at = er.at;
  };

  if (field.id) {
    router.put(route('item-update', field.id), form, {
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
    router.post(route('item-create'), form, {
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