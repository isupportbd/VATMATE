<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <Input type="text" id="name" label="name" placeholder="Ex: atfact" maxlength="20" v-model="field.name"
              :err="err.name" readonly focus must />
          </div>
          <div class="col-12">
            <Input type="text" category="decimal" id="value" label="Vat Fact" placeholder="Ex: 4512.5"
              v-model="field.value" :err="err.value" must />
          </div>
          <div class="col-12">
            <TextArea type="text" id="description" label="description" maxlength="150" style="height: 112px !important;"
              placeholder="Ex: Description..." v-model="field.description" :err="err.description" must="" />
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
import TextArea from "../../components/TextArea.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// vat factor field
const field = reactive({ id: '', name: '', value: '', description: '', isLoading: false });
const err = reactive({ name: '', value: '', description: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.name = row.name ?? "";
  field.value = row.value ?? "";
  field.description = row.description ?? "";
  // clear previous errors when setting a record
  err.name = err.value = err.description = "";
};

// reset form
const resetForm = () => {
  field.id = field.name = field.value = field.description = "";
  err.name = err.value = field.description = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update vat factor
const submitData = async () => {
  const form = { id: field.id, name: field.name, value: field.value, description: field.description };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.name = er.name;
    err.value = er.value;
    err.description = er.description;
  };

  if (field.id) {
    router.put(route('vatfactor-update', { id: field.id }), form, {
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