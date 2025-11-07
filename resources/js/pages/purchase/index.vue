<template>

  <Head title="Purchase" />
  <PageBar>
    <p class="m-0 fs-4">Purchase</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row gx-2">
    <div class="col-8">
      <div class="card border h-100">
        <div class="card-body">
          <form>
            <div class="row gx-1">
              <div class="col-12">
                <Input type="hidden" v-model="field.id" readonly />
              </div>
              <div class="col-5">
                <SelectInfinite placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
                  :hood="field.bin?.val?.company_name" :err="err.bin" must />
              </div>
              <div class="col-7">
                <SelectInfinite placeholder="Select..." title="item" v-model="field.item" :fetched="fetchedItem"
                  :hood="field.item?.val?.company_name" :err="err.item" must />
              </div>
              <div class="col-3">
                <Input type="text" id="lc" label="LC no" maxlength="50" placeholder="Ex: 5124845487"
                  v-model="field.lc_no" :err="err.lc_no" must />
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
                  :err="err.entry_date" must />
              </div>
              <div class="col-3">
                <SelectInfinite placeholder="Select..." title="station" v-model="field.stationcode"
                  :fetched="fetchedStationcode" :err="err.stationcode" must />
              </div>
              <div class="col-3">
                <SelectInfinite placeholder="Select..." title="mushak note" v-model="field.mushaknote"
                  :fetched="fetchedMushaknote" :err="err.mushaknote" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="DF/CVAT/CF" placeholder="Ex: 5214.215"
                  v-model="field.df_cvat_cf" :err="err.df_cvat_cf" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="assessable value" placeholder="Ex: 5214.215"
                  v-model="field.assessable_value" :err="err.assessable_value" @keyup="baseValueCalc" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="quantity" placeholder="Ex: 541.216"
                  v-model="field.quantity" :err="err.quantity" @keyup="netQtyCalc" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="extra quantity" placeholder="Ex: 5247.216"
                  v-model="field.extra_quantity" :err="err.extra_quantity" @keyup="netQtyCalc" must />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" maxlength="6" label="wastage(%)" placeholder="Ex: 3.5"
                  v-model="field.wastage" :err="err.wastage" must readonly />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="total quantity" placeholder="Ex: 3254.216"
                  v-model="field.total_quantity" :err="err.total_quantity" @keyup="netQtyCalc" must readonly />
              </div>
              <div class="col-3">
                <SelectInfinite placeholder="Select..." title="unit" v-model="field.unit" :fetched="fetchedUnit"
                  :err="err.stationcode" must disabled />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="base value" placeholder="Ex: 521423.215"
                  v-model="field.base_value" :err="err.base_value" must readonly />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" label="unit price" placeholder="Ex: 118.530"
                  v-model="field.unit_price" :err="err.unit_price" :hood="field.form43specific_unit_price" must
                  readonly />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="CD(%)" maxlength="6" placeholder="Ex: 5%"
                  placeholder1="Ex: 5214.25" v-model="field.cd" :err="err.cd" :calcdata="field.cdvalue" @keyup="cdCalc"
                  must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="RD(%)" maxlength="6" placeholder="Ex: 5%"
                  placeholder1="Ex: 5214.25" v-model="field.rd" :err="err.rd" :calcdata="field.rdvalue" @keyup="rdCalc"
                  must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="SD(%)" maxlength="6" placeholder="Ex: 5%"
                  placeholder1="Ex: 5214.25" v-model="field.sd" :err="err.sd" :calcdata="field.sdvalue" @keyup="sdCalc"
                  must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="VAT(%)" maxlength="6" placeholder="Ex: 7.5%"
                  placeholder1="Ex: 5214.25" v-model="field.vat" :err="err.vat" :calcdata="field.vatvalue"
                  @keyup="vatCalc" must />
              </div>
              <div class="col-3">
                <InputGroup type="text" category="decimal" label="AT(%)" maxlength="6" placeholder="Ex: 7.5%"
                  placeholder1="Ex: 5214.25" v-model="field.at" :err="err.at" :calcdata="field.atvalue" @keyup="atCalc"
                  must />
              </div>
              <div class="col-3">
                <Input type="text" id="assesment-no" label="Assesment No" maxlength="20" placeholder="Ex: 512489654654"
                  v-model="field.assesment_number" :err="err.assesment_number" />
              </div>
              <div class="col-3">
                <Input type="date" label="assesment date" placeholder="Ex: 2023-01-01" v-model="field.assesment_date"
                  :err="err.assesment_date" />
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
      <ClientDetails :view="field.bin?.val" />
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, reactive, inject } from "vue";
import { router } from "@inertiajs/vue3";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import Input from "../../components/Input.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";
import InputGroup from "../../components/InputGroup.vue";
import TextArea from '../../components/TextArea.vue';
import ClientDetails from './ClientDetails.vue';

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  purchase: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

// purchase field
const field = reactive({ id: '', bin: '', item: '', lc_no: '', lc_date: '', boe: '', boe_date: '', entry_date: '', stationcode: '', mushaknote: '', df_cvat_cf: '', assessable_value: '', quantity: '', extra_quantity: '', wastage: '', total_quantity: '', unit: '', base_value: '', unit_price: '', cd: '', cd_value: '', rd: '', rd_value: '', sd: '', sd_value: '', vat: '', vat_value: '', at: '', at_value: '', assesment_number: '', assesment_date: '', receipt_number: '', receipt_date: '', exporter_name: '', address: '' });

const err = reactive({ bin: '', item: '', lc_no: '', lc_date: '', boe: '', boe_date: '', entry_date: '', stationcode: '', mushaknote: '', df_cvat_cf: '', assessable_value: '', quantity: '', extra_quantity: '', wastage: '', total_quantity: '', unit: '', base_value: '', unit_price: '', cd: '', rd: '', sd: '', vat: '', at: '', assesment_number: '', assesment_date: '', receipt_number: '', receipt_date: '', exporter_name: '', address: '' });

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin })
  };
};

// item dropdown
const fetchedItem = async () => {
  return {
    url: route('item-dropdown'),
    data: 'item',
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` })
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

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.bin = !emptyObj(row.client) ? { id: row.client?.id, title: row.client?.bin } : "";
  field.item = !emptyObj(row.item) ? { id: row.item?.id, title: row.item?.name } : "";
  field.lc_no = row.lc_no ?? "";
  field.lc_date = row.lc_date ?? "";
  field.boe = row.boe ?? "";
  field.boe_date = row.boe_date ?? "";
  field.entry_date = row.entry_date ?? "";
  field.stationcode = !emptyObj(row.stationcode) ? { id: row.stationcode?.id, title: row.stationcode?.code } : "";
  field.mushaknote = !emptyObj(row.mushaknote) ? { id: row.mushaknote?.id, title: row.mushaknote?.name_note_no } : "";
  field.df_cvat_cf = row.df_cvat_cf ?? "";
  field.assessable_value = row.assessable_value ?? "";
  field.quantity = row.quantity ?? "";
  field.extra_quantity = row.extra_quantity ?? "";
  field.wastage = row.wastage ?? "";
  field.total_quantity = row.total_quantity ?? "";
  field.unit = !emptyObj(row.unit) ? { id: row.unit?.id, title: row.unit?.name_note_no } : "";
  field.base_value = row.base_value ?? "";
  field.quantity = row.quantity ?? "";
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
  field.assesment_number = row.assesment_number ?? "";
  field.assesment_date = row.assesment_date ?? "";
  field.receipt_number = row.receipt_number ?? "";
  field.receipt_date = row.receipt_date ?? "";
  field.exporter_name = row.exporter_name ?? "";
  field.address = row.address ?? "";

  // clear previous errors when setting a record
  err, bin = err.item = err.lc_no = err.lc_date = err.boe = err.boe_date = err.entry_date = err.stationcode = err.mushaknote = err.df_cvat_cf = err.assessable_value = err.quantity = err.extra_quantity = err.wastage = err.total_quantity = err.unit = err.base_value = err.unit_price = err.cd = err.rd = err.sd = err.vat = err.at = err.assesment_number = err.assesment_date = err.receipt_number = err.receipt_date = err.exporter_name = err.address = "";
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.item = field.lc_no = field.lc_date = field.boe = field.boe_date = field.entry_date = field.stationcode = field.mushaknote = field.df_cvat_cf = field.assessable_value = field.quantity = field.extra_quantity = field.wastage = field.total_quantity = field.unit = field.base_value = field.unit_price = field.cd = field.cd_value = field.rd = field.rd_value = field.sd = field.sd_value = field.vat = field.vat_value = field.at = field.at_value = field.assesment_number = field.assesment_date = field.receipt_number = field.receipt_date = field.exporter_name = field.address = "";

  err.bin = err.item = err.lc_no = err.lc_date = err.boe = err.boe_date = err.entry_date = err.stationcode = err.mushaknote = err.df_cvat_cf = err.assessable_value = err.quantity = err.extra_quantity = err.wastage = err.total_quantity = err.unit = err.base_value = err.unit_price = err.cd = err.rd = err.sd = err.vat = err.at = err.assesment_number = err.assesment_date = err.receipt_number = err.receipt_date = err.exporter_name = err.address = "";
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
    stationcode: field.stationcode,
    mushaknote_id: field.mushaknote?.id,
    df_cvat_cf: field.df_cvat_cf,
    assessable_value: field.assessable_value,
    quantity: field.quantity,
    extra_quantity: field.extra_quantity,
    wastage: field.wastage,
    total_quantity: field.total_quantity,
    unit_id: field.unit?.id,
    base_value: field.base_value,
    unit_price: field.unit_price,
    cd: field.cd,
    rd: field.rd,
    sd: field.sd,
    vat: field.vat,
    at: field.at,
    assesment_number: field.assesment_number,
    assesment_date: field.assesment_date,
    receipt_number: field.receipt_number,
    receipt_date: field.receipt_date,
    exporter_name: field.exporter_name,
    address: field.address,
  };

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
    err.stationcode = er.stationcode;
    err.mushaknote = er.mushaknote_id;
    err.df_cvat_cf = er.df_cvat_cf;
    err.assessable_value = er.assessable_value;
    err.quantity = er.quantity;
    err.extra_quantity = er.extra_quantity;
    err.wastage = er.wastage;
    err.total_quantity = er.total_quantity;
    err.unit = er.unit;
    err.base_value = er.base_value;
    err.unit_price = er.unit_price;
    err.cd = er.cd;
    err.rd = er.rd;
    err.sd = er.sd;
    err.vat = er.val;
    err.at = er.at;
    err.assesment_number = er.assesment_number;
    err.assesment_date = er.assesment_date;
    err.receipt_number = er.receipt_number;
    err.receipt_date = er.receipt_date;
    err.exporter_name = er.exporter_name;
    err.address = er.address;
  };

  if (field.id) {
    router.put(route('purchase-update', field.id), form, {
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

    router.post(route('purchase-create'), form, {
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

// reload division
const refresh = () => router.get(route('purchase'), {}, { preserveScroll: true });
</script>