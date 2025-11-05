<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-6">
            <Input type="text" category="decimal" id="amount" label="amount" placeholder="Ex: 2541"
              v-model="field.amount" :err="err.amount" focus must />
          </div>
          <div class="col-6">
            <Input type="month" id="tax-period" category="tax-period" label="tax period" placeholder="Ex: 2024-07"
              maxlength="7" v-model="field.tax_period" :err="err.tax_period" must />
          </div>
          <div class="col-12">
            <SelectInfinite placeholder="Select..." title="BIN" label="title" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" must />
          </div>
          <div class="col-12">
            <SelectInfinite id="expense" placeholder="Select..." title="other adjustment" label="title"
              v-model="field.expense" :fetched="fetchedExpense" :err="err.expense" must />
          </div>
          <div class="col-12">
            <TextArea type="text" label="remarks" maxlength="250" style="height: 112px !important;"
              placeholder="Ex: Remarks" v-model="field.remarks" />
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
import SelectInfinite from "../../components/SelectInfinite.vue";
import TextArea from '../../components/TextArea.vue';

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// adjustment field
const field = reactive({ id: '', bin: '', expense: '', tax_period: '', amount: '', remarks: '', isLoading: false });
const err = reactive({ bin: '', expense: '', tax_period: '', amount: '' });

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin })
  };
};

// expense
const fetchedExpense = async () => {
  return {
    url: route('expense-dropdown'),
    data: 'expense',
    mapFn: (exp) => ({ id: exp.id, title: exp.name })
  };
};

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.bin = !emptyObj(row.client) ? { id: row.client?.id, title: row.client?.bin } : "";
  field.expense = !emptyObj(row.expense) ? { id: row.expense?.id, title: row.expense?.name } : "";
  field.tax_period = row.tax_period ?? "";
  field.amount = row.amount ?? "";
  field.remarks = row.remarks ?? "";
  // clear previous errors when setting a record
  err.bin = err.expense = err.tax_period = err.amount = "";
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.expense = field.tax_period = field.amount = field.remarks = err.bin = err.expense = err.tax_period = err.amount = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    expense_id: field.expense?.id,
    tax_period: field.tax_period,
    amount: field.amount,
    remarks: field.remarks
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.expense = er.expense_id;
    err.tax_period = er.tax_period;
    err.amount = er.amount;
  };

  if (field.id) {
    router.put(route('adjustment-update', field.id), form, {
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
    router.post(route('adjustment-create'), form, {
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