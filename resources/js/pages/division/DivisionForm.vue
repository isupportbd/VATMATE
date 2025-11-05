<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <Input type="text" id="name" label="Name" placeholder="Ex: Dhaka" maxlength="30" v-model="field.name"
              :err="err.name" focus must />
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

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// division field
const field = reactive({ id: '', name: '', isLoading: false });
const err = reactive({ name: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.name = row.name ?? "";
  // clear previous errors when setting a record
  err.name = "";
};

// reset form
const resetForm = () => {
  field.id = field.name = err.name = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update division
const submitData = async () => {
  const form = { id: field.id, name: field.name };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.name = er.name;
  };

  if (field.id) {
    router.put(route('division-update', field.id), form, {
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
    router.post(route('division-create'), form, {
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