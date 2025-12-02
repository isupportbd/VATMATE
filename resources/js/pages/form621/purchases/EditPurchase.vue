<template>
  <div class="row gx-2">
    <div class="col-8">
      <div class="card border h-100">
        <div class="card-body">
          <form @submit.prevent="submitData">
            <div class="row gx-1">
              <div class="col-12">
                <Input type="hidden" v-model="field.id" readonly />
              </div>
              <div class="col-4">
                <SelectInfinite ref="binDD" placeholder="Select..." title="BIN" v-model="field.bin"
                  :fetched="fetchedBin" :hood="field.bin?.val?.company_name" :err="err.bin" disabled must />
              </div>
              <div class="col-5">
                <SelectInfinite ref="itemDD" placeholder="Select..." title="item" v-model="field.item"
                  :fetched="fetchedItem" :hood="`<span class='text-danger text-lowercase'>${field.form43_check}</span>`"
                  :err="err.item" :reset-key="field.bin?.id" disabled must />
              </div>
              <div class="col-3">
                <Input type="text" id="lc" label="LC no" maxlength="20" placeholder="Ex: 5124845487"
                  v-model="field.lc_no" :err="err.lc_no" @keyup="lcAutoSetField" must />
              </div>
              <div class="col-3">
                <Input type="date" label="LC date" placeholder="Ex: 2023-01-01" v-model="field.lc_date"
                  :err="err.lc_date" />
              </div>
              <div class="col-3">
                <Input type="text" id="boe" label="BoE" maxlength="20" placeholder="Ex: 5124" v-model="field.boe"
                  :err="err.boe" must />
              </div>
              <div class="col-3">
                <Input type="date" label="BoE date" placeholder="Ex: 2023-01-01" v-model="field.boe_date"
                  :err="err.boe_date" must />
              </div>
              <div class="col-3">
                <Input type="date" label="entry date" placeholder="Ex: 2023-01-01" v-model="field.entry_date"
                  :err="err.entry_date" :hood="field.form621_last_date" must />
              </div>
              <div class="col-3">
                <SelectInfinite ref="stationDD" placeholder="Select..." title="station" v-model="field.stationcode"
                  :fetched="fetchedStationcode" :err="err.stationcode" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="DF/CVAT/CF" placeholder="Ex: 5214.215"
                  v-model="field.df_cvat_cf" :err="err.df_cvat_cf" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="assessable value" placeholder="Ex: 5214.215"
                  v-model="field.assessable_value" :err="err.assessable_value" @keyup="recompute" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="opening stock" placeholder="Ex: 541.216"
                  v-model="field.opening_stock" :err="err.opening_stock" :hood="field.form43_sale_unit"
                  :readonly="field.readonly_opening_stock" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="quantity" placeholder="Ex: 541.216"
                  v-model="field.quantity" :err="err.quantity" :hood="field.form43_purchase_unit" @keyup="recompute"
                  must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="excess quantity" placeholder="Ex: 5247.216"
                  v-model="field.excess_quantity" :err="err.excess_quantity" :hood="field.form43_purchase_unit"
                  @keyup="recompute" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" maxlength="6" label="wastage(%)" placeholder="Ex: 3.5"
                  v-model="field.wastage" :err="err.wastage" must readonly />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="total quantity" placeholder="Ex: 3254.216"
                  v-model="field.total_quantity" :err="err.total_quantity" :hood="field.form43_purchase_unit"
                  @keyup="recompute" must readonly />
              </div>
              <div class="col-3">
                <SelectInfinite ref="unitDD" placeholder="Select..." title="unit" v-model="field.unit"
                  :fetched="fetchedUnit" :err="err.unit" must disabled />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="base value" placeholder="Ex: 521423.215"
                  v-model="field.base_value" :err="err.base_value" @keyup="recompute" must readonly />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="unit price" placeholder="Ex: 118.530"
                  v-model="field.unit_price" :err="err.unit_price" :hood="field.form43_sale_unit" @keyup="recompute"
                  must readonly />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="CD(%)" maxlength="6" placeholder="Ex: 5%"
                  placeholder1="Ex: 5214.25" v-model="field.cd" :err="err.cd" :calcdata="field.cd_value"
                  @keyup="recompute" must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="RD(%)" maxlength="6" placeholder="Ex: 5%"
                  placeholder1="Ex: 5214.25" v-model="field.rd" :err="err.rd" :calcdata="field.rd_value"
                  @keyup="recompute" must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="SD(%)" maxlength="6" placeholder="Ex: 5%"
                  placeholder1="Ex: 5214.25" v-model="field.sd" :err="err.sd" :calcdata="field.sd_value"
                  @keyup="recompute" must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="VAT(%)" maxlength="6" placeholder="Ex: 7.5%"
                  placeholder1="Ex: 5214.25" v-model="field.vat" :err="err.vat" :calcdata="field.vat_value"
                  @keyup="recompute" must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="AT(%)" maxlength="6" placeholder="Ex: 7.5%"
                  placeholder1="Ex: 5214.25" v-model="field.at" :err="err.at" :calcdata="field.at_value"
                  @keyup="recompute" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" id="mcu" label="MCU" placeholder="Ex: 0.00112" v-model="field.mcu"
                  :err="err.mcu" readonly must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="MCU amount" placeholder="Ex: 300302.87"
                  v-model="field.mcu_amount" :err="err.mcu_amount" @keyup="recompute" readonly must />
              </div>
              <div class="col-3">
                <SelectInfinite placeholder="Select..." title="mushak note" v-model="field.mushaknote"
                  :fetched="fetchedMushaknote" :err="err.mushaknote" must />
              </div>
              <div class="col-3">
                <Input type="text" id="receipt-no" label="Receipt No" maxlength="20" placeholder="Ex: 512489654654"
                  v-model="field.receipt_number" :err="err.receipt_number" />
              </div>
              <div class="col-3">
                <Input type="date" label="receipt date" placeholder="Ex: 2023-01-01" v-model="field.receipt_date"
                  :err="err.receipt_date" />
              </div>
              <div class="col-5">
                <Input type="text" label="exporter name" maxlength="100" placeholder="Ex: Jhon Doe"
                  v-model="field.exporter_name" :err="err.exporter_name" />
              </div>
              <div class="col-4">
                <Input type="text" label="address" maxlength="100" placeholder="Ex: China" v-model="field.address"
                  :err="err.address" />
              </div>
            </div>
            <div class="d-flex align-items-center">
              <Button type="submit" class="btn btn-primary me-1" label="Save" icon="bi bi-save"
                :disabled="field.isLoading" />
              <Button type="button" class="btn btn-secondary ms-1" label="Reset" icon="bi bi-stars"
                @click="resetForm" />
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-4">
      <ClientDetails :view="field.client_details" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, inject, computed, nextTick, onMounted, onBeforeUnmount } from "vue";
import moment from "moment-timezone";
import axios from "axios";
import { debounce } from "lodash-es";
import { router } from "@inertiajs/vue3";
import { empty } from "../../../helpers/utils";
import { user, role } from "../../../composables/user";

import Button from "../../../components/Button.vue";
import Input from "../../../components/Input.vue";
import SelectInfinite from "../../../components/SelectInfinite.vue";
import InputGroup from "../../../components/InputGroup.vue";
import ClientDetails from './ClientDetails.vue';

import Alert from "../../../helpers/customAlert";

const props = defineProps({
  modal: { type: Object },
});

const notify = inject('showToast'); // toast

// purchase field
const field = reactive({ id: '', bin: '', item: '', lc_no: '', lc_date: '', boe: '', boe_date: '', entry_date: '', stationcode: '', mushaknote: '', df_cvat_cf: '', assessable_value: '', opening_stock: '', quantity: '', excess_quantity: '', wastage: '', total_quantity: '', unit: '', base_value: '', unit_price: '', cd: '', cd_value: '', rd: '', rd_value: '', sd: '', sd_value: '', vat: '', vat_value: '', at: '', at_value: '', mcu: '', mcu_amount: '', receipt_number: '', receipt_date: '', exporter_name: '', address: '', form621_last_date: '', form43_purchase_unit: '', form43_sale_unit: '', form43_factor: '', form43_check: '', client_details: null, isLoading: false, readonly_opening_stock: true });

const err = reactive({ bin: '', item: '', lc_no: '', lc_date: '', boe: '', boe_date: '', entry_date: '', stationcode: '', mushaknote: '', df_cvat_cf: '', assessable_value: '', opening_stock: '', quantity: '', excess_quantity: '', wastage: '', total_quantity: '', unit: '', base_value: '', unit_price: '', cd: '', rd: '', sd: '', vat: '', at: '', mcu: '', mcu_amount: '', receipt_number: '', receipt_date: '', exporter_name: '', address: '' });

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin }),
  };
};

// item dropdown
const fetchedItem = async () => {
  return {
    url: route('client-item-dropdown'),
    data: 'item',
    params: { client_id: field.bin?.id },
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` }),
    option: async (row) => {
      await last();
      if (empty(field.bin?.id) || empty(row?.id)) {
        field.lc_no = field.lc_date = field.boe_date = field.entry_date = field.stationcode = "";
      }
    }
  };
};

// stationcode dropdown
const fetchedStationcode = async () => {
  return {
    url: route('stationcode-dropdown'),
    data: 'stationcode',
    mapFn: (stationcode) => ({ id: stationcode.id, title: stationcode.code })
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

// mushak note dropdown
const fetchedMushaknote = async () => {
  return {
    url: route('mushaknote-dropdown'),
    data: 'mushaknote',
    mapFn: (mushaknote) => ({ id: mushaknote.id, title: `${mushaknote.name_note_no}, ${mushaknote.percent}%` })
  };
};

/**
 * if same lc number has into form621
 * then fetch that lc number date,
 * boe date, entry date, station code
 * etc will be set
 */
const lcAutoSetField = debounce(async () => {
  const param = { client_id: field.bin?.id, item_id: field.item?.id, lc_no: field.lc_no }
  const { data } = await axios.get(route('purchase-lc-search'), { params: param });
  const resLc = data?.lcsearch;
  // console.log(resLc);
  field.lc_date = !empty(resLc) ? resLc.lc_date : "";
  field.boe_date = !empty(resLc) ? resLc.boe_date : "";
  field.entry_date = !empty(resLc) ? resLc.entry_date : "";
  field.stationcode = !empty(resLc) ? { id: resLc?.stationcode?.id, title: resLc?.stationcode?.code } : "";
}, 500);

/**
 * Here start instant data update
 * where need calcualtion
 */
const hasCombo = computed(() => !!field.bin?.id && !!field.item?.id); //combo checker

// one-liner to blank all calculated fields
const clearCalcs = () => {
  field.cd_value = field.rd_value = field.sd_value = field.base_value = field.vat_value = field.at_value = field.total_quantity = field.unit_price = field.mcu = field.opening_stock = field.wastage = field.unit = field.cd = field.rd = field.sd = field.vat = field.at = field.form43_purchase_unit = field.form43_sale_unit = field.mcu = field.form621_last_date = "";
  field.readonly_opening_stock = true;
};

// last check from form43 and form621
const last = async () => {
  const param = { client_id: field.bin?.id, item_id: field.item?.id };

  // if cleared, blank and return (no API, no recompute to 0)
  if (!param.client_id || !param.item_id) {
    clearCalcs();
    return;
  }

  const [clientsingle, r43, r621] = await Promise.all([
    axios.get(route('client-single', { id: param.client_id })),
    axios.get(route('form43-last'), { params: param }),
    axios.get(route('form621-last'), { params: param }),
  ]);

  field.client_details = clientsingle.data?.client;

  const res43 = r43.data?.form43last;
  field.wastage = !empty(res43) ? res43?.wastage : "";
  field.unit = !empty(res43?.saleunit) ? { id: res43?.saleunit?.id, title: res43?.saleunit?.name } : "";
  field.cd = !empty(res43) ? res43?.item?.cd : "";
  field.rd = !empty(res43) ? res43?.item?.rd : "";
  field.sd = !empty(res43) ? res43?.item?.sd : "";
  field.vat = !empty(res43) ? res43?.item?.vat : "";
  field.at = !empty(res43) ? res43?.item?.at : "";
  field.mcu = !empty(res43) ? res43?.mcu : "";
  field.form43_factor = !empty(res43) ? res43?.factor : "";
  field.form43_purchase_unit = !empty(res43?.purchaseunit) ? res43?.purchaseunit?.name : "";
  field.form43_sale_unit = !empty(res43?.saleunit) ? res43?.saleunit?.name : "";
  field.form43_check = !(r43.data?.has_addition) ? r43.data?.message : "";

  const res621 = r621.data;
  field.form621_last_date = !empty(res621?.lastdate) ? moment(res621?.lastdate).format("DD MMM YYYY") : "";
  field.opening_stock = !empty(res621?.form621last) ? res621?.form621last?.opening_stock : "";

  recompute();

  return { res43, res621 };
};

/**
 * av = assessable_value
 * cd = av * cd%
 * rd = av * rd%
 * sd = (av + cd + rd) * sd%
 * base_value = av + cd + rd + sd
 * vat = base_value * vat%
 * at = base_value * at%
 * unit_price = base_value_of_vat / net_quantity
 */
const num = v => (v === '' || v == null || Number.isNaN(+v) ? 0 : +v);
const pct = v => num(v) / 100;

const recompute = () => {
  if (!hasCombo.value) { clearCalcs(); return; }

  const mcu = num(field.mcu);
  const factor = num(field.form43_factor);
  const exQty = num(field.excess_quantity);
  const wastage = num(field.wastage);
  const assessable = num(field.assessable_value);
  const cdRate = pct(field.cd);
  const rdRate = pct(field.rd);
  const sdRate = pct(field.sd);
  const vatRate = pct(field.vat);
  const atRate = pct(field.at);
  const qty = pct(field.quantity);

  const cdValue = assessable * cdRate;
  const rdValue = assessable * rdRate;
  const sdBase = assessable + cdValue + rdValue;
  const sdValue = sdBase * sdRate;
  const baseValue = assessable + cdValue + rdValue + sdValue;
  const mcuAmount = assessable * mcu;

  const vatValue = baseValue * vatRate;
  const atValue = baseValue * atRate;

  const grossQty = qty + exQty;
  const totalQty = grossQty - (grossQty * wastage);
  const netQtySale = totalQty * factor;

  // console.log(factor);

  field.cd_value = +cdValue.toFixed(2); // here + is JavaScript shorthand that converts a string back into a number
  field.rd_value = +rdValue.toFixed(2);
  field.sd_value = +sdValue.toFixed(2);
  field.base_value = +baseValue.toFixed(2);
  field.vat_value = +vatValue.toFixed(2);
  field.at_value = +atValue.toFixed(2);
  field.mcu_amount = +mcuAmount.toFixed(2);
  field.excess_quantity = +exQty.toFixed(2);
  field.total_quantity = totalQty > 0 ? +totalQty.toFixed(2) : 0;
  field.unit_price = netQtySale > 0 ? +(baseValue / netQtySale).toFixed(2) : 0;
};

// --- Expose API for parent ---
const setForm = async (data = {}) => {
  let row = data.purchase;

  field.id = row?.id ?? "";
  field.bin = !empty(row.client) ? { id: row.client?.id, title: row.client?.bin, val: row.client } : "";
  await nextTick(); // selectinfinite always reset and clear. it is stop clear child visual when click edit
  field.item = !empty(row.item) ? { id: row.item?.id, title: `${row.item?.name} [${row.item?.hs_code}]` } : "";

  await last(); // call here cause last need cilent and item id

  field.lc_no = row.lc_no ?? "";
  field.lc_date = row.lc_date ?? "";
  field.boe = row.boe ?? "";
  field.boe_date = row.boe_date ?? "";
  field.entry_date = row.entry_date ?? "";
  field.stationcode = !empty(row.stationcode) ? { id: row.stationcode?.id, title: row.stationcode?.code } : "";
  field.mushaknote = !empty(row.mushaknote) ? { id: row.mushaknote?.id, title: row.mushaknote?.name_note_no } : "";
  field.df_cvat_cf = row.df_cvat_cf ?? "";
  field.assessable_value = row.assessable_value ?? "";
  field.opening_stock = row.opening_stock ?? "";
  field.quantity = (+(row.quantity) / +(row.factor)).toFixed(2) ?? "";
  field.excess_quantity = (+(row.excess_quantity) / +(row.factor)).toFixed(2) ?? "";
  field.wastage = row.wastage ?? "";
  field.total_quantity = (+(row.total_quantity) / +(row.factor)).toFixed(2) ?? "";
  field.unit = !empty(row.unit) ? { id: row.unit?.id, title: row.unit?.name } : "";
  field.base_value = row.base_value ?? "";
  field.unit_price = row.unit_price ?? "";
  field.cd = row.cd ?? "";
  field.cd_value = row.cd_value ?? "";
  field.rd = row.rd ?? "";
  field.rd_value = row.rd_value ?? "";
  field.sd = row.sd ?? "";
  field.sd_value = row.sd_value ?? "";
  field.vat = row.vat ?? "";
  field.vat_value = row.vat_value ?? "";
  field.at = row.at ?? "";
  field.at_value = row.at_value ?? "";
  field.mcu = row.mcu ?? "";
  field.mcu_amount = row.mcu_amount ?? "";
  field.receipt_number = row.receipt_number ?? "";
  field.receipt_date = row.receipt_date ?? "";
  field.exporter_name = row.exporter_name ?? "";
  field.address = row.address ?? "";
  field.readonly_opening_stock = !data.openingcheck;

  // clear previous errors when setting a record
  err.bin = err.item = err.lc_no = err.lc_date = err.boe = err.boe_date = err.entry_date = err.stationcode = err.mushaknote = err.df_cvat_cf = err.assessable_value = err.opening_stock = err.quantity = err.excess_quantity = err.wastage = err.total_quantity = err.unit = err.base_value = err.unit_price = err.cd = err.rd = err.sd = err.vat = err.at = err.assesment_number = err.assesment_date = err.receipt_number = err.receipt_date = err.exporter_name = err.address = err.mcu = err.mcu_amount = "";


};

// reset form
const resetForm = () => {
  field.id = field.bin = field.item = field.lc_no = field.lc_date = field.boe = field.boe_date = field.entry_date = field.stationcode = field.mushaknote = field.df_cvat_cf = field.assessable_value = field.opening_stock = field.quantity = field.excess_quantity = field.wastage = field.total_quantity = field.unit = field.base_value = field.unit_price = field.cd = field.cd_value = field.rd = field.rd_value = field.sd = field.sd_value = field.vat = field.vat_value = field.at = field.at_value = field.mcu = field.mcu_amount = field.receipt_number = field.receipt_date = field.exporter_name = field.address = field.form43_purchase_unit = field.form43_sale_unit = field.form43_factor = field.form621_last_date = field.opening_stock = "";

  err.bin = err.item = err.lc_no = err.lc_date = err.boe = err.boe_date = err.entry_date = err.stationcode = err.mushaknote = err.df_cvat_cf = err.assessable_value = err.opening_stock = err.quantity = err.excess_quantity = err.wastage = err.total_quantity = err.unit = err.base_value = err.unit_price = err.cd = err.rd = err.sd = err.vat = err.at = err.mcu = err.mcu_amount = err.receipt_number = err.receipt_date = err.exporter_name = err.address = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    item_id: field.item?.id,
    lc_no: field.lc_no,
    lc_date: field.lc_date,
    boe: field.boe,
    boe_date: field.boe_date,
    entry_date: field.entry_date,
    stationcode_id: field.stationcode?.id,
    mushaknote_id: field.mushaknote?.id,
    df_cvat_cf: field.df_cvat_cf,
    assessable_value: field.assessable_value,
    opening_stock: field.opening_stock,
    quantity: field.quantity,
    excess_quantity: field.excess_quantity,
    wastage: field.wastage,
    unit_id: field.unit?.id,
    cd: field.cd,
    rd: field.rd,
    sd: field.sd,
    vat: field.vat,
    at: field.at,
    mcu: field.mcu,
    receipt_number: field.receipt_number,
    receipt_date: field.receipt_date,
    exporter_name: field.exporter_name,
    address: field.address,
  };

  // console.log(form);

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.item = er.item_id;
    err.lc_no = er.lc_no;
    err.lc_date = er.lc_date;
    err.boe = er.boe;
    err.boe_date = er.boe_date;
    err.entry_date = er.entry_date;
    err.stationcode = er.stationcode_id;
    err.mushaknote = er.mushaknote_id;
    err.df_cvat_cf = er.df_cvat_cf;
    err.assessable_value = er.assessable_value;
    err.opening_stock = er.opening_stock;
    err.quantity = er.quantity;
    err.excess_quantity = er.excess_quantity;
    err.wastage = er.wastage;
    err.unit = er.unit_id;
    err.unit_price = er.unit_price;
    err.cd = er.cd;
    err.rd = er.rd;
    err.sd = er.sd;
    err.vat = er.vat;
    err.at = er.at;
    err.mcu = er.mcu;
    err.mcu_amount = er.mcu_amount;
    err.receipt_number = er.receipt_number;
    err.receipt_date = er.receipt_date;
    err.exporter_name = er.exporter_name;
    err.address = er.address;
  };

  if (field.id) {
    router.put(route('purchase-update', field.id), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        if (e.props.flash.err) {
          Alert(`<b class='text-danger'>${e.props.flash.err}</b>`);
          resetForm();
          props.modal.close();
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

// const binDD = ref(null);
// const itemDD = ref(null);
// const stationDD = ref(null);
// const unitDD = ref(null);
// onMounted(() => {
//   const reload = async () => {
//     const jobs = [];

//     if (binDD.value?.reload) {
//       jobs.push(binDD.value.reload());
//     }

//     if (itemDD.value?.reload) {
//       jobs.push(itemDD.value.reload());
//     }

//     if (stationDD.value?.reload) {
//       jobs.push(stationDD.value.reload());
//     }

//     if (unitDD.value?.reload) {
//       jobs.push(unitDD.value.reload());
//     }

//     // run all reloads in parallel
//     await Promise.all(jobs);
//   };

//   window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
// });

// onBeforeUnmount(() => {
//   window.Echo.leave(`App.Models.User.${user.id}`);
// });
</script>