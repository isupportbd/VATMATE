<template>

  <Head title="Purchase" />
  <PageBar>
    <p class="m-0 fs-4">Purchase</p>
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
                <SelectInfinite placeholder="Select..." title="BIN" label="title" v-model="field.bin"
                  :fetched="fetchedBin" :hood="field.bin?.val?.company_name" :err="err.bin" must />
              </div>
              <div class="col-7">
                <SelectInfinite placeholder="Select..." title="item" label="title" v-model="field.item"
                  :fetched="fetchedItem" :hood="field.item?.val?.company_name" :err="err.item" must />
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
                <SelectInfinite placeholder="Select..." title="station" label="title" v-model="field.stationcode"
                  :fetched="fetchedStationcode" :err="err.stationcode" must />
              </div>
              <div class="col-3">
                <Input type="text" id="lc" label="LC no" maxlength="50" placeholder="Ex: 5124845487"
                  v-model="field.lc_no" :err="err.lc_no" />
              </div>
              <div class="col-3">
                <Input type="date" label="LC date" placeholder="Ex: 2023-01-01" v-model="field.lc_date"
                  :err="err.lc_date" />
              </div>
              <div class="col-3">
                <Input type="text" category="decimal" maxlength="6" label="wastage(%)" placeholder="Ex: 3.5"
                  v-model="field.wastage" :err="err.wastage" must readonly />
              </div>
              <div class="col-3">
                <SelectInfinite placeholder="Select..." title="unit" label="title" v-model="field.stationcode"
                  :fetched="fetchedStationcode" :err="err.stationcode" must />
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
  purchase: { type: Object },
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

// reload division
const refresh = () => router.get(route('purchase'), {}, { preserveScroll: true });
</script>