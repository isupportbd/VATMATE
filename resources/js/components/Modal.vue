<template>
  <Teleport to="#modal-show">
    <div ref="modal" class="modal fade" :id="props.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
      :aria-labelledby="`${props.id}Label`">
      <div :class="`modal-dialog modal-dialog-centered modal-dialog-scrollable modal-${props.size}`">
        <div class="modal-content border">
          <div class="modal-header">
            <div class="d-flex w-100 justify-content-between align-items-center">
              <h5 class="modal-title me-3" id="modalScrollableTitle">{{ props.title }}</h5>
              <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
              <!-- <slot name="modal-header-extra"></slot> -->
            </div>
          </div>
          <div class="modal-body">
            <slot name="modalbody"></slot>
          </div>
          <div class="modal-footer py-2">
            <slot name="modalfooter"></slot>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import { Modal } from 'bootstrap';

defineOptions({ name: "Modal", inheritAttrs: false });

const props = defineProps({
  id: { type: String },
  title: { type: String },
  size: { type: String },
});

const modal = ref(null);
const close = () => {
  const modalpop = Modal.getOrCreateInstance(modal.value);
  modalpop.hide();
};

defineExpose({ close });
</script>

<style lang="scss" scoped></style>