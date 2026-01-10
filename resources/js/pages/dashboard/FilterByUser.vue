<template>
  <div class="row g-2 mb-2">
    <div class="col-12 col-sm-9 col-md-10">
      <SelectInfinite id="user" ref="userDD" placeholder="Select..." parentclass="mb-0" v-model="field.user" :fetched="fetchedUser" :err="err.user" />
    </div>
    <div class="col-12 col-sm-3 col-md-2 d-flex align-items-center justify-content-end">
      <Button type="submit" class="btn btn-outline-secondary w-100 me-2" icon="bi bi-filter" title="filter" @click="props.filteredwithuser(field.user?.id)" :disabled="field.isLoading" />
      <Button type="submit" class="btn btn-outline-secondary w-100" icon="bi bi-stars" title="reset" @click="props.resetuserbasedfilter" :disabled="field.isLoading" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SelectInfinite from '../../components/SelectInfinite.vue';
import Button from '../../components/Button.vue';

const props = defineProps({
  filteredwithuser: { type: Function },
  resetuserbasedfilter: { type: Function }
});

// client field
const field = reactive({ id: '', user: '', isLoading: false });
const err = reactive({ user: '' });

// user infinite scroll dropdown
const fetchedUser = async () => {
  return {
    url: route('user-dropdown'),
    data: 'user',
    mapFn: (user) => ({ id: user.id, title: user.email })
  };
};
</script>

<style lang="scss" scoped></style>