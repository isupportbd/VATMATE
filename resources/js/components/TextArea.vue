<template>
  <div :class="$attrs.topclass ? `${$attrs.topclass} position-relative` : 'mb-2'">
    <div class="position-relative">
      <div class="position-relative mb-1">
        <span class="text-capitalize" v-html="$attrs.label"></span>
        <span class="position-absolute text-danger rounded-circle bg-danger must"
          :class="{ 'd-none': !props.must }"></span>
      </div>
      <div class="form-floating h-100">
        <textarea :value="props.modelValue" @input="updateModel" class="form-control mb-0 h-100" :maxlength="max"
          :aria-describedby="`${$attrs.id}Help`" v-bind="$attrs">
        </textarea>
        <label :for="$attrs.id" class="d-block placeholder-label border-0" :class="{ 'd-none': !$attrs.placeholder }">
          {{ $attrs.placeholder }}
        </label>
      </div>
      <label class="position-absolute end-0 pe-1 text-secondary max" :class="{ 'd-none': !max }" style="top: 28px">
        {{ max - props.modelValue.length }}
      </label>
    </div>
    <div :id="`${$attrs.id}Help`" class="form-text text-danger" :class="{ 'd-none': !err }">{{ err }}</div>
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
.form-floating {
  label {
    top: -1px;
    left: 1px;
  }

  textarea:focus+label,
  textarea:not(:placeholder-shown)+label {
    top: -6px;
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
</style>