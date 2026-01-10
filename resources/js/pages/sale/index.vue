<template>

  <Head title="Supply" />
  <PageBar>
    <p class="m-0 fs-4">Supply</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <form @submit.prevent="submitData">
    <div class="row g-2">
      <div class="col-12 col-lg-6">
        <div class="card border h-100 mb-2">
          <div class="card-body">
            <div class="row gx-1">
              <div class="col-12 col-sm-4">
                <Input type="text" id="invoice-no" label="invoice no" placeholder="Ex: 0001" v-model="field.invoice_no"
                  :err="err.invoice_no" must readonly />
              </div>
              <div class="col-12 col-sm-8">
                <SelectInfinite ref="binDD" placeholder="Select..." title="BIN" v-model="field.bin"
                  :fetched="fetchedBin" :hood="field.bin?.val?.company_name" :err="err.bin" must />
              </div>
              <div class="col-6 col-sm-4">
                <Input type="date" label="date" placeholder="Ex: 2023-01-01" v-model="field.date" :err="err.date"
                  :hood="field.form621_last_date" must />
              </div>
              <div class="col-6 col-sm-4">
                <SelectInfinite ref="custDD" placeholder="Select..." title="customer" v-model="field.customer"
                  :fetched="fetchedCustomer" :err="err.customer" />
              </div>
              <div class="col-6 col-sm-4">
                <Input type="text" id="name" label="name" maxlength="100" placeholder="Ex: Jhon Doe"
                  v-model="field.name" :err="err.name" />
              </div>
              <div class="col-6 col-sm-4">
                <Input type="text" id="bin-or-nid" label="BIN/NID" maxlength="17" placeholder="Ex: 12345678912412574"
                  v-model="field.bin_or_nid" :err="err.bin_or_nid" />
              </div>
              <div class="col-6 col-sm-4">
                <Input type="text" id="address" label="address" maxlength="150" placeholder="Ex: Tamabil, Sylhet"
                  v-model="field.address" :err="err.address" />
              </div>
              <div class="col-6 col-sm-4">
                <Input type="text" category="decimal" label="grand total" placeholder="Ex: 118.530"
                  v-model="field.grand_total" :hood="`<span>limit: ${field.sale_limit}</span>`" :err="err.grand_total"
                  must readonly />
              </div>
              <div v-if="saleLimitLoaded && +(field.sale_limit) <= +(field.grand_total_note15 || 0)"
                class="col-6 col-sm-4">
                <SelectInfinite placeholder="Select..." title="bank" v-model="field.bank" :fetched="fetchedBank"
                  :err="err.bank" must />
              </div>
              <div v-if="saleLimitLoaded && +(field.sale_limit) <= +(field.grand_total_note15 || 0)"
                class="col-6 col-sm-4">
                <Input type="text" id="transaction-no" label="Transaction No" maxlength="20"
                  placeholder="Ex: 512489654654" v-model="field.transaction_no" :err="err.transaction_no" must />
              </div>
              <div v-if="saleLimitLoaded && +(field.sale_limit) <= +(field.grand_total_note15 || 0)"
                class="col-6 col-sm-4">
                <Input type="date" label="transaction date" placeholder="Ex: 2023-01-01"
                  v-model="field.transaction_date" :err="err.transaction_date" must />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <ClientDetails :view="field.bin?.val" />
      </div>
      <div class="col-12 col-lg-6 mb-2" v-for="(row, index) in items" :key="index">
        <div class=" card card-body h-100">
          <div class="row gx-1">
            <div class="col-12 col-sm-8">
              <SelectInfinite ref="itemsDD" placeholder="Select..." title="item" v-model="row.item"
                :fetched="fetchedItem(index)" :err="itemErr[index]?.item"
                :hood="!empty(row.check43) ? `<span class='text-danger text-lowercase'>${row.check43}</span>` : ''"
                :reset-key="field.bin?.id" must />
            </div>
            <div class="col-6 col-sm-4">
              <Input type="text" category="decimal" label="stock" placeholder="Ex: 541.216" v-model="row.stock"
                :hood="row.form621_sale_unit" :err="itemErr[index]?.stock" must disabled />
            </div>
            <div class="col-6 col-sm-4">
              <Input type="text" category="decimal" label="quantity" placeholder="Ex: 541.216" v-model="row.quantity"
                :err="itemErr[index]?.quantity" :hood="row.form621_sale_unit" @keyup="recompute" must />
            </div>
            <div class="col-6 col-sm-4">
              <Input type="text" category="decimal" label="unit_price" placeholder="Ex: 541.216"
                v-model="row.unit_price"
                :hood="!empty(row.ratemsg) ? `<span class='text-danger'>${row.ratemsg}</span>` : ''"
                :err="itemErr[index]?.unit_price" must readonly />
            </div>
            <div class="col-6 col-sm-4">
              <Input type="text" category="decimal" label="total price" placeholder="Ex: 541.216"
                v-model="row.total_price" :err="itemErr[index]?.total_price" must readonly />
            </div>
            <div class="col-6 col-sm-4">
              <SelectInfinite ref="unitsDD" placeholder="Select..." title="unit" v-model="row.unit"
                :fetched="fetchedUnit" :err="itemErr[index]?.unit" must disabled />
            </div>
            <div class="col-6 col-sm-4">
              <InputGroup type="text" category="decimal" label="SD(%)" maxlength="6" placeholder="Ex: 5%"
                placeholder1="Ex: 5214.25" v-model="row.sd" :err="itemErr[index]?.sd" :calcdata="row.sd_value"
                @keyup="recompute" must />
            </div>
            <div class="col-12 col-sm-4">
              <InputGroup type="text" category="decimal" label="VAT(%)" maxlength="6" placeholder="Ex: 7.5%"
                placeholder1="Ex: 5214.25" v-model="row.vat" :err="itemErr[index]?.vat" :calcdata="row.vat_value"
                @keyup="recompute" must />
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between" v-if="index === (items.length - 1)">
            <div class="d-flex align-items-center">
              <Button type="submit" class="btn btn-primary me-1" label="Save" icon="bi bi-save" title="save"
                :disabled="field.isLoading" />
              <Button type="button" class="btn btn-secondary mx-1" label="Reset" icon="bi bi-stars" title="reset"
                @click="resetForm" />
              <Button type="button" :class="[field.bin?.id ? 'btn btn-success' : 'btn btn-secondary']"
                icon="bi bi-plus-square" title="add new" @click="addItem" :disabled="!field.bin?.id" />
            </div>
            <div class="d-flex align-items-center">
              <Button type="button" :class="[field.bin?.id ? 'btn btn-secondary' : 'btn btn-outline-secondary']"
                icon="bi bi-dash-square" title="remove" :disabled="items.length === 1" @click="removeItem(index)" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script setup>
import axios from "axios";
import moment from "moment-timezone";
import { ref, reactive, inject, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import { empty } from "../../helpers/utils";
import { user, role } from "../../composables/user";

import PageBar from "../../components/PageBar.vue";
import Refresh from "../../components/Refresh.vue";
import Button from "../../components/Button.vue";
import Input from "../../components/Input.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";
import ClientDetails from "../sale/ClientDetails.vue";
import InputGroup from "../../components/InputGroup.vue";

import Confirm from "../../helpers/customConfirm";
import Alert from "../../helpers/customAlert";

const props = defineProps({
  sale: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

const field = reactive({ id: '', invoice_no: '', bin: '', date: '', customer: '', mobile: '', name: '', bin_or_nid: '', address: '', grand_total: '', bank: '', transaction_no: '', transaction_date: '', form621_last_date: '', sale_limit: '', note15: '', isLoading: false });

const err = reactive({ invoice_no: '', bin: '', date: '', customer: '', mobile: '', name: '', bin_or_nid: '', address: '', grand_total: '', bank: '', transaction_no: '', transaction_date: '' });

const emptyItem = () => ({ item: null, stock: '', quantity: '', unit_price: '', total_price: '', unit: '', sd: '', sd_value: '', vat: '', vat_value: '', form621_sale_unit: '', rate_vat: '' });
const emptyItemErr = () => ({ item: '', stock: '', quantity: '', unit_price: '', total_price: '', unit: '', sd: '', sd_value: '', vat: '', vat_value: '' });

const items = ref([emptyItem()]);
const itemErr = ref([emptyItemErr()]);

// reset form
const resetForm = () => {
  Object.assign(field, { id: '', invoice_no: '', bin: '', date: '', customer: '', mobile: '', name: '', bin_or_nid: '', address: '', grand_total: '', bank: '', transaction_no: '', transaction_date: '', form621_last_date: '', isLoading: false });
  Object.assign(err, { invoice_no: '', bin: '', date: '', customer: '', mobile: '', name: '', bin_or_nid: '', address: '', grand_total: '', bank: '', transaction_no: '', transaction_date: '' });
  items.value = [emptyItem()];
  itemErr.value = [emptyItemErr()];
};

// add new item block
const addItem = () => {
  items.value.push(emptyItem());
};

// remove an Item
const removeItem = (index) => {
  if (items.value.length > 1) {
    items.value.splice(index, 1);
  }
};

// const last invoice
const saleLimitLoaded = ref(false);
const loadSaleLimit = async () => await axios.get(route('sale-limit')).then((res) => {
  field.sale_limit = res.data?.salelimit ?? "";
  field.note15 = res.data?.note15 ?? "";
  saleLimitLoaded.value = true;
});

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin }),
    option: async (row) => {
      const [rLastInv, r621] = await Promise.all([
        axios.get(route('last-invoice'), { params: { client_id: row?.id } }),
        axios.get(route('form621-last'), { params: { client_id: row?.id } })
      ]);

      field.date = r621.data?.lastdate ?? "";
      field.form621_last_date = !empty(r621.data?.lastdate) ? moment(r621.data?.lastdate).format("DD MMM YYYY") : "";
      field.invoice_no = rLastInv?.data?.lastinvoice ?? ""; // reset invoice no on bin change
    }
  };
};

// customer dropdown
const fetchedCustomer = async () => {
  return {
    url: route('customer-dropdown'),
    data: 'customer',
    mapFn: (customer) => ({ id: customer.id, title: customer.mobile, val: customer }),
    option: (row) => {
      field.name = row?.val?.customer_name ?? "";
      field.bin_or_nid = row?.val?.bin_or_nid ?? "";
      field.address = row?.val?.address ?? "";
    }
  };
};

// item dropdown
const fetchedItem = (index) => {
  return async () => { // SelectInfinite needs a function returning a Promise
    return {
      url: route('client-item-dropdown'),
      data: 'item',
      params: { client_id: field.bin?.id },
      mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` }),
      option: async (row) => await setPromiseData(index, row)
    };
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

// one-liner to blank all calculated fields
const clearRowCalcs = (target) => {
  target.item = null;
  target.unit = target.stock = target.form621_sale_unit = target.quantity = target.unit_price = target.total_price = target.sd = target.sd_value = target.vat = target.vat_value = field.grand_total = field.customer = field.bin_or_nid = field.address = field.form621_last_date = target.ratemsg = target.check43 = "";
};
// set and call last client and item data
const setPromiseData = async (index, row) => {
  const param = { client_id: field.bin?.id, item_id: row?.id };

  const target = items.value[index];
  if (!target) return;

  if (!param.client_id || !param.item_id) {
    clearRowCalcs(target);
    return;
  }

  const [rRate, r621] = await Promise.all([
    axios.get(route('rate-last'), { params: param }),
    axios.get(route('form621-last'), { params: param }),
  ]);

  const res621 = r621.data;

  target.unit = res621?.form621last?.unit ? { id: res621.form621last.unit.id, title: res621.form621last.unit.name } : "";
  target.stock = res621?.form621last?.stock ?? "";
  target.form621_sale_unit = res621?.form621last?.unit?.name ?? "";

  const rate = rRate?.data?.ratelast ?? "";
  target.unit_price = rate?.unit_price ?? "";
  target.sd = rate?.sd ?? "";
  target.sd_value = rate?.sd_value ?? "";
  target.vat = rate?.vat ?? "";
  target.vat_value = rate?.vat_value ?? "";
  target.ratemsg = rRate?.data?.ratemsg ?? "";
  target.check43 = rRate?.data?.message ?? "";
  target.rate_vat = rate?.vat ?? "";

  recompute(row);
};

/**
 * total_price = quantity * unit_price
 * sd_value    = total_price * (sd / 100)
 * vat_value   = (total_price + sd_value) * (vat / 100)
 */
const recompute = () => {
  let gtotalAll = 0;
  let gtotalNote15 = 0;

  const note15 = +(field.note15) || 0;

  items.value.forEach((row) => {
    const qty = +(row.quantity) || 0;
    const up = +(row.unit_price) || 0;
    const sd = +(row.sd) || 0;
    const vat = +(row.vat) || 0;
    const tare = +(row.rate_vat) || 0;   // from rate-last

    const itemPrice = +(qty * up).toFixed(2);

    row.sd_value = +(itemPrice * (sd / 100)).toFixed(2);
    row.vat_value = +((itemPrice + row.sd_value) * (vat / 100)).toFixed(2);
    row.total_price = +(itemPrice + row.sd_value + row.vat_value).toFixed(2);

    // all items total (for display)
    gtotalAll += row.total_price;

    // 🔹 backend rule: item is "VAT 15" if either vat or tare equals note15
    const isVat15 = note15 > 0 && (vat === note15 || tare === note15);

    if (isVat15) {
      gtotalNote15 += row.total_price;
    }
  });

  field.grand_total = gtotalAll.toFixed(2);
  field.grand_total_note15 = gtotalNote15.toFixed(2);   // 🔹 new
};

// create or update branch
const submitData = async () => {
  const form = {
    id: field.id,
    client_id: field.bin?.id,
    date: field.date,
    customer_mobile: field.customer?.title,
    customer_name: field.name,
    bin_or_nid: field.bin_or_nid,
    address: field.address,
    bank_id: field.bank?.id,
    transaction_no: field.transaction_no,
    transaction_date: field.transaction_date,
    items: items.value.map((row) => ({
      item_id: row.item?.id,
      quantity: row.quantity,
      unit_price: row.unit_price,
      unit_id: row.unit?.id,
      sd: row.sd,
      vat: row.vat,
    }))
  };

  const onErr = (errors) => {
    if (errors?.items && typeof errors?.items === 'string') {
      Alert(`<span class="text-danger">${errors.items}</span>`);
      return;
    }

    let er = {};
    Object.keys(errors).map((key) => { er[key] = errors[key].toString(); });
    err.invoice_no = er.invoice_no;
    err.bin = er.client_id;
    err.date = er.date;
    err.customer = er.customer_id;
    err.name = er.customer_name;
    err.bin_or_nid = er.bin_or_nid;
    err.address = er.address;
    err.grand_total = er.grand_total;
    err.bank = er.bank_id;
    err.transaction_no = er.transaction_no;
    err.transaction_date = er.transaction_date;

    // reset item errors (same length as items)
    itemErr.value = items.value.map(() => emptyItemErr());

    // item errors: items.0.item_id, items.1.quantity, ...
    Object.keys(er).forEach((key) => {
      if (!key.startsWith('items.')) return;

      const parts = key.split('.'); // ['items', '0', 'item_id']
      const index = Number(parts[1]);
      const itemFieldErr = parts[2];
      if (isNaN(index)) return;

      if (!itemErr.value[index]) {
        itemErr.value[index] = emptyItemErr();
      }

      if (itemFieldErr === 'item_id') itemErr.value[index].item_id = er[key];
      else if (itemFieldErr === 'unit_id') itemErr.value[index].unit_id = er[key];
      else if (itemFieldErr === 'quantity') itemErr.value[index].quantity = er[key];
      else if (itemFieldErr === 'unit_price') itemErr.value[index].unit_price = er[key];
      else if (itemFieldErr === 'total_price') itemErr.value[index].total_price = er[key];
      else if (itemFieldErr === 'sd') itemErr.value[index].sd = er[key];
      else if (itemFieldErr === 'sd_value') itemErr.value[index].sd_value = er[key];
      else if (itemFieldErr === 'vat') itemErr.value[index].vat = er[key];
      else if (itemFieldErr === 'vat_value') itemErr.value[index].vat_value = er[key];
      else if (itemFieldErr === 'stock') itemErr.value[index].stock = er[key];
    });
  };

  ['id'].forEach((k) => delete form[k]);
  router.post(route('sale-create'), form, {
    preserveScroll: true,
    onSuccess: (e) => {
      if (e.props.flash.err) {
        Alert(`<span class="text-danger">${e.props.flash.err}</span>`);
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

// reload sale
const refresh = () => router.get(route('sale'), {}, { preserveScroll: true });

onMounted(async () => await loadSaleLimit());
</script>