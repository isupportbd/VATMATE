<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <Input type="text" id="name_note" label="name_note" placeholder="Ex: atfact" maxlength="20"
              v-model="field.name_note" :err="err.name_note" readonly focus must />
          </div>
          <div class="col-12">
            <Input type="text" category="decimal" id="percent" label="Vat Fact" placeholder="Ex: 4512.5"
              v-model="field.percent" :err="err.percent" must />
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

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// vat factor field
const field = reactive({ id: '', name_note: '', percent: '', isLoading: false });
const err = reactive({ name_note: '', percent: '', });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.name_note = row.name_note_no ?? "";
  field.percent = row.percent ?? "";
  // clear previous errors when setting a record
  err.name_note = err.percent = "";
};

// reset form
const resetForm = () => {
  field.id = field.name_note = field.percent = "";
  err.name_note = err.percent = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update vat factor
const submitData = async () => {
  const form = { id: field.id, name_note_no: field.name_note, percent: field.percent };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.name_note = er.name_note_no;
    err.percent = er.percent;
  };

  if (field.id) {
    router.put(route('mushaknote-update', { id: field.id }), form, {
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