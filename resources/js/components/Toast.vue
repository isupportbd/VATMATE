<template>
  <Teleport to="#toast-show">
    <div ref="notify" class="toast position-fixed" role="alert" aria-live="assertive" aria-atomic="true"
      style="z-index: 1000000; top: 10px; right: 23px;" data-bs-delay="2000">
      <div :class="`text-bg-${bgcolor} align-items-center rounded-3 shadow-sm`">
        <div class="d-flex">
          <div class="toast-body">
            {{ message }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import { Toast } from 'bootstrap';

defineOptions({ name: "Toast", inheritAttrs: false });

const props = defineProps({
  icon: { type: String }
});

const notify = ref(null);
const message = ref(null);
const bgcolor = ref(null);

const toastme = (msg, bg) => {
  message.value = msg;
  bgcolor.value = bg;
  // console.log(msg, bg);
  const toast = Toast.getOrCreateInstance(notify.value);
  toast.show();
};

defineExpose({ toastme });
</script>

<style lang="scss" scoped></style>