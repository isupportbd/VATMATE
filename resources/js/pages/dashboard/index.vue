<template>

  <Head title="Dashboard" />
  <PageBar>
    <p class="m-0 fs-4">Dashboard</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <h1>Dashboard</h1>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import PageBar from '../../components/PageBar.vue';
import Refresh from '../../components/Refresh.vue';
import Button from '../../components/Button.vue';

const refresh = () => router.get(route('dashboard'), {}, { preserveScroll: true });
</script>

<style lang="scss" scoped></style>