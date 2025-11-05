<template>

  <Head title="Form 6.2.1" />
  <PageBar>
    <p class="m-0 fs-4">Form 6.2.1</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="row">
    <div class="col-8">
      <div class="card border h-100">
        <div class="card-body">
          <form>
            <div class="row gx-1">
              <div class="col-12">
                <Input type="hidden" v-model="field.id" readonly />
              </div>
              <div class="col-5">
                <SelectInfinite ref="binDD" placeholder="Select..." title="BIN" label="title" v-model="field.bin"
                  :fetched="fetchedBin" :hood="field.bin?.val?.company_name" :err="err.bin" must />
              </div>
              <div class="col-7">
                <SelectInfinite ref="itemDD" placeholder="Select..." title="item" label="title" v-model="field.item"
                  :fetched="fetchedItem" :hood="field.item?.val?.company_name" :err="err.item" must />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-4">

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

import Confirm from "../../helpers/customConfirm";

const props = defineProps({
  form621: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

const field = reactive({ id: '', bin: '' });
const err = reactive({ bin: '' });

// bin dropdown
const binDD = ref(null);
const fetchedBin = async (search, reset = false, reload = false) => {
  await binDD.value?.fetchedDropdown(search, {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (cbin) => ({ id: cbin.id, title: cbin.bin, val: cbin }),
    reset: reset,
    reload: reload
  });
};

// item dropdown
const itemDD = ref(null);
const fetchedItem = async (search, reset = false, reload = false) => {
  await itemDD.value?.fetchedDropdown(search, {
    url: route('item-dropdown'),
    data: 'item',
    mapFn: (item) => ({ id: item.id, title: `${item.name} [${item.hs_code}]` }),
    reset: reset,
    reload: reload
  });
};

// reload sale
const refresh = () => router.get(route('form621'), {}, { preserveScroll: true });
</script>