<template>
  <div class="row gx-2">
    <div class="col-12">
      <div class="card card-body border h-100">
        <form @submit.prevent="submitData">
          <div class="row gx-1">
            <div class="col-12">
              <Input type="hidden" v-model="field.id" readonly />
            </div>
            <div class="col-6">
              <div class="row gx-1">
                <div class="col-12">
                  <Input type="text" label="name" maxlength="100" placeholder="Ex: Jhon Doe"
                    v-model="field.customer_name" />
                </div>
                <div class="col-6">
                  <Input type="text" label="mobile" maxlength="11" placeholder="Ex: Jhon Doe"
                    v-model="field.customer_mobile" />
                </div>
                <div class="col-6">
                  <Input type="text" label="BIN/NID" maxlength="17" placeholder="Ex: 12345678910"
                    v-model="field.bin_or_nid" />
                </div>
              </div>
            </div>
            <div class="col-6">
              <TextArea type="text" label="address" maxlength="250" style="height: 112px !important;"
                placeholder="Ex: Assampara, Jaintapur PS, Sylhet" v-model="field.address" />
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
  </div>
</template>

<script setup>
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { reactive, ref, inject } from 'vue';
import { empty } from "../../../helpers/utils";

import Input from '../../../components/Input.vue';
import Button from '../../../components/Button.vue';
import TextArea from '../../../components/TextArea.vue';

import Alert from "../../../helpers/customAlert";

const notify = inject('showToast');

const props = defineProps({
  modal: { type: Object }
});

const field = reactive({ id: '', customer_mobile: '', customer_name: '', address: '', bin_or_nid: '', isLoading: false });
const err = reactive({ customer_mobile: '', customer_name: '', address: '', bin_or_nid: '' });

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.id = row.id ?? "";
  field.customer_mobile = row.customer_mobile ?? "";
  field.customer_name = row.customer_name ?? "";
  field.bin_or_nid = row.bin_or_nid ?? "";
  field.address = row.address ?? "";

  // clear previous errors when setting a record
  err.customer_mobile = err.customer_name = err.bin_or_nid = err.address = "";
};

// reset form
const resetForm = () => {
  field.customer_mobile = field.customer_name = field.bin_or_nid = field.address = "";
  err.customer_mobile = err.customer_name = err.bin_or_nid = err.address = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

const submitData = async () => {
  const form = {
    id: field.id,
    customer_mobile: field.customer_mobile,
    customer_name: field.customer_name,
    bin_or_nid: field.bin_or_nid,
    address: field.address,
  };

  // console.log(dt);
  if (field.id) {
    router.put(route('sale-customer-update', field.id), form, {
      preserveScroll: true,
      onSuccess: (e) => {
        notify(e.props.flash.msg, 'success');
        resetForm();
        props.modal.close();
      },
      onError: (errors) => {
        if (errors?.customer_name) {
          return Alert(`<span class='text-danger'>${errors?.customer_name}</span>`);
        }

        let er = {};
        Object.keys(errors).map((key) => er[key] = errors[key].toString());
        err.customer_mobile = er.customer_mobile;
        err.customer_name = er.customer_name;
        err.bin_or_nid = er.bin_or_nid;
        err.address = er.address;
      },
      onStart: () => field.isLoading = true,
      onFinish: () => field.isLoading = false
    });
  }
};

// clientbin
const binField = reactive({ all: [], val: '', err: '', page: 0, size: 10 });
const binData = ref({});
const fetchedBin = async (search) => {
  binField.page++;
  search && (binField.all = [], binField.page = 0);

  await axios.get(route('clientbin-dropdown'), { params: { page: binField.page, size: binField.size, search: search } }).then(async (res) => {
    binData.value = res.data.clientbin;
    res.data.clientbin.data.forEach((dt) => {
      binField.all = [...binField.all, { id: dt.id, title: dt.bin, company: dt }];
    });
  });
};

// // reset the form
// const resetForm = () => {
//   field.customer_name = field.address = field.bin_or_nid = "";
//   Object.keys(err).map((key) => err[key] = '');
// };

// onMounted(async () => await selectedBinItemCriteria());

// watchEffect(() => { // if clicked edit function from parent and get the data
//   field.id = props.edit.id;
//   binField.val = Object.keys(props.edit).length > 0 ? { id: props.edit?.clientbin_id, title: props.edit?.clientbin?.bin, company: props.edit?.clientbin } : '';
//   field.customer_name = props.edit.customer_name ? props.edit?.customer_name : "";
//   field.bin_or_nid = props.edit.bin_or_nid ? props.edit?.bin_or_nid : "";
//   field.address = props.edit.address ? props.edit?.address : "";
// });
</script>

<style lang="scss" scoped>
.border {
  border-color: #CCCCCC !important;
}
</style>