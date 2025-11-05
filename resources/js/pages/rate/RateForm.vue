<template>
  <div class="card border h-100">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12">
            <Input type="hidden" v-model="field.id" readonly />
          </div>
          <div class="col-12">
            <SelectInfinite placeholder="Select..." title="BIN" label="title" v-model="field.bin" :fetched="fetchedBin"
              :hood="field.bin?.val?.company_name" :err="err.bin" must />
          </div>
          <div class="col-12">
            <SelectInfinite placeholder="Select..." title="Item" label="title" v-model="field.item"
              :fetched="fetchedItem" :err="err.item" must />
          </div>
          <div class="col-6">
            <Input type="date" id="date" label="date" v-model="field.date" :err="err.date" must />
          </div>
          <div class="col-6">
            <Input type="text" category="decimal" id="unit-price" label="unit price" placeholder="Ex: 1600"
              v-model="field.unit_price" :err="err.unit_price" must />
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
import { emptyObj } from "../../helpers/utils";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// rate field
const field = reactive({ id: '', bin: '', item: '', date: '', unit_price: '', isLoading: false });
const err = reactive({ bin: '', item: '', date: '', unit_price: '' });

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

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.bin = !emptyObj(row.client) ? { id: row.client?.id, title: row.client?.bin } : "";
  field.item = !emptyObj(row.item) ? { id: row.item?.id, title: row.item?.name } : "";
  field.date = row.date ?? "";
  field.unit_price = row.unit_price ?? "";
  // clear previous errors when setting a record
  err.bin = err.item = err.date = err.unit_price = "";
};

// reset form
const resetForm = () => {
  field.id = field.bin = field.item = field.date = field.unit_price = err.bin = err.item = err.date = err.unit_price = "";
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
    unit_price: field.unit_price
  };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.item = er.item_id;
    err.date = er.date;
    err.unit_price = er.unit_price;
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