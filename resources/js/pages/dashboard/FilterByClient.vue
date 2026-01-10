<template>
  <div class="row g-2 mb-2">
    <div class="col-12 col-sm-8 col-md-7 col-lg-8">
      <SelectInfinite ref="releaseDD" parentclass="m-0" placeholder="Select..." v-model="field.client" :fetched="fetchedClient" />
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-2">
      <Input type="text" category="tax-period" id="tax_period" :topclass="`mb-0 ${err.monthYear ? 'border border-danger rounded-2' : ''}`" placeholder="yyyy-mm" onfocus="(this.type='month')" onblur="(this.type='text')" v-model="field.monthYear" />
    </div>
    <div class="col-12 col-md-2 d-flex align-items-center justify-content-end">
      <Button type="submit" class="btn btn-outline-secondary w-100 me-2" icon="bi bi-filter" title="filter" @click="props.filteredwithclient(field.client?.id, field.monthYear)" :disabled="field.isLoading" />
      <Button type="submit" class="btn btn-outline-secondary w-100" icon="bi bi-stars" title="reset" @click="props.resetclientbasedfilter" :disabled="field.isLoading" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { usePage } from "@inertiajs/vue3";
import SelectInfinite from "../../components/SelectInfinite.vue";
import Button from "../../components/Button.vue";
import Input from "../../components/Input.vue";

const props = defineProps({
  filteredwithclient: { type: Function },
  resetclientbasedfilter: { type: Function }
});

// client field
const field = reactive({ id: '', client: '', monthYear: '', isLoading: false });
const err = reactive({ client: '', monthYear: '' });

const fetchedClient = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (rbin) => ({ id: rbin.id, title: rbin.bin, val: rbin })
  };
};

const reset = () => {
  field.id = field.client = field.monthYear = '';
  err.client = err.monthYear = '';
};

defineExpose({ reset });
</script>

<style lang="scss" scoped></style>