<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12 col-sm-5">
            <Input type="text" category="number" id="lc_no" label="Lc No" maxlength="20"
              placeholder="Ex: 00003764658574" autocomplete="off" v-model="field.lc_no" :err="err.lc_no" focus must />
          </div>
          <div class="col-12 col-sm-7">
            <SelectInfinite ref="binDD" placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" :disabled="!empty(field.id)" must />
          </div>
          <div class="col-12 col-sm-8">
            <SelectInfinite ref="itemDD" placeholder="Select..." title="Item" v-model="field.item"
              :fetched="fetchedItem" :err="err.item" :reset-key="field.bin?.id" must />
          </div>
          <div class="col-12 col-sm-4">
            <Input type="text" label="Item Code" placeholder="Ex: 001" v-model="field.item_code" :err="err.item_code"
              :hood="last.last_item_code" :readonly="last.last621" must />
          </div>
          <div class="col-6 col-sm-4">
            <SelectInfinite ref="punitDD" placeholder="Select..." title="Purchase Unit" v-model="field.purchase_unit"
              :fetched="fetchedPUnit" :err="err.purchase_unit" :disabled="last.last621" must />
          </div>
          <div class="col-6 col-sm-4">
            <SelectInfinite ref="sunitDD" placeholder="Select..." title="Sale Unit" v-model="field.sale_unit"
              :fetched="fetchedSUnit" :err="err.sale_unit" :disabled="last.last621" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" category="decimal" label="factor" placeholder="Ex: 22" v-model="field.factor"
              :err="err.factor" :readonly="last.last621" @keyup="recompute" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="date" label="submission date" placeholder="Ex: 2023-01-01" v-model="field.submission_date"
              :err="err.submission_date" readonly must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="date" label="first delivery date" placeholder="Ex: 2023-01-01"
              v-model="field.first_delivery_date" :err="err.first_delivery_date" />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="date" label="BoE date" placeholder="Ex: 2023-01-01" v-model="field.boe_date"
              :err="err.boe_date" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" id="boe_no" label="BoE No" placeholder="Ex: 5214" v-model="field.boe_no"
              :err="err.boe_no" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" category="decimal" id="quantity" label="quantity" placeholder="Ex: 50"
              v-model="field.quantity" :err="err.quantity" @keyup="recompute" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" category="decimal" label="excess quantity" placeholder="Ex: 254.25"
              v-model="field.excess_quantity" :err="err.excess_quantity" @keyup="recompute" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" category="decimal" label="purchase total" placeholder="Ex: 25"
              v-model="field.purchase_unit_total" :err="err.purchase_unit_total" must readonly />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" category="decimal" id="wastage" label="wastage(%)" placeholder="Ex: 3.5"
              v-model="field.wastage" :err="err.wastage" @keyup="recompute" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" category="decimal" label="stock quantity" placeholder="Ex: 255"
              v-model="field.sale_unit_total" :err="err.sale_unit_total" must readonly />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" id="base_value" category="decimal" label="base value" placeholder="Ex: 18965.874"
              v-model="field.base_value" :err="err.base_value" @keyup="recompute" must />
          </div>
          <div class="col-6 col-sm-4">
            <Input type="text" category="decimal" id="mcu" label="MCU" placeholder="Amount ÷ 100000" v-model="field.mcu"
              :err="err.mcu" must />
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
import { ref, reactive, inject, nextTick, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { empty } from "../../helpers/utils";
import { user, role } from "../../composables/user";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";

import Alert from '../../helpers/customAlert';

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// form 4.3 field
const last = reactive({ last621: null, has_form621: false, last_item_code: '', last_fctor: '', last_purchase_unit: '', last_sale_unit: '' });

const field = reactive({ id: '', lc_no: '', bin: '', item: '', purchase_unit: '', sale_unit: '', factor: '', item_code: '', submission_date: moment().format('YYYY-MM-DD'), first_delivery_date: '', boe_date: '', boe_no: '', quantity: '', wastage: '', excess_quantity: '', purchase_unit_total: '', sale_unit_total: '', base_value: '', mcu: '', unit_price: '', isLoading: false });

const err = reactive({ lc_no: '', bin: '', item: '', purchase_unit: '', sale_unit: '', factor: '', item_code: '', submission_date: '', first_delivery_date: '', boe_date: '', boe_no: '', quantity: '', wastage: '', excess_quantity: '', purchase_unit_total: '', sale_unit_total: '', base_value: '', mcu: '', unit_price: '' });

// last form 6.2.1 with client_id and item_id
const form621last = async () => {
  const param = { client_id: field.bin?.id, item_id: field.item?.id };

  // if cleared, blank and return (no API, no recompute to 0)
  if (!param.client_id || !param.item_id) {
    field.factor = field.item_code = field.purchase_unit = field.sale_unit = last.last_item_code = "";
    return;
  }

  const { data } = await axios(route('form621-last'), { params: param });
  const res621 = data?.form621last;
  last.last621 = !empty(res621);
  last.last_item_code = data?.max_item_code ?? "";
  field.item_code = data?.item_code ?? "";
  field.factor = !empty(res621) ? res621?.form43?.factor : "";
  field.purchase_unit = !empty(res621?.form43?.purchaseunit) ? { id: res621?.form43?.purchaseunit?.id, title: res621?.form43?.purchaseunit?.name } : "";
  field.sale_unit = !empty(res621?.form43?.saleunit) ? { id: res621?.form43?.saleunit?.id, title: res621?.form43?.saleunit?.name } : "";
};

// calculate live field
const recompute = async () => {
  const wastage = +(field.wastage);
  const quantity = +(field.quantity);
  const excess_quantity = +(field.excess_quantity);
  const factor = +(field.factor);
  const base_value = +(field.base_value);

  const put = quantity + excess_quantity;
  const netQty = wastage ? (put - (put * (wastage / 100))) : put;
  const sut = netQty * factor;
  const up = (base_value && sut) ? (base_value / sut) : 0; // (base value / total qty)

  field.purchase_unit_total = +put.toFixed(2);
  field.sale_unit_total = +sut.toFixed(2);
  field.unit_price = +up.toFixed(2);
};

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
      (empty(field.bin?.id) || empty(row?.id)) && (last.last621 = false);
      empty(field.id) && await form621last();
    }
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
const setForm = async (data = {}) => {
  const row = data.form43;

  last.has_form621 = data.has_form621; // for block or open field
  last.last_item_code = data.max_item_code ?? "";

  field.id = row.id ?? "";
  field.lc_no = row.lc_no ?? "";
  field.bin = !empty(row.client) ? { id: row.client?.id, title: row.client?.bin, val: row.client } : "";
  await nextTick(); // selectinfinite always reset and clear. it is stop clear child visual when click edit
  field.item = !empty(row.item) ? { id: row.item?.id, title: `${row.item?.name} [${row.item?.hs_code}]` } : "";
  field.purchase_unit = !empty(row.purchaseunit) ? { id: row.purchaseunit?.id, title: row.purchaseunit?.name } : "";
  field.sale_unit = !empty(row.saleunit) ? { id: row.saleunit?.id, title: row.saleunit?.name } : "";
  field.factor = row.factor ?? "";
  field.item_code = row.item_code ?? "";
  field.first_delivery_date = row.first_delivery_date ?? "";
  field.boe_date = row.boe_date ?? "";
  field.boe_no = row.boe_no ?? "";
  field.quantity = row.quantity ?? "";
  field.wastage = row.wastage ?? "";
  field.excess_quantity = row.excess_quantity ?? "";
  field.purchase_unit_total = row.purchase_unit_total ?? "";
  field.sale_unit_total = row.sale_unit_total ?? "";
  field.base_value = row.base_value ?? "";
  field.mcu = row.mcu ?? "";
  field.unit_price = row.unit_price ?? "";

  // clear previous errors when setting a record
  err.lc_no = err.bin = err.item = err.purchase_unit = err.sale_unit = err.factor = err.item_code = err.submission_date = err.boe_date = err.boe_no = err.quantity = err.wastage = err.excess_quantity = err.base_value = err.first_delivery_date = err.mcu = "";
};

// reset form
const resetForm = () => {
  last.last43 = last.has_form621 = false;
  field.id = field.lc_no = field.bin = field.item = field.purchase_unit = field.sale_unit = field.factor = field.item_code = field.first_delivery_date = field.boe_date = field.boe_no = field.quantity = field.wastage = field.excess_quantity = field.purchase_unit_total = field.sale_unit_total = field.base_value = field.mcu = field.unit_price = "";
  err.lc_no = err.bin = err.item = err.purchase_unit = err.sale_unit = err.factor = err.item_code = err.submission_date = err.boe_date = err.boe_no = err.quantity = err.wastage = err.excess_quantity = err.base_value = err.mcu = err.first_delivery_date = "";
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
    mcu: field.mcu,
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
    err.first_delivery_date = er.first_delivery_date;
    err.boe_date = er.boe_date;
    err.boe_no = er.boe_no;
    err.quantity = er.quantity;
    err.wastage = er.wastage;
    err.excess_quantity = er.excess_quantity;
    err.base_value = er.base_value;
    err.mcu = er.mcu;
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

// const binDD = ref(null);
// const itemDD = ref(null);
// const punitDD = ref(null);
// const sunitDD = ref(null);
// onMounted(() => {
//   const reload = async () => {
//     const jobs = [];
//     if (binDD.value?.reload) {
//       jobs.push(binDD.value.reload());
//     }

//     if (itemDD.value?.reload) {
//       jobs.push(itemDD.value.reload());
//     }

//     if (punitDD.value?.reload) {
//       jobs.push(punitDD.value.reload());
//     }

//     if (sunitDD.value?.reload) {
//       jobs.push(sunitDD.value.reload());
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