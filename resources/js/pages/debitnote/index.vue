<template>

  <Head title="Debit Note" />
  <PageBar>
    <p class="m-0 fs-4">Debit Note</p>
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
              <div class="col-12 col-sm-7 col-md-7 col-lg-8">
                <SelectInfinite ref="binDD" placeholder="Select..." title="BIN" v-model="field.bin"
                  :fetched="fetchedBin" :hood="field.bin?.val?.company_name" :err="err.bin" must />
              </div>
              <div class="col-12 col-sm-5 col-md-5 col-lg-4">
                <SelectInfinite ref="lcDD" placeholder="Select..." title="LC" v-model="field.lc" :fetched="fetchedLC"
                  :err="err.lc" :reset-key="field.bin?.id" must />
              </div>
              <div class="col-12 col-sm-8 col-md-8 col-lg-6">
                <SelectInfinite ref="itemDD" placeholder="Select..." title="item" v-model="field.item"
                  :fetched="fetchedItem" :reset-key="field.lc?.title" :err="err.item" must />
              </div>
              <div class="col-12 col-sm-4 col-md-4 col-lg-3">
                <SelectInfinite ref="boeDD" placeholder="Select..." title="BoE" v-model="field.boe"
                  :fetched="fetchedBoe" :reset-key="field.item?.id" :err="err.boe" must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="date" id="issued-date" label="issued date" v-model="field.issued_date"
                  :hood="field.form621_last_date" :err="err.issued_date" must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="date" id="challan-date" label="challan date" v-model="field.vat_challan_date"
                  :err="err.vat_challan_date" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="text" category="decimal" id="value" label="value" placeholder="Ex: 152.25"
                  v-model="field.value_in_challan" :err="err.value_in_challan" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="text" category="decimal" id="stock" label="stock" placeholder="Ex: 5623"
                  v-model="field.stock" :err="err.stock" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="text" category="decimal" id="quantity" label="quantity" placeholder="Ex: 89.25"
                  v-model="field.quantity_in_challan" :err="err.quantity_in_challan" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-4">
                <SelectInfinite ref="unitDD" placeholder="Select..." title="Unit" v-model="field.unit"
                  :fetched="fetchedUnit" :err="err.unit" disabled must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-4">
                <Input type="text" category="decimal" id="vat" label="VAT" placeholder="Ex: 1598.87"
                  v-model="field.vat_in_challan" :err="err.vat_in_challan" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-4">
                <Input type="text" category="decimal" id="sd" label="SD" placeholder="Ex: 520.74"
                  v-model="field.sd_in_challan" :err="err.sd_in_challan" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="text" category="decimal" id="increase-quantity" label="decrease quantity"
                  placeholder="Ex: 214.25" v-model="field.quantity_of_increasing_adjustment"
                  :err="err.quantity_of_increasing_adjustment" @keyup="recompute" must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="text" category="decimal" id="increase-value" label="increase value"
                  placeholder="Ex: 554.21" v-model="field.value_of_increasing_adjustment"
                  :err="err.value_of_increasing_adjustment" @keyup="recompute" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="text" category="decimal" id="increase-vat" label="increase vat" placeholder="Ex: 354.25"
                  v-model="field.vat_of_increasing_adjustment" :err="err.vat_of_increasing_adjustment"
                  @keyup="recompute" readonly must />
              </div>
              <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <Input type="text" category="decimal" id="increase-sd" label="increase SD" placeholder="Ex: 654.14"
                  v-model="field.sd_of_increasing_adjustment" :err="err.sd_of_increasing_adjustment" @keyup="recompute"
                  readonly must />
              </div>
              <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                <TextArea type="text" id="reason" label="reason" maxlength="150" style="height: 112px !important;"
                  placeholder="Ex: Reason" v-model="field.reason_for_issuance" :err="err.reason_for_issuance" must="" />
              </div>
              <div class="col-6">
                <TextArea type="text" label="remarks" maxlength="250" style="height: 112px !important;"
                  placeholder="Ex: Remarks" v-model="field.remarks" :err="err.remarks" />
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
import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";
import TextArea from "../../components/TextArea.vue";
import ClientDetails from "./ClientDetails.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  creditnote: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// creditnote field
const field = reactive({ id: '', bin: '', lc: '', boe: '', item: '', issued_date: '', vat_challan_date: '', value_in_challan: '', quantity_in_challan: '', unit: '', vat_in_challan: '', sd_in_challan: '', value_of_increasing_adjustment: '', quantity_of_increasing_adjustment: '', vat_of_increasing_adjustment: '', sd_of_increasing_adjustment: '', reason_for_issuance: '', remarks: '', purchase_unit_price: '', purchase_sd: '', purchase_vat: '', form621_last_date: '', isLoading: false });

const err = reactive({ bin: '', lc: '', item: '', boe: '', issued_date: '', vat_challan_date: '', value_in_challan: '', quantity_in_challan: '', unit: '', vat_in_challan: '', sd_in_challan: '', value_of_increasing_adjustment: '', quantity_of_increasing_adjustment: '', vat_of_increasing_adjustment: '', sd_of_increasing_adjustment: '', reason_for_issuance: '', remarks: '' });

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
  field.boe = field.vat_challan_date = field.value_in_challan = field.stock = field.quantity_in_challan = field.unit = field.vat_in_challan = field.sd_in_challan = field.value_of_increasing_adjustment = field.vat_of_increasing_adjustment = field.sd_of_increasing_adjustment = field.form621_last_date = "";
};

const setPromiseData = async () => {
  const param = { client_id: field.bin?.id, item_id: field.item?.id, lc_or_invoice_no: field.lc?.title, boe: field.boe?.title };

  if (!param.client_id || !param.lc_or_invoice_no || !param.item_id || !param.boe) {
    clearCalcs();
    return;
  }

  const { data } = await axios.get(route('form621-client-item-lc-boe'), { params: param });
  const res = data?.debitnote;
  field.vat_challan_date = res?.date ?? "";
  field.value_in_challan = res?.purchase?.base_value ?? "";
  field.unit = !empty(res?.unit) ? { id: res?.unit?.id, title: res?.unit?.name } : "";
  field.vat_in_challan = res?.purchase?.vat_value ?? "";
  field.sd_in_challan = res?.purchase?.sd_value ?? "";
  field.quantity_in_challan = res?.purchase?.total_quantity ?? "";
  field.purchase_sd = res?.purchase?.sd ?? "";
  field.purchase_vat = res?.purchase?.vat ?? "";
  field.purchase_unit_price = res?.purchase?.unit_price ?? "";
  field.stock = data?.form621laststock?.stock ?? "";
  field.form621_last_date = moment(data?.form621lastdate).format("DD MMM YYYY") ?? "";
};

const recompute = async () => {
  const qty = +(field.quantity_of_increasing_adjustment);
  const pup = +(field.purchase_unit_price);
  const purchasesd = +(field.purchase_sd);
  const purchasevat = +(field.purchase_vat);

  const price = qty * pup;
  const sdvalue = +(price * (purchasesd / 100));
  const vatvalue = +((price + sdvalue) * (purchasevat / 100));

  if (!qty) {
    field.sd_of_increasing_adjustment = "";
    field.vat_of_increasing_adjustment = "";
    field.value_of_increasing_adjustment = "";
    return;
  }

  field.sd_of_increasing_adjustment = +sdvalue.toFixed(2);
  field.vat_of_increasing_adjustment = +vatvalue.toFixed(2);
  field.value_of_increasing_adjustment = +price.toFixed(2);
};

// reset form
const resetForm = () => {
  field.id = field.invoice = field.bin = field.item = field.unit = field.issued_date = field.vat_challan_no = field.vat_challan_date = field.reason_for_issuance = field.value_in_challan = field.quantity_in_challan = field.vat_in_challan = field.sd_in_challan = field.value_of_increasing_adjustment = field.quantity_of_increasing_adjustment = field.vat_of_increasing_adjustment = field.sd_of_increasing_adjustment = field.remarks = field.purchase_unit_price = field.purchase_sd = field.purchase_vat = field.form621_last_date = "";

  err.invoice = err.bin = err.item = err.unit = err.issued_date = err.vat_challan_no = err.vat_challan_date = err.reason_for_issuance = err.value_in_challan = err.quantity_in_challan = err.vat_in_challan = err.sd_in_challan = err.value_of_increasing_adjustment = err.quantity_of_increasing_adjustment = err.vat_of_increasing_adjustment = err.sd_of_increasing_adjustment = err.remarks = "";
};

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    lc_or_invoice_no: field.lc?.title,
    client_id: field.bin?.id,
    item_id: field.item?.id,
    boe: field.boe?.title,
    unit_id: field.unit?.id,
    issued_date: field.issued_date,
    vat_challan_date: field.vat_challan_date,
    value_in_challan: field.value_in_challan,
    quantity_in_challan: field.quantity_in_challan,
    vat_in_challan: field.vat_in_challan,
    sd_in_challan: field.sd_in_challan,
    value_of_increasing_adjustment: field.value_of_increasing_adjustment,
    quantity_of_increasing_adjustment: field.quantity_of_increasing_adjustment,
    vat_of_increasing_adjustment: field.vat_of_increasing_adjustment,
    sd_of_increasing_adjustment: field.sd_of_increasing_adjustment,
    reason_for_issuance: field.reason_for_issuance,
    remarks: field.remarks
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.invoice = er.lc_or_invoice_no;
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

  delete form['id'];
  router.post(route('debitnote-create'), form, {
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

// reload creditnote
const refresh = () => router.get(route('creditnote'), {}, { preserveScroll: true });

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