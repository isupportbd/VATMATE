<script setup>
import { ref, reactive, onMounted, nextTick, computed, onBeforeUnmount, watch } from 'vue';
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
  resetKey: { type: [String, Number, Boolean, Object, null], default: null },
});

// vue3.4 way
const emit = defineEmits(['fetched', 'clear']);
const model = defineModel();

const skipNextSearch = ref(false); // suppress search right after clear

let observer = ref(null);
let load = ref(null);

// ✅ store callbacks coming from fetchedDropdown(pack)
const hooks = ref({ option: null }); // function | function[] | null

// remember last fetch config and search so the child can refetch/append by itself
const lastPack = ref(null);   // { url, data, params, mapFn }
const lastSearch = ref('');   // current search term

const hasNextPage = computed(() => {
  const total = Number(fieldData.value?.total ?? 0);
  return field.all.length < total;
});

// ask parent for a pack and run our internal fetcher
const callParentFetched = async (search, opts = {}) => {
  const maybePack = await props.fetched?.({ search, ...opts });
  if (maybePack && typeof maybePack === 'object') {
    await fetchedDropdown(search, { ...maybePack, ...opts });
  }
};

const onClear = () => {
  resetMe();                   // clear options + selected value
  skipNextSearch.value = true; // suppress the immediate empty search
  emit('clear');               // notify parent that clear button was clicked
};

const onOpen = async () => {
  // if empty, load page 1 via parent fetcher (no boolean flags)
  if (!field.all.length) {
    if (lastPack.value) {
      await fetchedDropdown('', { ...lastPack.value, reset: true });
    } else {
      // await props.fetched?.('');
      await callParentFetched('', { reset: true });
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
  const optHooks = Array.isArray(hooks.value.option) ? hooks.value.option : (hooks.value.option ? [hooks.value.option] : []);

  // When user clicks × or backspaces everything, v-select sets model to null.
  if (val == null || val === '') {
    for (const f of optHooks) {
      if (typeof f === 'function') {
        await Promise.resolve(f(null));
      }
    }
    emit('clear');
    return;
  }

  field.val = val;

  for (const f of optHooks) {
    if (typeof f === 'function') {
      await Promise.resolve(f(val));
    }
  }
};

const inputSearch = debounce(async (search, loading) => {
  if (skipNextSearch.value && (!search || !search.trim().length)) {
    skipNextSearch.value = false; // consume the flag
    return;
  }

  if (search && search.trim().length) {
    loading(true);
    if (lastPack.value) {
      await fetchedDropdown(search, { ...lastPack.value, reset: true }).finally(() => loading(false));
    } else {
      await callParentFetched(search, { reset: true }).finally(() => loading(false));
    }
  } else {
    // user erased the query manually
    field.page = 0;
    if (lastPack.value) {
      await fetchedDropdown('', { ...lastPack.value }); // no reset → keep list
    } else {
      await callParentFetched('');
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
    await callParentFetched(lastSearch.value || '');
  }
  await nextTick();
  if (ul) ul.scrollTop = scrollTop;
}

onMounted(async () => {
  observer.value = new IntersectionObserver(infiniteScroll);

  // set default value if provided
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
  const reset = !!pack?.reset;
  const reload = !!pack?.reload;

  // make option sticky: only overwrite if caller provided it
  if (Object.prototype.hasOwnProperty.call(pack ?? {}, 'option')) {
    const pOpt = pack.option;
    hooks.value.option = Array.isArray(pOpt) ? pOpt.filter(fn => typeof fn === 'function') : (typeof pOpt === 'function' ? [pOpt] : []);
  }

  // remember essentials so the child can refetch/append on its own
  lastPack.value = { url, data, params, mapFn };
  lastSearch.value = search ?? '';

  // if pass and true -> reset, reload , search
  (reset && resetMe()) || (reload && reloadMe()) || (!empty(search) && !reset && !reload && reloadMe());

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
    const key = opt?.id ?? opt?.title ?? JSON.stringify(opt); // build a comparison key: prefer id, then title, then full object

    if (!field.all.some(o => (o?.id ?? o?.title ?? JSON.stringify(o)) === key)) {
      field.all = [...field.all, opt];
    }
  });
};

/**
 * if backend send proper object or null
 * then watch function not get any axios
 * error else axios can send error
 */
watch(() => props.resetKey, async (newVal, oldVal) => {
  if (oldVal === undefined) return;

  const optHooks = Array.isArray(hooks.value.option) ? hooks.value.option : (hooks.value.option ? [hooks.value.option] : []);

  resetMe(); // clears field.all, field.page, field.val
  model.value = '';

  // Notify hooks that selection is gone
  for (const fn of optHooks) {
    if (typeof fn === 'function') {
      await Promise.resolve(fn(null));
    }
  }

  skipNextSearch.value = true; // avoid instant empty-search call
  await callParentFetched('', { reset: true, reload: true });
});

/**
watch(() => props.resetKey, async (newVal, oldVal) => {
  if (oldVal === undefined) return;

  // normalize hooks to an array
  const optHooks = Array.isArray(hooks.value.option) ? hooks.value.option : (hooks.value.option ? [hooks.value.option] : []);

  // Parent (BIN) cleared → just clear everything locally, no axios
  if (!newVal) {
    resetMe(); // clear the select itself

    for (const fn of optHooks) { // notify all option hooks that the selection is gone
      if (typeof fn === 'function') {
        await Promise.resolve(fn(null));
      }
    }

    return; // important: don't fetch when BIN is empty
  }

  await callParentFetched('', { reset: true, reload: true }); // Parent (BIN) changed to a valid id → reset + reload from parent
});
*/

defineExpose({ field, fieldData, fetchedDropdown });
</script>

<template>
  <div :class="`${$attrs.parentclass ? $attrs.parentclass : 'mb-2'}`">
    <label :for="$attrs.id" class="text-capitalize d-flex align-items-center mb-1" :class="{ 'd-none': !$attrs.title }">
      <div class="position-relative">
        <span class="text-capitalize" v-html="$attrs.title"></span>
        <span class="position-absolute text-danger rounded-circle bg-danger must"
          :class="{ 'd-none': !props.must }"></span>
      </div>
      <div class="text-uppercase w-100 text-end text-primary fw-semibold" :class="{ 'd-none': !props.hood }"
        style="font-size: 12px; margin-top: 0.15rem;" v-html="props.hood"></div>
    </label>
    <v-select label="title" :filterable="false" v-model="model" :options="field.all" @open="onOpen"
      @update:modelValue="onModelUpdate" @close="onClose" @search="inputSearch" @clear="onClear" v-bind="$attrs">
      <template #list-footer>
        <li v-show="hasNextPage" ref="load" class="loader">More...</li>
      </template>
    </v-select>
    <div class="form-text text-danger" :class="{ 'd-none': !err }" style="font-size: 0.8rem;">{{ err }}</div>
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
  top: 0;
  margin-top: 5px;
  margin-left: 2px;
}
</style>
