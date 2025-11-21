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
              <slot name="modal-header-extra"></slot>
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
import { ref, onMounted } from 'vue';
import { Modal } from 'bootstrap';

defineOptions({ name: "Modal", inheritAttrs: false });

const props = defineProps({
  id: { type: String, required: true },
  title: { type: String, default: "" },
  size: { type: String, default: "md" },
});

const modal = ref(null);
let modalInstance = null;

onMounted(() => {
  modalInstance = new Modal(modal.value, {
    backdrop: 'static',
    keyboard: false,
  });
});

const open = () => {
  if (!modalInstance) {
    modalInstance = new Modal(modal.value, {
      backdrop: 'static',
      keyboard: false,
    });
  }
  modalInstance.show();
};

const close = () => {
  if (!modalInstance) {
    modalInstance = new Modal(modal.value, {
      backdrop: 'static',
      keyboard: false,
    });
  }
  document.activeElement?.blur(); // to avoid aria-hidden warning
  modalInstance.hide();
};

defineExpose({ close, open });
</script>


<style lang="scss" scoped></style>