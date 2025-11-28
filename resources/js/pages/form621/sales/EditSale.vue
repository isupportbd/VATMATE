<template>
  <form @submit.prevent="submitData">
    <div class="row gx-2">
      <div class="col-7">
        <div class="card border h-100 mb-2">
          <div class="card-body">
            <div class="row gx-1">
              <div class="col-4">
                <Input type="text" id="invoice-no" label="invoice no" placeholder="Ex: 0001" v-model="field.invoice_no"
                  :err="err.invoice_no" must readonly />
              </div>
              <div class="col-8">
                <SelectInfinite placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
                  :hood="field.bin?.val?.company_name" :err="err.bin" disabled must />
              </div>
              <div class="col-8">
                <SelectInfinite placeholder="Select..." title="item" v-model="field.item" :fetched="fetchedItem"
                  :err="err?.item" :reset-key="field.bin?.id" disabled must />
              </div>
              <div class="col-4">
                <Input type="date" label="date" placeholder="Ex: 2023-01-01" v-model="field.date" :err="err.date"
                  :hood="field.form621_last_date" must />
              </div>
              <div class="col-4">
                <Input type="text" category="decimal" label="grand total" placeholder="Ex: 118.530"
                  v-model="field.grand_total" :hood="`<span>limit: ${field.sale_limit}</span>`" :err="err.grand_total"
                  must readonly />
              </div>
              <div class="col-4">
                <Input type="text" category="decimal" label="stock" placeholder="Ex: 541.216" v-model="field.stock"
                  :hood="field.form621_sale_unit" :err="err?.stock" must disabled />
              </div>
              <div class="col-4">
                <Input type="text" category="decimal" label="quantity" placeholder="Ex: 541.216"
                  v-model="field.quantity" :err="err?.quantity" :hood="field.form621_sale_unit" @keyup="recompute"
                  must />
              </div>
              <div class="col-4">
                <Input type="text" category="decimal" label="unit_price" placeholder="Ex: 541.216"
                  v-model="field.unit_price" :err="err?.unit_price" must readonly />
              </div>
              <div class="col-4">
                <Input type="text" category="decimal" label="total price" placeholder="Ex: 541.216"
                  v-model="field.total_price" :err="err?.total_price" must readonly />
              </div>
              <div class="col-4">
                <SelectInfinite placeholder="Select..." title="unit" v-model="field.unit" :fetched="fetchedUnit"
                  :err="err?.unit" must disabled />
              </div>
              <div class="col-4">
                <InputGroup type="text" category="decimal" label="SD(%)" maxlength="6" placeholder="Ex: 5%"
                  placeholder1="Ex: 5214.25" v-model="field.sd" :err="err?.sd" :calcdata="field.sd_value"
                  @keyup="recompute" must />
              </div>
              <div class="col-4">
                <InputGroup type="text" category="decimal" label="VAT(%)" maxlength="6" placeholder="Ex: 7.5%"
                  placeholder1="Ex: 5214.25" v-model="field.vat" :err="err?.vat" :calcdata="field.vat_value"
                  @keyup="recompute" must />
              </div>
              <div class="col-4">
                <Input type="text" id="mobile" label="mobile" maxlength="11" placeholder="Ex: 01711000000"
                  v-model="field.mobile" :err="err.mobile" />
              </div>
              <div class="col-4">
                <Input type="text" id="name" label="name" maxlength="100" placeholder="Ex: Jhon Doe"
                  v-model="field.name" :err="err.name" />
              </div>
              <div class="col-4">
                <Input type="text" id="bin-or-nid" label="BIN/NID" maxlength="17" placeholder="Ex: 12345678912412574"
                  v-model="field.bin_or_nid" :err="err.bin_or_nid" />
              </div>
              <div class="col-4">
                <Input type="text" id="address" label="address" maxlength="150" placeholder="Ex: Tamabil, Sylhet"
                  v-model="field.address" :err="err.address" />
              </div>
              <div class="col-4" :class="{ 'd-none': +(field.sale_limit) > +(field.grand_total_note15 || 0) }">
                <SelectInfinite placeholder="Select..." title="bank" v-model="field.bank" :fetched="fetchedBank"
                  :err="err.bank" must />
              </div>
              <div class="col-4" :class="{ 'd-none': +(field.sale_limit) > +(field.grand_total_note15 || 0) }">
                <Input type="text" id="transaction-no" label="Transaction No" maxlength="20"
                  placeholder="Ex: 512489654654" v-model="field.transaction_no" :err="err.transaction_no" must />
              </div>
              <div class="col-4" :class="{ 'd-none': +(field.sale_limit) > +(field.grand_total_note15 || 0) }">
                <Input type="date" label="transaction date" placeholder="Ex: 2023-01-01"
                  v-model="field.transaction_date" :err="err.transaction_date" must />
              </div>
            </div>
            <div class="d-flex align-items-center">
              <Button type="submit" class="btn btn-primary me-1" label="Save" icon="bi bi-save" title="save"
                :disabled="field.isLoading" />
              <Button type="button" class="btn btn-secondary mx-1" label="Reset" icon="bi bi-stars" title="reset"
                @click="resetForm" />
            </div>
          </div>
        </div>
      </div>
      <div class="col-5">
        <ClientDetails :view="field.bin?.val" />
      </div>
    </div>
  </form>
</template>

<script setup>
import axios from "axios";
import moment from "moment-timezone";
import { ref, reactive, inject, onMounted, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import { empty } from "../../../helpers/utils";

import Button from "../../../components/Button.vue";
import Input from "../../../components/Input.vue";
import SelectInfinite from "../../../components/SelectInfinite.vue";
import ClientDetails from "./ClientDetails.vue";
import InputGroup from "../../../components/InputGroup.vue";

import Confirm from "../../../helpers/customConfirm";
import Alert from "../../../helpers/customAlert";

const props = defineProps({
  modal: { type: Object },
});

const notify = inject('showToast'); // toast

const field = reactive({ id: '', stock: '', invoice_no: '', bin: '', item: '', date: '', mobile: '', name: '', bin_or_nid: '', address: '', grand_total: '', bank: '', transaction_no: '', transaction_date: '', form621_last_date: '', sale_limit: '', note15: '', rate_vat: '', isLoading: false });

const err = reactive({ invoice_no: '', bin: '', date: '', mobile: '', name: '', bin_or_nid: '', address: '', grand_total: '', bank: '', transaction_no: '', transaction_date: '' });

// reset form
const resetForm = () => {
  field.id = field.date = field.mobile = field.name = field.bin_or_nid = field.address = field.grand_total = field.quantity = field.bank = field.transaction_no = field.transaction_date = "";
  err.invoice_no = err.bin = err.item = err.date = err.mobile = err.name = err.bin_or_nid = err.address = err.grand_total = err.bank = err.transaction_no = err.transaction_date = "";
};

// const last invoice
const loadSaleLimit = async () => await axios.get(route('sale-limit')).then((res) => {
  field.sale_limit = res.data?.salelimit ?? "";
  field.note15 = res.data?.note15 ?? "";
});

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin }),
  };
};

// item dropdown
const fetchedItem = () => {
  return {
    url: route('client-item-dropdown'),
    data: 'item',
    params: { client_id: field.bin?.id },
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` }),
    option: async () => await setPromiseData()
  };
};

// unit dropdown
const fetchedUnit = async () => {
  return {
    url: route('unit-dropdown'),
    data: 'unit',
    mapFn: (unit) => ({ id: unit.id, title: unit.name })
  };
};

// bank dropdown
const fetchedBank = async () => {
  return {
    url: route('bank-dropdown'),
    data: 'bank',
    mapFn: (bank) => ({ id: bank.id, title: bank.name })
  };
};

// set and call last client and item data
const clearRowCalcs = () => {
  field.unit = field.stock = field.form621_sale_unit = field.quantity = field.unit_price = field.total_price = field.sd = field.sd_value = field.vat = field.vat_value = field.grand_total = field.grand_total_note15 = field.mobile = field.bin_or_nid = field.address = field.form621_last_date = field.rate_vat = "";
};

const setPromiseData = async () => {
  const param = { client_id: field.bin?.id, item_id: field.item?.id };

  if (!param.client_id || !param.item_id) {
    clearRowCalcs();
    return;
  }

  const [rRate, r621] = await Promise.all([
    axios.get(route('rate-last'), { params: param }),
    axios.get(route('form621-last'), { params: param }),
  ]);

  const res621 = r621.data;

  field.unit = res621?.form621last?.unit ? { id: res621.form621last.unit.id, title: res621.form621last.unit.name } : "";
  field.form621_sale_unit = res621?.form621last?.unit?.name ?? "";
  field.form621_last_date = !empty(res621?.form621last) ? moment(res621?.form621last?.date).format("DD MMM YYYY") : "";
  const rate = rRate?.data?.ratelast ?? "";

  // 🔹 only keep rate VAT (tare) for Note 15 check
  field.rate_vat = rate?.vat ?? "";

  // 🔹 do NOT override sale's stored unit_price, sd, vat here
  recompute();
};


/**
 * total_price = quantity * unit_price
 * sd_value    = total_price * (sd / 100)
 * vat_value   = (total_price + sd_value) * (vat / 100)
 */
const recompute = () => {
  const qty = +(field.quantity) || 0;
  const up = +(field.unit_price) || 0;
  const sd = +(field.sd) || 0;
  const vat = +(field.vat) || 0;
  const tare = +(field.rate_vat) || 0;   // from rate-last
  const note15 = +(field.note15) || 0;

  // itemPrice = quantity * unit_price
  const itemPrice = +(qty * up).toFixed(2);

  // sd_value = itemPrice * (sd / 100)
  field.sd_value = +(itemPrice * (sd / 100)).toFixed(2);

  // vat_value = (itemPrice + sd_value) * (vat / 100)
  field.vat_value = +((itemPrice + field.sd_value) * (vat / 100)).toFixed(2);

  // totalPrice = itemPrice + sd_value + vat_value
  const totalPrice = itemPrice + field.sd_value + field.vat_value;

  field.total_price = itemPrice.toFixed(2);
  field.grand_total = totalPrice.toFixed(2);

  // 🔹 same Note 15 condition as backend
  const isVat15 = note15 > 0 && (vat === note15 || tare === note15);

  field.grand_total_note15 = isVat15 ? totalPrice.toFixed(2) : "0.00";
};


// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.invoice_no = row.invoice_no ?? "";
  field.bin = !empty(row.client) ? { id: row.client?.id, title: row.client?.bin, val: row.client } : "";
  await nextTick(); // selectinfinite always reset and clear. it is stop clear child visual when click edit
  field.date = row.date ?? "";
  field.mobile = row.customer_mobile ?? "";
  field.name = row.customer_name ?? "";
  field.bin_or_nid = row.bin_or_nid ?? "";
  field.address = row.address ?? "";
  field.grand_total = +(row.total_price) + +(row.sd_value) + +(row.vat_value) ?? "";
  field.bank = !empty(row.bank) ? { id: row.bank?.id, title: row.bank?.name } : "";
  field.transaction_no = row.transaction_no ?? "";
  field.transaction_date = row.transaction_date ?? "";
  field.item = !empty(row.item) ? { id: row.item?.id, title: row.item?.name } : "";
  field.stock = row.opening_stock ?? "";
  field.quantity = row.quantity ?? "";
  field.unit_price = row.unit_price ?? "";
  field.total_price = row.total_price ?? "";
  field.unit = !empty(row.unit) ? { id: row.unit?.id, title: row.unit?.name } : "";
  field.sd = row.sd ?? "";
  field.sd_value = row.sd_value ?? "";
  field.vat = row.vat ?? "";
  field.vat_value = row.vat_value ?? "";

  // clear previous errors when setting a record
  err.invoice_no = err.bin = err.date = err.mobile = err.name = err.bin_or_nid = err.address = err.grand_total = err.bank = err.transaction_no = err.transaction_date = err.item = err.stock = err.quantity = err.unit_price = err.total_price = err.unit = err.sd = err.vat = "";

  await setPromiseData();
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    date: field.date,
    customer_mobile: field.mobile,
    customer_name: field.name,
    bin_or_nid: field.bin_or_nid,
    address: field.address,
    item_id: field.item?.id,
    quantity: field.quantity,
    unit_price: field.unit_price,
    unit_id: field.unit?.id,
    sd: field.sd,
    vat: field.vat,
    bank_id: field.bank?.id,
    transaction_no: field.transaction_no,
    transaction_date: field.transaction_date
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => { er[key] = errors[key].toString() });
    err.bin = er.client_id;
    err.item = er.item_id;
    err.date = er.date;
    err.mobile = er.customer_mobile;
    err.name = er.customer_name;
    err.bin_or_nid = er.bin_or_nid;
    err.address = er.address;
    err.bank = er.bank_id;
    err.transaction_no = er.transaction_no;
    err.transaction_date = er.transaction_date;
    err.unit = er.unit_id;
    err.quantity = er.quantity;
    err.unit_price = er.unit_price;
    err.total_price = er.total_price;
    err.sd = er.sd;
    err.vat = er.vat;
  };

  if (field.id) {
    router.put(route('sale-update', field.id), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(`<span class="text-danger">${e.props.flash.err}</span>`);
        } else {
          notify(e.props.flash.msg, 'success');
          resetForm();
          props.modal.close();
        }
      },
      onError: onErr,
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  }
};

onMounted(() => loadSaleLimit());
</script>