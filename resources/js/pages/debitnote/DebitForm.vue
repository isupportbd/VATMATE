<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-6">
            <SelectInfinite placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" must />
          </div>
          <div class="col-12 col-sm-6 col-md-4 col-lg-6">
            <SelectInfinite placeholder="Select..." title="LC" v-model="field.lc" :fetched="fetchedLC" :err="err.lc"
              :reset-key="field.bin?.id" must />
          </div>
          <div class="col-12 col-sm-7 col-md-4 col-lg-7">
            <SelectInfinite placeholder="Select..." title="item" v-model="field.item" :fetched="fetchedItem"
              :err="err.item" :reset-key="field.lc?.title" must />
          </div>
          <div class="col-6 col-sm-5 col-md-4 col-lg-5">
            <SelectInfinite placeholder="Select..." title="BoE" v-model="field.boe" :fetched="fetchedBoe" :err="err.boe"
              :reset-key="field.item?.id" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="challan-number" id="challan-no" label="Challan No"
              placeholder="Ex: 5214-98547584515" maxlength="16" v-model="field.vat_challan_no" :err="err.vat_challan_no"
              must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="date" id="issued-date" label="issued date" v-model="field.issued_date" :err="err.issued_date"
              must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="date" id="challan-date" label="challan date" v-model="field.vat_challan_date"
              :err="err.vat_challan_date" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="value" label="value" placeholder="Ex: 152.25"
              v-model="field.value_in_challan" :err="err.value_in_challan" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="quantity" label="quantity" placeholder="Ex: 89.25"
              v-model="field.quantity_in_challan" :err="err.quantity_in_challan" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <SelectInfinite ref="unitDD" placeholder="Select..." title="Unit" v-model="field.unit"
              :fetched="fetchedUnit" :err="err.unit" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="vat" label="VAT" placeholder="Ex: 1598.87"
              v-model="field.vat_in_challan" :err="err.vat_in_challan" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="sd" label="SD" placeholder="Ex: 520.74"
              v-model="field.sd_in_challan" :err="err.sd_in_challan" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="increase-value" label="increase value" placeholder="Ex: 554.21"
              v-model="field.value_of_increasing_adjustment" :err="err.value_of_increasing_adjustment" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="increase-quantity" label="increase quantity"
              placeholder="Ex: 214.25" v-model="field.quantity_of_increasing_adjustment"
              :err="err.quantity_of_increasing_adjustment" must />
          </div>
          <div class="col-6 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="increase-vat" label="increase vat" placeholder="Ex: 354.25"
              v-model="field.vat_of_increasing_adjustment" :err="err.vat_of_increasing_adjustment" must />
          </div>
          <div class="col-12 col-sm-4 col-md-4">
            <Input type="text" category="decimal" id="increase-sd" label="increase SD" placeholder="Ex: 654.14"
              v-model="field.sd_of_increasing_adjustment" :err="err.sd_of_increasing_adjustment" must />
          </div>
          <div class="col-12 col-md-8 col-lg-12">
            <Input type="text" id="reason" label="reason" placeholder="Ex: Test reason" maxlength="150"
              v-model="field.reason_for_issuance" :err="err.reason_for_issuance" must />
          </div>
          <div class="col-12">
            <TextArea type="text" label="remarks" maxlength="250" style="height: 112px !important;"
              placeholder="Ex: Remarks" v-model="field.remarks" :err="err.remarks" />
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
import TextArea from '../../components/TextArea.vue';

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// creditnote field
const field = reactive({ id: '', lc: '', bin: '', item: '', issued_date: '', vat_challan_no: '', vat_challan_date: '', reason_for_issuance: '', value_in_challan: '', quantity_in_challan: '', unit: '', vat_in_challan: '', sd_in_challan: '', value_of_increasing_adjustment: '', quantity_of_increasing_adjustment: '', vat_of_increasing_adjustment: '', sd_of_increasing_adjustment: '', remarks: '', isLoading: false });

const err = reactive({ lc: '', bin: '', item: '', issued_date: '', vat_challan_no: '', vat_challan_date: '', reason_for_issuance: '', value_in_challan: '', quantity_in_challan: '', unit: '', vat_in_challan: '', sd_in_challan: '', value_of_increasing_adjustment: '', quantity_of_increasing_adjustment: '', vat_of_increasing_adjustment: '', sd_of_increasing_adjustment: '', remarks: '' });

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('form621-client-dropdown'),
    data: 'clientdropdown',
    mapFn: (col) => ({ id: col?.client?.id, title: col?.client?.bin, val: col?.client })
  };
};

// form 6.2.1 dropdown
const fetchedLC = async () => {
  return {
    url: route('form621-client-lc-inv-dropdown'),
    data: 'lcinvdropdown',
    params: { client_id: field?.bin?.id, type: 'lc' },
    mapFn: (col) => ({ title: col?.lc_or_invoice_no }),
  };
};

// item dropdown
const fetchedItem = async () => {
  return {
    url: route('form621-client-lc-inv-item-dropdown'),
    data: 'itemdropdown',
    params: { client_id: field.bin?.id, lc_or_invoice_no: field.lc?.title },
    mapFn: (col) => ({ id: col?.item?.id, title: `${col?.item?.name} [${col?.item?.hs_code}]` })
  }
};

// boe dropdown
const fetchedBoe = async () => {
  return {
    url: route('form621-client-lc-item-boe-dropdown'),
    data: 'boedropdown',
    params: { client_id: field.bin?.id, lc_or_invoice_no: field.lc?.title, item_id: field.item?.id },
    mapFn: (col) => ({ title: col?.boe })
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
  field.lc = !empty(row.form621) ? { title: row.form621?.lc_or_invoice_no } : "";
  field.bin = !empty(row.client) ? { id: row.client?.id, title: row.client?.bin } : "";
  field.item = !empty(row.item) ? { id: row.item?.id, title: row.item?.name } : "";
  field.unit = !empty(row.unit) ? { id: row.unit?.id, title: row.unit?.name } : "";
  field.issued_date = row.issued_date ?? "";
  field.vat_challan_no = row.vat_challan_no ?? "";
  field.vat_challan_date = row.vat_challan_date ?? "";
  field.reason_for_issuance = row.reason_for_issuance ?? "";
  field.value_in_challan = row.value_in_challan ?? "";
  field.quantity_in_challan = row.quantity_in_challan ?? "";
  field.vat_in_challan = row.vat_in_challan ?? "";
  field.sd_in_challan = row.sd_in_challan ?? "";
  field.value_of_increasing_adjustment = row.value_of_increasing_adjustment ?? "";
  field.quantity_of_increasing_adjustment = row.quantity_of_increasing_adjustment ?? "";
  field.vat_of_increasing_adjustment = row.vat_of_increasing_adjustment ?? "";
  field.sd_of_increasing_adjustment = row.sd_of_increasing_adjustment ?? "";
  field.remarks = row.remarks ?? "";
  // clear previous errors when setting a record
  err.lc = err.bin = err.item = err.unit = err.issued_date = err.vat_challan_no = err.vat_challan_date = err.reason_for_issuance = err.value_in_challan = err.quantity_in_challan = err.vat_in_challan = err.sd_in_challan = err.value_of_increasing_adjustment = err.quantity_of_increasing_adjustment = err.vat_of_increasing_adjustment = err.sd_of_increasing_adjustment = err.remarks = "";
};

// reset form
const resetForm = () => {
  field.id = field.lc = field.bin = field.item = field.unit = field.issued_date = field.vat_challan_no = field.vat_challan_date = field.reason_for_issuance = field.value_in_challan = field.quantity_in_challan = field.vat_in_challan = field.sd_in_challan = field.value_of_increasing_adjustment = field.quantity_of_increasing_adjustment = field.vat_of_increasing_adjustment = field.sd_of_increasing_adjustment = field.remarks = "";

  err.lc = err.bin = err.item = err.unit = err.issued_date = err.vat_challan_no = err.vat_challan_date = err.reason_for_issuance = err.value_in_challan = err.quantity_in_challan = err.vat_in_challan = err.sd_in_challan = err.value_of_increasing_adjustment = err.quantity_of_increasing_adjustment = err.vat_of_increasing_adjustment = err.sd_of_increasing_adjustment = err.remarks = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    lc_or_invoice_no: field.lc?.title,
    client_id: field.bin?.id,
    item_id: field.item?.id,
    unit_id: field.unit?.id,
    issued_date: field.issued_date,
    vat_challan_no: field.vat_challan_no,
    vat_challan_date: field.vat_challan_date,
    reason_for_issuance: field.reason_for_issuance,
    value_in_challan: field.value_in_challan,
    quantity_in_challan: field.quantity_in_challan,
    vat_in_challan: field.vat_in_challan,
    sd_in_challan: field.sd_in_challan,
    value_of_increasing_adjustment: field.value_of_increasing_adjustment,
    quantity_of_increasing_adjustment: field.quantity_of_increasing_adjustment,
    vat_of_increasing_adjustment: field.vat_of_increasing_adjustment,
    sd_of_increasing_adjustment: field.sd_of_increasing_adjustment,
    remarks: field.remarks
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.lc = er.lc_or_invoice_no;
    err.bin = er.client_id;
    err.item = er.item_id;
    err.unit = er.unit_id;
    err.issued_date = er.issued_date;
    err.vat_challan_no = er.vat_challan_no;
    err.vat_challan_date = er.vat_challan_date;
    err.reason_for_issuance = er.reason_for_issuance;
    err.value_in_challan = er.value_in_challan;
    err.quantity_in_challan = er.quantity_in_challan;
    err.vat_in_challan = er.vat_in_challan;
    err.sd_in_challan = er.sd_in_challan;
    err.value_of_increasing_adjustment = er.value_of_increasing_adjustment;
    err.quantity_of_increasing_adjustment = er.value_of_increasing_adjustment;
    err.vat_of_increasing_adjustment = er.vat_of_increasing_adjustment;
    err.sd_of_increasing_adjustment = er.sd_of_increasing_adjustment;
    err.remarks = er.remarks;
  };

  if (field.id) {
    router.put(route('debitnote-update', field.id), form, {
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
    router.post(route('debitnote-create'), form, {
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