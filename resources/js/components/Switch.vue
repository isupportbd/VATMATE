<template>
  <div :class="props.vertical ? `text-center ${$attrs.topclass}` : ''">
    <label :class="props.vertical ? 'form-check-label d-block' : 'd-none'">
      <span
        v-html="props.checked ? `<div class='d-flex justify-content-center'><span>${$attrs.text}</span><div class='rounded-circle bg-success indicator'></div></div>` : `<div class='d-flex justify-content-center'><span>${$attrs.blank}</span><div class='rounded-circle bg-danger indicator'></div></div>`"></span>
    </label>
    <div class="form-check form-switch d-inline-block">
      <input class="form-check-input shadow-none border-primary" type="checkbox" role="switch" :checked="props.checked"
        v-bind="$attrs">
      <label :class="props.vertical ? 'd-none' : 'form-check-label'">
        <span
          v-html="props.checked ? `<div class='d-flex justify-content-center'><span>${$attrs.text}</span><div class='rounded-circle bg-success indicator'></div></div>` : `<div class='d-flex justify-content-center'><span>${$attrs.blank}</span><div class='rounded-circle bg-danger indicator'></div></div>`"></span>
      </label>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: "Switch", inheritAttrs: false });

const props = defineProps({
  modelValue: { type: [String, Number, Boolean] },
  value: { type: [Array, Object, String, Number, Boolean] },
  vertical: { type: Boolean },
  checked: { type: [Boolean, Number] }
});
</script>

<style lang="scss">
.indicator {
  width: 4px;
  height: 4px;
  margin-left: 2px;
  margin-top: 5px;
}
</style>
<style lang="scss" scoped>
label,
input {
  cursor: pointer;
}
</style>