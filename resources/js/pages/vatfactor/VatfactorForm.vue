<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <Input type="text" category="decimal" id="vatfact" label="Vat Fact" placeholder="Ex: 7.50" maxlength="6"
              v-model="field.vatfact" :err="err.vatfact" :disabled="field.id ? false : true" focus must />
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
import { emptyObj } from "../../helpers/utils";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// vat factor field
const field = reactive({ id: '', vatfact: '', isLoading: false });
const err = reactive({ vatfact: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.vatfact = row.vatfact ?? "";
  // clear previous errors when setting a record
  err.vatfact = "";
};

// reset form
const resetForm = () => {
  field.id = field.vatfact = "";
  err.vatfact = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update vat factor
const submitData = async () => {
  const form = { id: field.id, vatfact: field.vatfact };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.vatfact = er.vatfact;
  };

  if (field.id) {
    router.put(route('vatfactor-update', field.id), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        notify(e.props.flash.msg, 'success');
        resetForm();
      },
      onError: onErr,
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  }
};
</script>