<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-6">
            <SelectInfinite placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" must />
          </div>
          <div class="col-6">
            <SelectInfinite placeholder="Select..." title="LC/INV" v-model="field.lc_inv" :fetched="fetchedLcInv"
              :err="err.lc_inv" :reset-key="field.bin?.id" must />
          </div>
          <div :class="[field.has_boe ? 'col-8' : 'col-12']">
            <SelectInfinite placeholder="Select..." title="item" v-model="field.item" :fetched="fetchedItem"
              :err="err.item" :reset-key="field.lc_inv?.title" must />
          </div>
          <div class="col-4" :class="{ 'd-none': !field.has_boe }">
            <SelectInfinite placeholder="Select..." title="BoE" v-model="field.boe" :fetched="fetchedBoe" :err="err.boe"
              :reset-key="field.item?.id" must />
          </div>
          <div class="col-4">
            <SelectInfinite placeholder="Select..." title="unit" v-model="field.unit" :fetched="fetchedUnit"
              :err="err.unit" must disabled />
          </div>
          <div class="col-4">
            <Input type="date" id="date" label="date" v-model="field.date" :err="err.date" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="quantity" label="quantity" placeholder="Ex: 89.25"
              v-model="field.quantity" :err="err.quantity" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="value"
              label="value <span class='fs-mini text-danger'>(without vat)</span>" placeholder="Ex: 89.25"
              v-model="field.value" :err="err.value" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="value" label="VAT" placeholder="Ex: 412.25" v-model="field.vat"
              :err="err.vat" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="proposed-value" label="proposed value" placeholder="Ex: 412.25"
              v-model="field.proposed_value" :err="err.proposed_value" must />
          </div>
          <div class="col-6">
            <Input type="text" category="decimal" id="increasing-adjustment-vat" label="increasing adjustment vat"
              placeholder="Ex: 412.25" v-model="field.increasing_adjustment_vat" :err="err.increasing_adjustment_vat"
              must />
          </div>
          <div class="col-6">
            <Input type="text" category="decimal" id="net-decrease" label="net decrease" placeholder="Ex: 412.25"
              v-model="field.net_decrease" :err="err.net_decrease" must />
          </div>
          <div class="col-12">
            <Input type="text" id="reason" label="reason" placeholder="Ex: Test reason" maxlength="150"
              v-model="field.reason_ineligibility" :err="err.reason_ineligibility" must />
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

// creditnote field
const field = reactive({ id: '', bin: '', lc_inv: '', item: '', boe: '', unit: '', date: '', value: '', vat: '', proposed_value: '', increasing_adjustment_vat: '', net_decrease: '', reason_ineligibility: '', has_boe: false, isLoading: false });

const err = reactive({ bin: '', lc_inv: '', item: '', boe: '', unit: '', date: '', value: '', vat: '', proposed_value: '', increasing_adjustment_vat: '', net_decrease: '', reason_ineligibility: '' });

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('form621-client-dropdown'),
    data: 'clientdropdown',
    mapFn: (col) => ({ id: col?.client?.id, title: col?.client?.bin, val: col?.client })
  };
};

// form 6.2.1 dropdown
const fetchedLcInv = async () => {
  return {
    url: route('form621-client-lc-inv-dropdown'),
    data: 'lcinvdropdown',
    params: { client_id: field?.bin?.id, type: 'all' },
    mapFn: (col) => ({ title: col?.lc_or_invoice_no, val: col }),
    option: async (row) => {
      // console.log(row)
      field.has_boe = row?.val?.has_boe;
    }
  };
};

// item dropdown
const fetchedItem = async () => {
  return {
    url: route('form621-client-lc-inv-item-dropdown'),
    data: 'itemdropdown',
    params: { client_id: field.bin?.id, lc_or_invoice_no: field.lc_inv?.title },
    mapFn: (col) => ({ id: col?.item?.id, title: `${col?.item?.name} [${col?.item?.hs_code}]` })
  }
};

// boe dropdown
const fetchedBoe = async () => {
  return {
    url: route('form621-client-lc-item-boe-dropdown'),
    data: 'boedropdown',
    params: { client_id: field.bin?.id, lc_or_invoice_no: field.lc_inv?.title, item_id: field.item?.id },
    mapFn: (col) => ({ id: col?.id, title: col?.boe })
  }
};

// unit dropdown
const fetchedUnit = async () => {
  return {
    url: route('unit-dropdown'),
    data: 'unit',
    mapFn: (unit) => ({ id: unit.id, title: unit.name })
  };
};

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.bin = !empty(row.client) ? { id: row.client?.id, title: row.client?.bin } : "";
  field.item = !empty(row.item) ? { id: row.item?.id, title: row.item?.name } : "";
  field.lc_inv = !empty(row.form621) ? { id: row.form621?.id, title: row.form621?.lc_or_invoice_no } : "";
  field.unit = !empty(row.unit) ? { id: row.unit?.id, title: row.unit?.name } : "";
  field.date = row.date ?? "";
  field.boe = row.boe ?? "";
  field.value = row.value ?? "";
  field.vat = row.vat ?? "";
  field.proposed_value = row.proposed_value ?? "";
  field.increasing_adjustment_vat = row.increasing_adjustment_vat ?? "";
  field.net_decrease = row.net_decrease ?? "";
  field.reason_ineligibility = row.reason_ineligibility ?? "";
  // clear previous errors when setting a record
  err.bin = err.item = err.unit = err.date = err.boe = err.lc_inv = err.value = err.vat = err.proposed_value = err.increasing_adjustment_vat = err.net_decrease = err.reason_ineligibility = "";
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.item = field.unit = field.date = field.boe = field.lc_inv = field.value = field.vat = field.proposed_value = field.increasing_adjustment_vat = field.net_decrease = field.reason_ineligibility = "";

  err.bin = err.item = err.unit = err.date = err.boe = err.lc_inv = err.value = err.vat = err.proposed_value = err.increasing_adjustment_vat = err.net_decrease = err.reason_ineligibility = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    item_id: field.item?.id,
    unit_id: field.unit?.id,
    date: field.date,
    boe: field.boe,
    lc_inv: field.lc_inv?.title,
    value: field.value,
    vat: field.vat,
    proposed_value: field.proposed_value,
    increasing_adjustment_vat: field.increasing_adjustment_vat,
    net_decrease: field.net_decrease,
    reason_ineligibility: field.reason_ineligibility
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.item = er.item_id;
    err.unit = er.unit_id;
    err.date = er.date;
    err.boe = er.boe;
    err.lc_inv = er.lc_inv;
    err.value = er.value;
    err.vat = er.vat;
    err.proposed_value = er.proposed_value;
    err.increasing_adjustment_vat = er.increasing_adjustment_vat;
    err.net_decrease = er.net_decrease;
    err.reason_ineligibility = er.reason_ineligibility;
  };

  if (field.id) {
    router.put(route('form44-update', field.id), form, {
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
    router.post(route('form44-create'), form, {
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