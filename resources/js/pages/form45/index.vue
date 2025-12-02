<template>

  <Head title="Form 4.5" />
  <PageBar>
    <p class="m-0 fs-4">Form 4.5</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-12 col-lg-7 mb-2 mb-lg-0">
      <div class="card border h-100">
        <div class="card-body">
          <form @submit.prevent="submitData">
            <div class="row gx-1">
              <div class="col-12">
                <Input type="hidden" v-model="field.id" readonly />
              </div>
              <div class="col-12 col-sm-7 col-lg-8">
                <SelectInfinite ref="binDD" placeholder="Select..." title="BIN" v-model="field.bin"
                  :fetched="fetchedBin" :hood="field.bin?.val?.company_name" :err="err.bin" must />
              </div>
              <div class="col-12 col-sm-5 col-lg-4">
                <SelectInfinite ref="lcDD" placeholder="Select..." title="LC" v-model="field.lc" :fetched="fetchedLC"
                  :err="err.lc" :reset-key="field.bin?.id" must />
              </div>
              <div class="col-12 col-sm-7 col-md-6">
                <SelectInfinite ref="itemDD" placeholder="Select..." title="item" v-model="field.item"
                  :fetched="fetchedItem" :reset-key="field.lc?.title" :err="err.item" must />
              </div>
              <div class="col-6 col-sm-5 col-md-3">
                <SelectInfinite ref="boeDD" placeholder="Select..." title="BoE" v-model="field.boe"
                  :fetched="fetchedBoe" :reset-key="field.item?.id" :err="err.boe" must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <SelectInfinite ref="unitDD" placeholder="Select..." title="unit" v-model="field.unit"
                  :fetched="fetchedUnit" :err="err.unit" must disabled />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="date" id="date" label="date" v-model="field.date" :hood="field.form621_last_date"
                  :err="err.date" must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="text" category="decimal" id="stock" label="stock" placeholder="Ex: 89.25"
                  v-model="field.stock" :err="err.stock" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="text" category="decimal" id="quantity" label="quantity" placeholder="Ex: 89.25"
                  v-model="field.quantity" :err="err.quantity" @keyup="recompute" must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="text" category="decimal" id="value"
                  label="value <span class='fs-mini text-danger'>(without vat)</span>" placeholder="Ex: 89.25"
                  v-model="field.value" :err="err.value" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="text" category="decimal" id="vat" label="VAT" placeholder="Ex: 412.25" v-model="field.vat"
                  :err="err.vat" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="text" category="decimal" id="proposed-value" label="proposed value"
                  placeholder="Ex: 412.25" v-model="field.proposed_value" :hood="field.purchase_unit_price"
                  :err="err.proposed_value" @keyup="recompute" must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="text" category="decimal" id="increasing-adjustment-vat" label="inc. adj. vat"
                  placeholder="Ex: 412.25" v-model="field.increasing_adjustment_vat"
                  :err="err.increasing_adjustment_vat" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3">
                <Input type="text" category="decimal" id="net-increase" label="net increase" placeholder="Ex: 412.25"
                  v-model="field.net_increase" :err="err.net_increase" readonly must />
              </div>
              <div class="col-12">
                <TextArea type="text" id="reason" label="reason" maxlength="150" style="height: 112px !important;"
                  placeholder="Ex: Reason" v-model="field.reason_ineligibility" :err="err.reason_ineligibility"
                  must="" />
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
    <div class="col-12 col-lg-5">
      <ClientDetails :view="field.bin?.val" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import moment from "moment-timezone";
import { ref, inject, reactive, nextTick, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { empty } from "../../helpers/utils";
import { user, role } from "../../composables/user";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import Input from "../../components/Input.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";
import TextArea from "../../components/TextArea.vue";
import ClientDetails from "./ClientDetails.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from '../../helpers/customAlert';

const props = defineProps({
  form45: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// creditnote field
const field = reactive({ id: '', bin: '', lc: '', item: '', boe: '', unit: '', date: '', quantity: '', value: '', vat: '', proposed_value: '', increasing_adjustment_vat: '', net_increase: '', reason_ineligibility: '', form621_last_date: '', stock: '', purchase_unit_price: '', purchase_sd: '', purchase_vat: '', rate_sd: '', rate_vat: '', note15: '', isLoading: false });

const err = reactive({ bin: '', lc: '', item: '', boe: '', unit: '', date: '', quantity: '', value: '', vat: '', proposed_value: '', increasing_adjustment_vat: '', net_increase: '', reason_ineligibility: '' });

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin }),
  };
};

// form 6.2.1 dropdown
const fetchedLC = async () => {
  return {
    url: route('form621-client-lc-inv-dropdown'),
    data: 'lcinvdropdown',
    params: { client_id: field?.bin?.id, type: 'lc' },
    mapFn: (col) => ({ title: col?.lc_or_invoice_no })
  };
};


// item dropdown
const fetchedItem = async () => {
  return {
    url: route('form621-client-lc-inv-item-dropdown'),
    data: 'itemdropdown',
    params: { client_id: field.bin?.id, lc_or_invoice_no: field.lc?.title },
    mapFn: (col) => ({ id: col?.item?.id, title: `${col?.item?.name} [${col?.item?.hs_code}]` }),
  }
};

// boe dropdown
const fetchedBoe = async () => {
  return {
    url: route('form621-client-lc-item-boe-dropdown'),
    data: 'boedropdown',
    params: { client_id: field.bin?.id, lc_or_invoice_no: field.lc?.title, item_id: field.item?.id },
    mapFn: (col) => ({ id: col?.id, title: col?.boe }),
    option: async () => await setPromiseData()
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

// one liner to blank
const clearCalcs = () => {
  field.boe = field.stock = field.unit = field.value = field.vat = field.form621_last_date = field.purchase_unit_price = field.increasing_adjustment_vat = field.net_increase = "";
};

const setPromiseData = async () => {
  const param = { client_id: field.bin?.id, item_id: field.item?.id, lc_or_invoice_no: field.lc?.title, boe: field.boe?.title };

  if (!param.client_id || !param.lc_or_invoice_no || !param.item_id || !param.boe) {
    clearCalcs();
    return;
  }

  const [rRate, r621] = await Promise.all([
    await axios.get(route('rate-last'), { params: param }),
    await axios.get(route('form621-client-item-lc-boe'), { params: param }),
  ]);

  const res621 = r621.data?.debitnote;
  field.unit = !empty(res621?.unit) ? { id: res621?.unit?.id, title: res621?.unit?.name } : "";
  field.form621_last_date = moment(r621?.data?.form621lastdate).format("DD MMM YYYY") ?? "";
  field.stock = r621?.data?.form621laststock?.stock ?? "";
  field.purchase_sd = res621?.purchase?.sd ?? "";
  field.purchase_vat = res621?.purchase?.vat ?? "";
  field.purchase_unit_price = res621?.purchase?.unit_price ?? "";
  field.note15 = r621?.data?.note15;

  const ratelast = rRate.data?.ratelast;
  field.rate_sd = ratelast?.sd ?? "";
  field.rate_vat = ratelast?.vat ?? "";
};

const recompute = async () => {
  const qty = +(field.quantity);
  const pup = +(field.purchase_unit_price);
  const psd = +(field.purchase_sd);
  const pvat = +(field.purchase_vat);
  const rsd = +(field.rate_sd);
  const rvat = +(field.rate_vat);
  const pv = +(field.proposed_value); // proposed_value can not be over purchase_unit_price

  // 1) If no quantity → clear EVERYTHING and stop
  if (!qty) {
    field.value = "";
    field.vat = "";
    field.increasing_adjustment_vat = "";
    field.net_increase = "";
    return;
  }

  // 2) Quantity exists → compute value & vat
  const price = qty * pup;
  const sdvalue = price * (psd / 100);
  const vatvalue = (price + sdvalue) * (pvat / 100);

  field.value = +price.toFixed(2);
  field.vat = +vatvalue.toFixed(2);

  // 3) If no proposed value → clear adjustment fields and stop
  if (!pv) {
    field.increasing_adjustment_vat = 0;
    field.net_increase = 0;
    return;
  }

  // 4) Quantity + proposed value → compute adjustment & net increase
  const adj = qty * pv;
  const pvsd = adj * (rsd / 100);
  const pvvat = (adj + pvsd) * (rvat / 100);

  let incadj;
  let netinc;
  if (+field.note15 === rvat) {
    incadj = pvvat;
    netinc = vatvalue - pvvat;
  } else {
    incadj = netinc = pvvat;
  }

  field.increasing_adjustment_vat = +incadj.toFixed(2);
  field.net_increase = +netinc.toFixed(2);
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.lc = field.item = field.boe = field.unit = field.date = field.quantity = field.value = field.vat = field.proposed_value = field.increasing_adjustment_vat = field.net_increase = field.reason_ineligibility = "";

  err.bin = err.lc = err.item = err.boe = err.unit = err.date = err.quantity = err.value = err.vat = err.proposed_value = err.increasing_adjustment_vat = err.net_increase = err.reason_ineligibility = "";
};

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    lc_or_invoice_no: field.lc?.title,
    item_id: field.item?.id,
    boe: field.boe?.title,
    unit_id: field.unit?.id,
    date: field.date,
    quantity: field.quantity,
    value: field.value,
    vat: field.vat,
    proposed_value: field.proposed_value,
    increasing_adjustment_vat: field.increasing_adjustment_vat,
    net_increase: field.net_increase,
    reason_ineligibility: field.reason_ineligibility,
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.lc = er.lc_or_invoice_no;
    err.item = er.item_id;
    err.boe = er.boe;
    err.unit = er.unit_id;
    err.date = er.date;
    err.quantity = er.quantity;
    err.value = er.value;
    err.vat = er.vat;
    err.proposed_value = er.proposed_value;
    err.increasing_adjustment_vat = er.increasing_adjustment_vat;
    err.net_increase = er.net_increase;
    err.reason_ineligibility = er.reason_ineligibility;
  };

  delete form['id'];
  router.post(route('form45-create'), form, {
    preserveScroll: true,
    onSuccess: (e) => {
      if (e.props.flash.err) {
        Alert(`<b class='text-danger'>${e.props.flash.err}</b>`);
      } else {
        notify(e.props.flash.msg, 'primary');
        resetForm();
      }
    },
    onError: onErr,
    onStart: () => field.isLoading = true,
    onFinish: () => field.isLoading = false
  });
};

// reload form45
const refresh = () => router.get(route('form45'), {}, { preserveScroll: true });

// const binDD = ref(null);
// const lcDD = ref(null);
// const itemDD = ref(null);
// const boeDD = ref(null);
// const unitDD = ref(null);
// onMounted(() => {
//   const reload = async () => {
//     const jobs = [];

//     if (binDD.value?.reload) {
//       jobs.push(binDD.value.reload());
//     }

//     if (lcDD.value?.reload) {
//       jobs.push(lcDD.value.reload());
//     }

//     if (itemDD.value?.reload) {
//       jobs.push(itemDD.value.reload());
//     }

//     if (boeDD.value?.reload) {
//       jobs.push(boeDD.value.reload());
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