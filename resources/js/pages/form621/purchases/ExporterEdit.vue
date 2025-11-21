<template>
  <div class="row gx-2">
    <div class="col-12">
      <div class="card card-body border h-100">
        <form @submit.prevent="submitData">
          <div class="row gx-1">
            <div class="col-12">
              <Input type="hidden" v-model="field.id" readonly />
            </div>
            <div class="col-12">
              <Input type="text" label="exporter name" maxlength="100" placeholder="Ex: Jhon Doe"
                v-model="field.exporter_name" :err="err.exporter_name" />
            </div>
            <div class="col-12">
              <Input type="text" label="address" maxlength="100" placeholder="Ex: China" v-model="field.address"
                :err="err.address" />
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
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { inject, reactive } from 'vue';
import Input from '../../../components/Input.vue';
import Button from '../../../components/Button.vue';

const props = defineProps({
  modal: { type: Object }
}); // props pass from Local621IndividualTable component

const notify = inject('showToast');

const field = reactive({ id: '', exporter_name: '', address: '', isLoading: false });
const err = reactive({ exporter_name: '', address: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.exporter_name = row.exporter_name ?? "";
  field.address = row.address ?? "";

  // clear previous errors when setting a record
  err.exporter_name = err.address = "";
};

// reset form
const resetForm = () => {
  field.exporter_name = field.address = "";
  err.exporter_name = err.address = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

const submitData = async () => {
  const form = {
    id: field.id,
    exporter_name: field.exporter_name,
    address: field.address,
  };

  // console.log(dt);
  if (field.id) {
    router.put(route('purchase-exporter-update', field.id), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        notify(e.props.flash.msg, 'success');
        resetForm();
        props.modal.close();
      },
      onError: (errors) => {
        let er = {};
        Object.keys(errors).map((key) => er[key] = errors[key].toString());
        err.exporter_name = er.exporter_name;
        err.address = er.address;
      },
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  }
};
</script>

<style lang="scss" scoped></style>