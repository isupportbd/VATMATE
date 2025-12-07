<template>
  <div class="card border">
    <div class="card-body">
      <form @submit.prevent="submitData">
        <div class="row gx-1">
          <div class="col-12 col-lg-4 col-xl-4">
            <SelectInfinite parentclass="m-0" placeholder="Select..." title="BIN" v-model="field.bin"
              :fetched="fetchedBin" :hood="field.bin?.val?.company_name" :err="err.bin" must />
          </div>
          <div class="col-12 col-lg-5 col-xl-6">
            <SelectInfinite parentclass="m-0" placeholder="Item..." title="item" v-model="field.item"
              :fetched="fetchedItem" :err="err.item" must />
          </div>
          <div class="col-12 col-lg-3 col-xl-2">
            <div class="d-flex align-items-center justify-content-end gap-1 mt-2 mt-lg-4 pt-lg-1">
              <Button type="submit" class="btn btn-primary" label="Save" icon="bi bi-save"
                :disabled="field.isLoading" />
              <Button type="button" class="btn btn-secondary" label="Reset" icon="bi bi-stars" @click="resetForm" />
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, inject, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { user, role } from "../../composables/user";

import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";
import SelectInfinite from "../../components/SelectInfinite.vue";

const notify = inject('showToast'); // toast message

// props define
const props = defineProps({
  data: { type: [Array, Object] }
});

// division field
const field = reactive({ bin: '', item: '', isLoading: false });
const err = reactive({ item: '', });

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
    url: route('item-dropdown'),
    data: 'item',
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` }),
  };
};

// --- Expose API for parent ---
const setForm = async (row = {}) => {
  field.bin = row.bin?.id ?? "";
  field.item = row.item?.id ?? "";
  // clear previous errors when setting a record
  err.bin = err.item = "";
};

// reset form
const resetForm = () => {
  field.bin = field.item = err.bin = err.item = "";
};

defineExpose({ setForm, resetForm });
// --- end expose ---

// create or update division
const submitData = async () => {
  const form = { client_id: field.bin?.id, item_id: field.item?.id };

  const onErr = (errors) => {
    let er = {};
    Object.keys(errors).map((key) => er[key] = errors[key].toString());
    err.bin = er.client_id;
    err.item = er.item_id;
  };

  router.post(route('economicarea-create'), form, {
    preserveScroll: true,
    onSuccess: (e) => {
      notify(e.props.flash.msg, 'primary');
      resetForm();
    },
    onError: onErr,
    onStart: () => field.isLoading = true,
    onFinish: () => field.isLoading = false
  });
};

// const binDD = ref(null);
// const itemDD = ref(null);
// onMounted(() => {
//   const reload = async () => {
//     const jobs = [];

//     if (binDD.value?.reload) {
//       jobs.push(binDD.value.reload());
//     }

//     if (itemDD.value?.reload) {
//       jobs.push(itemDD.value.reload());
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