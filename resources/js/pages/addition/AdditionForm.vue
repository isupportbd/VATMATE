<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <SelectInfinite id="costsheet" placeholder="Select..." title="costsheet" v-model="field.costsheet"
              :fetched="fetchedCostsheet" :err="err.costsheet" :disabled="field.id ? true : false" must />
          </div>
          <div class="col-12">
            <InputGroup type="text" category="decimal" label="Rate" placeholder="Ex: 120" placeholder1="Ex: 5%"
              v-model="field.value" :err="err.rateval" @keyup="ratevalue" :calcdata="field.rate"
              :hood="props.form43Current?.unit_price" must />
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
import InputGroup from "../../components/InputGroup.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] },
  form43Current: { type: [Object, Array] },
});

// addition field
const field = reactive({ id: '', form43: '', costsheet: '', rate: '', value: '', isLoading: false });
const err = reactive({ form43: '', rateval: '', costsheet: '' });

// costsheet
const fetchedCostsheet = async () => {
  return {
    url: route('costsheet-dropdown'),
    data: 'costsheet',
    mapFn: (cost) => ({ id: cost.id, title: cost.name })
  };
};

// rate with value
const ratevalue = () => {
  field.rate = field.value ? Number((Number(field.value) / Number(props.form43Current?.unit_price)) * 100)?.toFixed(2) : '';
}

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.form43 = row.form43_id ?? "";
  field.costsheet = !emptyObj(row.costsheet) ? { id: row.costsheet?.id, title: row.costsheet?.name } : "";
  field.rate = row.rate?.toString() ?? "";
  field.value = row.value?.toString() ?? "";
  // clear previous errors when setting a record
  err.form43 = err.rateval = err.costsheet = "";
};

// reset form
const resetForm = () => {
  field.id = field.form43 = field.costsheet = field.rate = field.value = err.form43 = err.rateval = err.costsheet = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    form43_id: props.form43Current?.id,
    costsheet_id: field.costsheet?.id,
    value: field.value
  };

  // console.log(form);

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.form43 = er.form43_id;
    err.rateval = er.value;
    err.costsheet = er.costsheet_id;
  };

  if (field.id) {
    router.put(route('addition-update', field.id), form, {
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
    router.post(route('addition-create'), form, {
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