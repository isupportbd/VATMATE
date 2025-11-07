<template>

  <Head title="Supply" />
  <PageBar>
    <p class="m-0 fs-4">Supply</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <form>
    <div class="row gx-2">
      <div class="col-6">
        <div class="card border mb-2">
          <div class="card-body">
            <div class="row gx-1">
              <div class="col-4">
                <Input type="text" id="invoice-no" label="invoice no" placeholder="Ex: 0001" v-model="field.invoice_no"
                  :err="err.invoice_no" must readonly />
              </div>
              <div class="col-8">
                <SelectInfinite placeholder="Select..." title="BIN" v-model="field.bin" :fetched="fetchedBin"
                  :hood="field.bin?.val?.company_name" :err="err.bin" must />
              </div>
              <div class="col-4">
                <Input type="date" label="date" placeholder="Ex: 2023-01-01" v-model="field.date" :err="err.date"
                  must />
              </div>
              <div class="col-4">
                <SelectInfinite placeholder="Select..." title="customer" v-model="field.customer"
                  :fetched="fetchedCustomer" :err="err.customer" />
              </div>
              <div class="col-4">
                <Input type="text" id="bin-nid" label="BIN/NID" maxlength="17" placeholder="Ex: 15243257841214562"
                  v-model="field.bin_nid" :err="err.bin_nid" />
              </div>
              <div class="col-8">
                <Input type="text" id="address" label="address" maxlength="150" placeholder="Ex: Tamabil, Sylhet"
                  v-model="field.address" :err="err.address" />
              </div>
              <div class="col-4">
                <Input type="text" category="decimal" label="grand total" placeholder="Ex: 118.530"
                  v-model="field.grand_total" :err="err.grand_total" must readonly />
              </div>
              <div class="col-4">
                <SelectInfinite placeholder="Select..." title="bank" v-model="field.bank" :fetched="fetchedBank"
                  :err="err.bank" must />
              </div>
              <div class="col-4">
                <Input type="text" id="transaction-no" label="Transaction No" maxlength="20"
                  placeholder="Ex: 512489654654" v-model="field.transaction_no" :err="err.transaction_no" must />
              </div>
              <div class="col-4">
                <Input type="date" label="transaction date" placeholder="Ex: 2023-01-01"
                  v-model="field.transaction_date" :err="err.transaction_date" must />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="card card-body">
          <div class="row gx-1">
            <div class="col-8">
              <SelectInfinite placeholder="Select..." title="item" v-model="field.item" :fetched="fetchedItem"
                :err="err.item" must />
            </div>
            <div class="col-4">
              <Input type="text" category="decimal" label="stock" placeholder="Ex: 541.216" v-model="field.stock"
                :err="err.stock" must disabled />
            </div>
            <div class="col-4">
              <Input type="text" category="decimal" label="quantity" placeholder="Ex: 541.216" v-model="field.quantity"
                :err="err.quantity" must />
            </div>
            <div class="col-4">
              <Input type="text" category="decimal" label="unit_price" placeholder="Ex: 541.216"
                v-model="field.unit_price" :err="err.unit_price" must readonly />
            </div>
            <div class="col-4">
              <Input type="text" category="decimal" label="total price" placeholder="Ex: 541.216"
                v-model="field.total_price" :err="err.total_price" must readonly />
            </div>
            <div class="col-4">
              <SelectInfinite placeholder="Select..." title="unit" v-model="field.unit" :fetched="fetchedUnit"
                :err="err.unit" must disabled />
            </div>
            <div class="col-4">
              <InputGroup type="text" category="decimal" label="SD(%)" maxlength="6" placeholder="Ex: 5%"
                placeholder1="Ex: 5214.25" v-model="field.sd" :err="err.sd" :calcdata="field.sdvalue" @keyup="sdCalc"
                must />
            </div>
            <div class="col-4">
              <InputGroup type="text" category="decimal" label="VAT(%)" maxlength="6" placeholder="Ex: 7.5%"
                placeholder1="Ex: 5214.25" v-model="field.vat" :err="err.vat" :calcdata="field.vatvalue"
                @keyup="vatCalc" must />
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <Button type="button" class="btn btn-secondary me-1" icon="bi bi-plus-square" title="add new" />
            <div class="d-flex align-items-center">
              <Button type="submit" class="btn btn-primary me-1" label="Save" icon="bi bi-save" title="save"
                :disabled="field.isLoading" />
              <Button type="button" class="btn btn-secondary ms-1" label="Reset" icon="bi bi-stars" title="reset"
                @click="resetForm" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
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
import ClientDetails from "../purchase/ClientDetails.vue";
import InputGroup from "../../components/InputGroup.vue";

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  sale: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

const field = reactive({ id: '', bin: '' });
const err = reactive({ bin: '' });

// bin dropdown
const fetchedBin = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin })
  };
};

// customer dropdown
const fetchedCustomer = async () => {
  return {
    url: route('customer-dropdown'),
    data: 'customer',
    mapFn: (customer) => ({ id: customer.id, title: customer.mobile })
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

// reload sale
const refresh = () => router.get(route('sale'), {}, { preserveScroll: true });
</script>