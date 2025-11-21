<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <SelectInfinite id="division" placeholder="Select..." title="Division" v-model="field.division"
              :fetched="fetchedDivision" :err="err.division" must :disabled="field.id ? true : false" />
          </div>
          <div class="col-12">
            <SelectInfinite id="bank" placeholder="Select..." title="Bank" v-model="field.bank" :fetched="fetchedBank"
              :err="err.bank" must :disabled="field.id ? true : false" />
          </div>
          <div class="col-12">
            <Input type="text" id="name" label="Name" placeholder="Ex: Dhaka" maxlength="200" v-model="field.name"
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
const field = reactive({ id: '', name: '', division: '', bank: '', isLoading: false });
const err = reactive({ name: '', division: '', bank: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.name = row.name ?? "";
  field.division = !empty(row.division) ? { id: row.division?.id, title: row.division?.name } : "";
  field.bank = !empty(row.bank) ? { id: row.bank?.id, title: row.bank?.name } : "";
  // clear previous errors when setting a record
  err.name = err.division = err.bank = "";
};

// reset form
const resetForm = () => {
  field.id = field.name = field.division = field.bank = err.name = err.division = err.bank = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// division
const fetchedDivision = async (search, reset = false, reload = false) => {
  return {
    url: route('division-dropdown'),
    data: 'division',
    mapFn: (division) => ({ id: division.id, title: division.name }),
    reset: reset,
    reload: reload
  };
};

// bank
const fetchedBank = async (search, reset = false, reload = false) => {
  return {
    url: route('bank-dropdown'),
    data: 'bank',
    mapFn: (bank) => ({ id: bank.id, title: bank.name }),
    reset: reset,
    reload: reload
  };
};

// create or update branch
const submitData = async () => {
  const form = { id: field.id, name: field.name, division_id: field.division?.id, bank_id: field.bank?.id };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.name = er.name;
    err.division = er.division;
    err.bank = er.bank;
  };

  if (field.id) {
    router.put(route('branch-update', field.id), form, {
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
    router.post(route('branch-create'), form, {
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