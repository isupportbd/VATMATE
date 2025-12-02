<template>
  <!-- Layout wrapper -->
  <Toast ref="toastEl" />
  <div class="wrapper">
    <!-- sidebar start -->
    <Sidebar />
    <!-- sidebar end -->
    <!-- main start -->
    <div class="main">
      <div class="content-wrapper container-xxl px-0">
        <!-- header start -->
        <Header />
        <!-- header end -->
        <!-- content start -->
        <div class="content printarea">
          <slot></slot>
        </div>
        <!-- content end -->
        <!-- footer start -->
        <Footer />
        <!-- footer end -->
      </div>
    </div>
    <!-- main end -->
  </div>
  <div class="backdrop"></div>
  <!-- / Layout wrapper -->
</template>

<script setup>
import { ref, inject, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import Toast from '../../components/Toast.vue';
import Header from './Header.vue';
import Sidebar from './Sidebar.vue';
import Footer from './Footer.vue';

const notify = inject('notify')
const toastEl = ref(null)

onMounted(async () => {
  // your back-button blocker
  history.pushState(null, null, window.location.href);
  window.onpopstate = () => history.pushState(null, null, window.location.href);

  // attach toast instance
  notify.value = toastEl.value;
});

// onBeforeUnmount(() => {
//   if (notify.value === toastEl.value) notify.value = null;
// })
</script>
