<template>

  <Head title="Dashboard" />
  <PageBar>
    <p class="m-0 fs-4">Dashboard</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <h1>Dashboard</h1>

  <!-- <div class="card card-body mb-2">
    <div class="row g-2">
      <div class="col-12 col-sm-9 col-md-10">
        <SelectInfinite id="user" ref="userDD" placeholder="Select..." parentclass="mb-0" v-model="field.user" :fetched="fetchedUser" :err="err.user" />
      </div>
      <div class="col-12 col-sm-3 col-md-2 d-flex align-items-center justify-content-end">
        <Button type="submit" class="btn btn-outline-secondary w-100 me-2" icon="bi bi-filter" title="filter" :disabled="field.isLoading" />
        <Button type="submit" class="btn btn-outline-secondary w-100" icon="bi bi-stars" title="reset" :disabled="field.isLoading" />
      </div>
    </div>
  </div> -->

  <!-- <div class="container-fluid px-0 mb-2">
    <div class="row">
      <div class="col-12">
        <div class="card card-body">
          <Filter />
          <div class="row g-2">
            <Card title="Total Client" :data="props.total_client" icon="bi bi-people" foot="Clients" />
            <Card title="Jan 2026" data="200" :client="props.current_month_client" icon="bi bi-person" foot="Transaction" />
            <Card title="Dec 2025" data="200" :client="props.previous_month_client" icon="bi bi-person" foot="Transaction" />
            <Card title="Nov 2025" data="200" :client="props.two_month_ago_client" icon="bi bi-person" foot="Transaction" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="row">
      <div class="col-12">
        <div class="card card-body">
          <div class="row g-2 mb-2">
            <div class="col-12 col-sm-9 col-md-10">
              <SelectInfinite ref="releaseDD" parentclass="m-0" placeholder="Select..." v-model="field.client" :fetched="fetchedClient" />
            </div>
            <div class="col-12 col-sm-3 col-md-2 d-flex align-items-center justify-content-end">
              <Button type="submit" class="btn btn-outline-secondary w-100 me-2" icon="bi bi-filter" title="filter" :disabled="field.isLoading" />
              <Button type="submit" class="btn btn-outline-secondary w-100" icon="bi bi-stars" title="reset" :disabled="field.isLoading" />
            </div>
          </div>
          <div class="row g-2">
            <div class="col-12 col-md-6 col-lg-7">
              <div class="row g-2 mb-2">
                <div class="col-12 col-lg-6">
                  <div class="card card-body">
                    <p class="m-0"><span>BIN :</span> <span>xxxxxxxxxxxxxxx</span></p>
                    <p class="m-0"><span>Client :</span> <span>xxxxxxxxxxxxxxx</span></p>
                    <p class="m-0"><span>Address :</span> <span>xxxxxxxxxxxxxxx</span></p>
                  </div>
                </div>
                <div class="col-12 col-lg-6">
                  <div class="card card-body">
                    <p class="m-0"><span>Name :</span> <span>xxxxxxxxxxxxxxx</span></p>
                    <p class="m-0"><span>Mobile :</span> <span>xxxxxxxxxxxxxxx</span></p>
                    <p class="m-0"><span>Email :</span> <span>xxxxxxxxxxxxxxx</span></p>
                  </div>
                </div>
              </div>
              <div class="card card-body">
                <div class="row">
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                  <div class="col-6 col-sm-4 col-md-4">
                    <span>Treasury : </span> <span>0.00</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-5">
              <div class="row g-2">
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card card-body">
                    <h6 class="m-0">BoE</h6>
                    <p class="m-0">20</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> -->
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { user, role } from '../../composables/user';

import PageBar from '../../components/PageBar.vue';
import Refresh from '../../components/Refresh.vue';
import Button from '../../components/Button.vue';
import SelectInfinite from '../../components/SelectInfinite.vue';
import Card from './Card.vue';
import Filter from './Filter.vue';

const props = defineProps({
  total_client: { type: [String, Number], default: 0 },
  current_month_client: { type: [String, Number], default: 0 },
  previous_month_client: { type: [String, Number], default: 0 },
  two_month_ago_client: { type: [String, Number], default: 0 },
});

// client field
const field = reactive({ id: '', user: '', client: '', isLoading: false });
const err = reactive({ user: '', client: '' });

const fetchedClient = async () => {
  return {
    url: route('client-dropdown'),
    data: 'client',
    mapFn: (rbin) => ({ id: rbin.id, title: rbin.bin, val: rbin })
  };
};

const refresh = () => router.get(route('dashboard'), {}, { preserveScroll: true });
</script>

<style lang="scss" scoped></style>