<template>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body position-relative">
        <div class="d-flex align-items-center justify-content-between">
          <div class="position-relative z-1 me-3">
            <h5 class="card-title fw-bold">{{ props.title }}</h5>
            <p class="card-text fs-3 fw-bold">{{ props.data }}</p>
          </div>
          <div class="position-relative text-end z-1" :class="{ 'd-none': empty(props.client) }">
            <h5 class="card-title fw-bold">New Client</h5>
            <p class="card-text fs-3 fw-bold">{{ props.client }}</p>
          </div>
        </div>
        <div :class="['position-absolute top-50', empty(props.client) ? 'end-0 translate-middle-y' : 'top-50 start-50 translate-middle']">
          <i :class="['opacity-25', props.icon ? props.icon : 'bi bi-people']" style="font-size: 6rem"></i>
        </div>
      </div>
      <div class="card-footer py-1 fw-bold text-center text-uppercase">{{ props.foot }}</div>
    </div>
  </div>
</template>

<script setup>
import { empty } from '../../helpers/utils';

const props = defineProps({
  title: { type: String, default: 'Card Title' },
  client: { type: [String, Number], default: 0 },
  data: { type: [String, Number], default: '0' },
  icon: { type: String, default: '' },
  foot: { type: String, default: 'Card Footer' }
});
</script>

<style lang="scss" scoped></style>