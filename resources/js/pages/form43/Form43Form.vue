<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12 col-sm-5">
            <Input type="text" id="lc_no" label="Lc No" maxlength="50" placeholder="Ex: 00003764658574"
              v-model="field.lc_no" :err="err.lc_no" focus must />
          </div>
          <div class="col-12 col-sm-7">
            <SelectInfinite placeholder="Select..." title="BIN" label="title" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" must :disabled="field.id ? true : false" />
          </div>
          <div class="col-12">
            <SelectInfinite placeholder="Select..." title="Item" label="title" v-model="field.item"
              :fetched="fetchedItem" :err="err.item" :disabled="field.id ? true : false" must />
          </div>
          <div class="col-12 col-sm-4">
            <SelectInfinite placeholder="Select..." title="Purchase Unit" label="title" v-model="field.purchase_unit"
              :fetched="fetchedPUnit" :err="err.purchase_unit" :disabled="last.lastobj || (field.id ? true : false)"
              must />
          </div>
          <div class="col-12 col-sm-4">
            <SelectInfinite placeholder="Select..." title="Sale Unit" label="title" v-model="field.sale_unit"
              :fetched="fetchedSUnit" :err="err.sale_unit" :disabled="last.lastobj || (field.id ? true : false)" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" category="decimal" label="factor" placeholder="Ex: 22" v-model="field.factor"
              :err="err.factor" :readonly="last.last_fctor || !empty(field.id)" @keyup="netQtyCalc" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" label="Item Code" placeholder="Ex: 001" v-model="field.item_code" :err="err.item_code"
              :hood="last.last_item_code" :readonly="field.id ? true : false" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="date" label="submission date" placeholder="Ex: 2023-01-01" v-model="field.submission_date"
              :err="err.submission_date" readonly must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="date" label="first delivery date" placeholder="Ex: 2023-01-01"
              v-model="field.first_delivery_date" :err="err.first_delivery_date" />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="date" label="BoE date" placeholder="Ex: 2023-01-01" v-model="field.boe_date"
              :err="err.boe_date" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" id="boe_no" label="BoE No" placeholder="Ex: 5214" v-model="field.boe_no"
              :err="err.boe_no" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" category="decimal" id="quantity" label="quantity" placeholder="Ex: 50"
              v-model="field.quantity" :err="err.quantity" @keyup="netQtyCalc" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" category="decimal" label="excess quantity" placeholder="Ex: 254.25"
              v-model="field.excess_quantity" :err="err.excess_quantity" @keyup="netQtyCalc" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" category="decimal" label="purchase total" placeholder="Ex: 25"
              v-model="field.purchase_unit_total" :err="err.purchase_unit_total" must readonly />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" category="decimal" id="wastage" label="wastage(%)" placeholder="Ex: 3.5"
              v-model="field.wastage" :err="err.wastage" @keyup="netQtyCalc" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" category="decimal" label="stock quantity" placeholder="Ex: 255"
              v-model="field.sale_unit_total" :err="err.sale_unit_total" must readonly />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" id="base_value" category="decimal" label="base value" placeholder="Ex: 18965.874"
              v-model="field.base_value" :err="err.base_value" @keyup="unitPrice" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" category="decimal" label="unit price" placeholder="Ex: 15.620" v-model="field.unit_price"
              :err="err.unit_price" must readonly />
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
import axios from "axios";
import moment from "moment-timezone";
import { ref, reactive, inject } from "vue";
import { router } from "@inertiajs/vue3";
import { empty, emptyObj } from "../../helpers/utils";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// form 4.3 field
const last = reactive({ lastobj: null, last_item_code: '', last_fctor: '', last_purchase_unit: '', last_sale_unit: '' });
const field = reactive({ id: '', lc_no: '', bin: '', item: '', purchase_unit: '', sale_unit: '', factor: '', item_code: '', submission_date: moment().format('YYYY-MM-DD'), first_delivery_date: '', boe_date: '', boe_no: '', quantity: '', wastage: '', excess_quantity: '', purchase_unit_total: '', sale_unit_total: '', base_value: '', unit_price: '', isLoading: false });
const err = reactive({ lc_no: '', bin: '', item: '', purchase_unit: '', sale_unit: '', factor: '', item_code: '', submission_date: '', first_delivery_date: '', boe_date: '', boe_no: '', quantity: '', wastage: '', excess_quantity: '', purchase_unit_total: '', sale_unit_total: '', base_value: '', unit_price: '' });

// net quantity calculation
const netQtyCalc = () => {
  let wastage = Number(field.wastage);
  let quantity = Number(field.quantity);
  let excess_quantity = Number(field.excess_quantity);
  let factor = Number(field.factor);

  field.purchase_unit_total = Number(quantity) + excess_quantity;
  let net_quantity = wastage ? (field.purchase_unit_total - (field.purchase_unit_total * wastage / 100)).toFixed(2) : field.purchase_unit_total;
  field.sale_unit_total = net_quantity * factor;
  unitPrice();
}
// unit price calculation
const unitPrice = () => {
  let base_value = Number(field.base_value);
  let sale_unit_total = Number(field.sale_unit_total);
  field.unit_price = (base_value && sale_unit_total) ? (base_value / sale_unit_total).toFixed(2) : 0; // (base value / total qty)
};

// last form 4.3 with client_id and item_id
const form43Last = async () => {
  const client_id = field.bin?.id;
  const item_id = field.item?.id;

  // don't call API until both dropdowns are chosen
  if (!client_id || !item_id) return;

  await axios.get(route('form43-last'), { params: { client_id: field.bin?.id, item_id: field.item?.id } }).then((res) => {
    last.lastobj = !emptyObj(res.data?.lastform43);
    last.last_fctor = !empty(res.data?.lastform43?.factor);
    last.last_item_code = res.data?.lastform43?.item_code;

    field.factor = res.data?.lastform43?.factor;
    field.item_code = res.data?.lastform43?.item_code;
    field.purchase_unit = { id: res.data?.lastform43?.purchaseunit?.id, title: res.data?.lastform43?.purchaseunit?.name };
    field.sale_unit = { id: res.data?.lastform43?.saleunit?.id, title: res.data?.lastform43?.saleunit?.name };
  });
};

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin }),
    option: form43Last
  };
};

// item dropdown
const fetchedItem = async () => {
  return {
    url: route('item-dropdown'),
    data: 'item',
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` }),
    option: form43Last
  };
};

// purchase unit dropdown
const fetchedPUnit = async () => {
  return {
    url: route('unit-dropdown'),
    data: 'unit',
    mapFn: (punit) => ({ id: punit.id, title: punit.name })
  };
};

// sale unit dropdown
const fetchedSUnit = async () => {
  return {
    url: route('unit-dropdown'),
    data: 'unit',
    mapFn: (sunit) => ({ id: sunit.id, title: sunit.name })
  };
};

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.lc_no = row.lc_no ?? "";
  field.bin = !emptyObj(row.client) ? { id: row.client?.id, title: row.client?.bin } : "";
  field.item = !emptyObj(row.item) ? { id: row.item?.id, title: row.item?.name } : "";
  field.purchase_unit = !emptyObj(row.purchaseunit) ? { id: row.purchaseunit?.id, title: row.purchaseunit?.name } : "";
  field.sale_unit = !emptyObj(row.saleunit) ? { id: row.saleunit?.id, title: row.saleunit?.name } : "";
  field.factor = row.factor ?? "";
  field.item_code = row.item_code ?? "";
  // field.submission_date = row.submission_date ?? ""; // it is set in defailt field.submission_date
  field.first_delivery_date = row.first_delivery_date ?? "";
  field.boe_date = row.boe_date ?? "";
  field.boe_no = row.boe_no ?? "";
  field.quantity = row.quantity ?? "";
  field.wastage = row.wastage ?? "";
  field.excess_quantity = row.excess_quantity ?? "";
  field.purchase_unit_total = row.purchase_unit_total ?? "";
  field.sale_unit_total = row.sale_unit_total ?? "";
  field.base_value = row.base_value ?? "";
  field.unit_price = row.unit_price ?? "";


  // clear previous errors when setting a record
  err.lc_no = err.bin = err.item = err.purchase_unit = err.sale_unit = err.factor = err.item_code = err.submission_date = err.boe_date = err.boe_no = err.quantity = err.wastage = err.excess_quantity = err.base_value = "";
};

// reset form
const resetForm = () => {
  field.id = field.lc_no = field.bin = field.item = field.purchase_unit = field.sale_unit = field.factor = field.item_code = field.first_delivery_date = field.boe_date = field.boe_no = field.quantity = field.wastage = field.excess_quantity = field.purchase_unit_total = field.sale_unit_total = field.base_value = field.unit_price = "";
  err.lc_no = err.bin = err.item = err.purchase_unit = err.sale_unit = err.factor = err.item_code = err.submission_date = err.boe_date = err.boe_no = err.quantity = err.wastage = err.excess_quantity = err.base_value = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    lc_no: field.lc_no,
    client_id: field.bin?.id,
    item_id: field.item?.id,
    purchase_unit_id: field.purchase_unit?.id,
    sale_unit_id: field.sale_unit?.id,
    factor: field.factor,
    item_code: field.item_code,
    submission_date: field.submission_date,
    first_delivery_date: field.first_delivery_date,
    boe_date: field.boe_date,
    boe_no: field.boe_no,
    quantity: field.quantity,
    wastage: field.wastage,
    excess_quantity: field.excess_quantity,
    base_value: field.base_value,
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.lc_no = er.lc_no;
    err.bin = er.client_id;
    err.item = er.item_id;
    err.purchase_unit = er.purchase_unit_id;
    err.sale_unit = er.sale_unit_id;
    err.factor = er.factor;
    err.item_code = er.item_code;
    err.submission_date = er.submission_date;
    err.boe_date = er.boe_date;
    err.boe_no = er.boe_no;
    err.quantity = er.quantity;
    err.wastage = er.wastage;
    err.excess_quantity = er.excess_quantity;
    err.base_value = er.base_value;
  };

  if (field.id) {
    router.put(route('form43-update', field.id), form, {
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
    ['id'].forEach((k) => delete form[k]);

    router.post(route('form43-create'), form, {
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