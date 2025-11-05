<template>
  <div class="position-relative mb-3">
    <div class="position-relative">
      <label :for="$attrs.id" :class="$attrs.label ? 'text-capitalize d-flex mb-1' : 'd-none'">
        <span>{{ $attrs.label }}</span>
        <span :class="props.must ? 'text-danger rounded-circle bg-danger must' : 'd-none'"></span>
      </label>
      <div class="form-floating h-100">
        <textarea :value="props.modelValue" @input="updateModel" class="form-control mb-0 h-100" :maxlength="max"
          :aria-describedby="`${$attrs.id}Help`" v-bind="$attrs">
        </textarea>
        <label :for="$attrs.id" :class="$attrs.placeholder ? 'd-block placeholder-label border-0' : 'd-none'"
          style="top: -11px; left: 3px;">
          {{ $attrs.placeholder }}
        </label>
      </div>
      <label :class="max ? 'position-absolute end-0 pe-1 text-secondary max' : 'd-none'" style="top: 28px">
        {{ max - props.modelValue.length }}
      </label>
    </div>
    <div :id="`${$attrs.id}Help`" :class="!err ? 'd-none' : 'form-text text-danger'">{{ err }}</div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, useAttrs } from 'vue';

defineOptions({ name: 'TextArea', inheritAttrs: false });

const $attrs = useAttrs();
const props = defineProps({
  focus: { type: Boolean },
  must: { type: Boolean },
  err: { type: [String, Boolean] },
  modelValue: { type: [String, Number], default: '' },
});

const max = $attrs.maxlength ? $attrs.maxlength.match(/^[0-9]*$/) : '';
const emit = defineEmits(['update:modelValue']);
const updateModel = (e) => emit('update:modelValue', e.target.value);

onMounted(() => { props.focus ? nextTick(() => document.querySelector(`#${$attrs.id}`).focus()) : false });
</script>

<style lang="scss" scoped>
textarea {
  border-color: #CCCCCC;

  &:focus {
    border-color: #9ea5f5;
  }

  &:read-only {
    background-color: #F8F8F8;
  }
}

.placeholder-label {
  color: #CCCCCC !important;
}

.must {
  width: 4px;
  height: 4px;
  margin-left: 2px;
  margin-top: 5px;
}

.max {
  font-size: 0.6rem;
}

textarea {
  border-color: #CCCCCC;
}

textarea:read-only {
  background-color: #F8F8F8;
}
</style>