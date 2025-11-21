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
            <Input type="date" id="date" label="date" v-model="field.date" :err="err.date" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="quantity" label="quantity" placeholder="Ex: 89.25"
              v-model="field.quantity" :err="err.quantity" must />
          </div>
          <div class="col-4">
            <SelectInfinite placeholder="Select..." title="unit" v-model="field.unit" :fetched="fetchedUnit"
              :err="err.unit" must disabled />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="proposed-unit-value" label="proposed unit value"
              placeholder="Ex: 412.25" v-model="field.proposed_unit_value" :err="err.proposed_unit_value" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="total-value" label="total value" placeholder="Ex: 89.25"
              v-model="field.total_value" :err="err.total_value" must />
          </div>
          <div class="col-4">
            <Input type="text" category="decimal" id="value" label="VAT" placeholder="Ex: 412.25" v-model="field.vat"
              :err="err.vat" must />
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
import TextArea from "../../components/TextArea.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// creditnote field
const field = reactive({ id: '', bin: '', item: '', unit: '', lc_inv: '', boe: '', date: '', quantity: '', proposed_unit_value: '', total_value: '', vat: '', remarks: '', has_boe: '', isLoading: false });

const err = reactive({ bin: '', item: '', unit: '', lc_inv: '', boe: '', date: '', quantity: '', proposed_unit_value: '', total_value: '', vat: '', remarks: '' });

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
  field.quantity = row.quantity ?? "";
  field.proposed_unit_value = row.proposed_unit_value ?? "";
  field.total_value = row.total_value ?? "";
  field.vat = row.vat ?? "";
  field.remarks = row.remarks ?? "";
  // clear previous errors when setting a record
  err.bin = err.item = err.lc_inv = err.unit = err.date = err.quantity = err.proposed_unit_value = err.total_value = err.vat = err.remarks = "";
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.item = field.unit = field.lc_inv = field.date = field.quantity = field.proposed_unit_value = field.total_value = field.vat = field.remarks = "";

  err.bin = err.item = err.lc_inv = err.unit = err.date = err.quantity = err.proposed_unit_value = err.total_value = err.vat = err.remarks = "";
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
    lc_inv: field.lc_inv?.title,
    date: field.date,
    quantity: field.quantity,
    proposed_unit_value: field.proposed_unit_value,
    total_value: field.total_value,
    vat: field.vat,
    remarks: field.remarks
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.item = er.item_id;
    err.lc_inv = er.lc_inv;
    err.unit = er.unit_id;
    err.date = er.date;
    err.quantity = er.quantity;
    err.proposed_unit_value = er.proposed_unit_value;
    err.total_value = er.total_value;
    err.vat = er.vat;
    err.remarks = er.remarks;
  };

  if (field.id) {
    router.put(route('form46-update', field.id), form, {
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
    router.post(route('form46-create'), form, {
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