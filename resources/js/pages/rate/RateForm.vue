<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12 col-md-5">
            <SelectInfinite placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" must />
          </div>
          <div class="col-12 col-md-7">
            <SelectInfinite placeholder="Select..." title="Item" v-model="field.item" :fetched="fetchedItem"
              :err="err.item" :hood="`<span class='text-lowercase text-danger'>${field.check43}</span>`"
              :reset-key="field.bin?.id" must />
          </div>
          <div class="col-6 col-md-3 col-lg-12">
            <Input type="date" id="date" label="date" v-model="field.date" :err="err.date" must />
          </div>
          <div class="col-6 col-md-3 col-lg-6">
            <Input type="text" category="decimal" id="unit-price" label="unit price" placeholder="Ex: 1600"
              v-model="field.unit_price" :hood="field.last_purchase_rate" :err="err.unit_price" must />
          </div>
          <div class="col-6 col-md-3 col-lg-6">
            <Input type="text" category="decimal" id="sd" label="SD(%)" placeholder="Ex: 5%" maxlength="6"
              v-model="field.sd" :err="err.sd" must />
          </div>
          <div class="col-6 col-md-3 col-lg-6">
            <Input type="text" category="decimal" id="vat" label="VAT(%)" placeholder="Ex: 15%" maxlength="6"
              v-model="field.vat" :err="err.vat" must />
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
import { ref, reactive, inject, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import { empty } from "../../helpers/utils";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";
import axios from "axios";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// rate field
const field = reactive({ id: '', bin: '', item: '', date: '', unit_price: '', sd: '', vat: '', last_purchase_rate: '', check43: '', isLoading: false });
const err = reactive({ bin: '', item: '', date: '', unit_price: '', sd: '', vat: '' });

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
    url: route('client-item-dropdown'),
    data: 'item',
    params: { client_id: field.bin?.id },
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` }),
    option: async () => empty(field.id) && await lastForm43Load()
  };
};

const lastForm43Load = async () => {
  const param = { client_id: field.bin?.id, item_id: field.item?.id };
  if (!param.client_id || !param.item_id) {
    field.last_purchase_rate = "";
    field.check43 = "";
    return;
  }
  const { data } = await axios.get(route('rate-last', { client_id: field.bin?.id, item_id: field.item?.id }));
  field.last_purchase_rate = data?.form43last?.unit_price ?? "";
  field.check43 = data?.message ?? "";
};

// --- Expose API for parent ---
const setForm = async (data = {}) => {
  const row = data?.rate;
  field.id = row.id ?? "";
  field.bin = !empty(row.client) ? { id: row.client?.id, title: row.client?.bin, val: row.client } : "";
  await nextTick(); // selectinfinite always reset and clear. it is stop clear child visual when click edit
  field.item = !empty(row.item) ? { id: row.item?.id, title: `${row.item?.name} [${row.item?.hs_code}]` } : "";
  await nextTick(); // selectinfinite always reset and clear. it is stop clear child visual when click edit
  field.last_purchase_rate = data?.form43last?.unit_price ?? "";
  field.date = row.date ?? "";
  field.unit_price = row.unit_price ?? "";
  field.sd = row.sd ?? "";
  field.vat = row.vat ?? "";
  // clear previous errors when setting a record
  err.bin = err.item = err.date = err.unit_price = err.sd = err.vat = "";
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.item = field.date = field.unit_price = field.sd = field.vat = err.bin = err.item = err.date = err.unit_price = err.sd = err.vat = field.last_purchase_rate = field.check43 = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    item_id: field.item?.id,
    date: field.date,
    unit_price: field.unit_price,
    sd: field.sd,
    vat: field.vat
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.item = er.item_id;
    err.date = er.date;
    err.unit_price = er.unit_price;
    err.sd = er.sd;
    err.vat = er.vat;
  };

  if (field.id) {
    router.put(route('rate-update', field.id), form, {
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
    router.post(route('rate-create'), form, {
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