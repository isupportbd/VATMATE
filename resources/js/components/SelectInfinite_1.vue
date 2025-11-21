<script setup>
import { ref, reactive, onMounted, nextTick, computed, onBeforeUnmount } from 'vue';
import { debounce } from 'lodash-es';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { empty } from "../helpers/utils";

defineOptions({ name: "SelectInfinite", inheritAttrs: false });

const props = defineProps({
  fetched: { type: Function },
  must: { type: Boolean },
  err: { type: [String, Boolean], required: false },
  hood: { type: [String, Boolean] },
  defaultValue: { type: [Object, String, Number, Boolean, null], default: null },
});

// vue3.4 way
const emit = defineEmits(['fetched']);
const model = defineModel();

let observer = ref(null);
let load = ref(null);

// ✅ store callbacks coming from fetchedDropdown(pack)
const hooks = ref({
  option: null, // function | function[] | null
});
// remember last fetch config and search so the child can refetch/append by itself
const lastPack = ref(null);   // { url, data, params, mapFn }
const lastSearch = ref('');   // current search term

const hasNextPage = computed(() => {
  const total = Number(fieldData.value?.total ?? 0);
  return field.all.length < total;
});

const onOpen = async () => {
  // if empty, load page 1 via parent fetcher (no boolean flags)
  if (!field.all.length) {
    if (lastPack.value) {
      await fetchedDropdown('', { ...lastPack.value, reset: true });
    } else {
      await props.fetched?.('');
    }
  }

  await nextTick();
  if (load.value && hasNextPage.value) {
    observer.value?.observe(load.value);
  }
};

const onClose = async () => {
  observer.value?.disconnect();
};

const onModelUpdate = async (val) => {
  // Backspace (or X) clears selection → val is null/'' (depending on your reduce)
  if (val == null || val === '') {
    // keep current items; append full list from page 1
    field.page = 0;
    if (lastPack.value) {
      await fetchedDropdown('', { ...lastPack.value }); // no reset → do not clear field.all
    } else {
      await props.fetched?.('');
    }
    await nextTick();
    if (load.value && hasNextPage.value) observer.value?.observe(load.value);
    field.val = val;
    return; // ← prevent running option callbacks on clear
  }
  field.val = val;

  // run one or many callbacks
  const optHook = hooks.value.option;
  if (Array.isArray(optHook)) {
    for (const f of optHook) {
      if (typeof f === 'function') await Promise.resolve(f(val));
    }
  } else if (typeof optHook === 'function') {
    await Promise.resolve(optHook(val));
  }
};

const inputSearch = debounce(async (search, loading) => {
  if (search.length) {
    loading(true);
    if (lastPack.value) {
      await fetchedDropdown(search, { ...lastPack.value, reset: true }).then(() => loading(false));
    } else {
      await props.fetched(search).then(() => loading(false));
    }
  } else {
    // cleared search → keep current items; append full list from page 1
    field.page = 0;
    if (lastPack.value) {
      await fetchedDropdown('', { ...lastPack.value }); // no reset
    } else {
      await props.fetched?.('');
    }
    await nextTick();
    if (load.value && hasNextPage.value) observer.value?.observe(load.value);
  }
}, 400);

const infiniteScroll = async ([{ isIntersecting, target }]) => {
  if (!isIntersecting) return;
  const ul = target?.offsetParent;
  const scrollTop = ul?.scrollTop ?? 0;
  if (lastPack.value) {
    await fetchedDropdown(lastSearch.value, { ...lastPack.value }); // next page of current mode
  } else {
    await props.fetched?.(); // fallback if first-time no pack yet
  }
  await nextTick();
  if (ul) ul.scrollTop = scrollTop;
}

onMounted(async () => {
  observer.value = new IntersectionObserver(infiniteScroll);

  // ✅ set default value if provided
  if (props.defaultValue) {
    await nextTick();
    field.val = props.defaultValue;
    model.value = props.defaultValue;
  }
});

onBeforeUnmount(() => {
  observer.value?.disconnect();
  observer.value = null;
});

// prototype and it will call from parent
const field = reactive({ all: [], val: '', page: 0, size: 10 });
const fieldData = ref({});

const resetMe = () => {
  field.all = [];
  field.page = 0;
  field.val = '';
  if (model) model.value = '';
};

const reloadMe = () => {
  field.all = [];
  field.page = 0;
}

const fetchedDropdown = async (search, pack = null) => {
  const url = pack?.url ?? null;
  const data = pack?.data ?? null;
  const params = pack?.params ?? {};
  const mapFn = pack?.mapFn ?? ((x) => x);
  const reset = pack?.reset ?? false;
  const reload = pack?.reload ?? false;

  // ✅ make option sticky: only overwrite if caller provided it
  if (Object.prototype.hasOwnProperty.call(pack ?? {}, 'option')) {
    const pOpt = pack.option;
    hooks.value.option = Array.isArray(pOpt) ? pOpt.filter(fn => typeof fn === 'function') : (typeof pOpt === 'function' ? [pOpt] : []);
  }

  // remember essentials so the child can refetch/append on its own
  lastPack.value = { url, data, params, mapFn };
  lastSearch.value = search ?? '';

  if (reset) { resetMe() }

  if (reload) { reloadMe() }

  if (!empty(search)) { reloadMe() }

  // after reset/reload/search
  field.page++;
  const param = { page: field.page, size: field.size, search: search };

  // log once (don’t call axios twice)
  // console.log('GET', pack.url, { params: pack.params ? { ...pack.params, ...param } : param });

  const key = await axios.get(url, { params: params ? { ...params, ...param } : param });
  const payload = key.data[data];

  fieldData.value = payload;
  const rows = Array.isArray(payload?.data) ? payload.data : [];
  // console.log('Fetched', payload?.data);
  rows.forEach((dt) => {
    const opt = mapFn(dt);
    if (!field.all.some(o => o?.id === opt?.id)) {
      field.all = [...field.all, opt];
    }
  });
};

defineExpose({ field, fieldData, fetchedDropdown });
</script>

<template>
  <div :class="`${$attrs.parentclass ? $attrs.parentclass : 'mb-2'}`">
    <label :for="$attrs.id" :class="$attrs.title ? 'text-capitalize d-flex mb-1' : 'd-none'">
      <span class="text-capitalize">{{ $attrs.title }}</span>
      <span :class="props.must ? 'text-danger rounded-circle bg-danger must' : 'd-none'"></span>
      <div :class="props.hood ? 'text-uppercase w-100 text-end text-primary' : 'd-none'" style="font-size: 13px;">
        {{ props.hood }}
      </div>
    </label>
    <v-select :filterable="false" v-model="model" v-bind="$attrs" :options="field.all" @open="onOpen"
      @update:modelValue="onModelUpdate" @close="onClose" @search="inputSearch">
      <template #list-footer>
        <li v-show="hasNextPage" ref="load" class="loader">More...</li>
      </template>
    </v-select>
    <div :class="`${err ? 'form-text text-danger' : 'd-none'}`" style="font-size: 0.8rem;">{{ err }}</div>
  </div>
</template>

<style lang="scss" scoped>
.loader {
  text-align: center;
  color: #bbbbbb;
}

.must {
  width: 4px;
  height: 4px;
  margin-left: 2px;
  margin-top: 5px;
}
</style>
