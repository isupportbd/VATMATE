<template>
  <div :class="$attrs.topclass ? $attrs.topclass : 'mb-3'">
    <div class="position-relative">
      <label :for="$attrs.id" :class="$attrs.label ? 'text-capitalize d-flex mb-1' : 'd-none'">
        <span>{{ $attrs.label }}</span>
        <span :class="props.must ? 'text-danger rounded-circle bg-danger must' : 'd-none'"></span>
        <div :class="hood ? 'text-uppercase w-100 text-end text-primary' : 'd-none'" style="font-size: 13px;">
          {{ hood }}
        </div>
      </label>
      <div class="input-group">
        <div class="position-relative" style="width: 35%;">
          <input type="text" class="form-control flex-grow-0 px-1 text-center rounded-0 rounded-start" :maxlength="max"
            :value="modelValue" @input="updateModel" :id="$attrs.id" v-bind="$attrs" />
          <div :class="max ? 'position-absolute bottom-0 end-0 pe-1 text-info fw-bold max' : 'd-none'">
            {{ max - props.modelValue.length }}
          </div>
        </div>
        <input type="text" class="form-control border-start second-input" style="width: 62%;"
          :placeholder="$attrs.placeholder1" :value="props.calcdata" readonly />
        <!-- <input class="form-control" v-model="model" :maxlength="max" v-bind="$attrs"> vue3.3 (2nd way)/3.4 -->
      </div>
    </div>
    <div :id="`${$attrs.id}Help`" :class="err ? 'form-text text-danger' : 'd-none'">{{ err }}</div>
  </div>
</template>

<script setup>
import { onMounted, nextTick, useAttrs } from 'vue';

defineOptions({ name: 'InputGroup', inheritAttrs: false });

const $attrs = useAttrs();
const props = defineProps({
  focus: { type: Boolean },
  must: { type: Boolean },
  err: { type: [String, Boolean], default: false },
  modelValue: { type: [String, Number], default: '' },
  category: { type: String },
  calcdata: { type: [String, Number], default: '' },
  hood: { type: [String, Number, Boolean] }
});

const max = $attrs.maxlength ? $attrs.maxlength.match(/^[0-9]*$/) : '';

// vue3.4 way
/**
 * const model = defineModel({
 *   set: (value) => {
 *     if (props.category === 'number') {
 *       return value.replace(/[^\d]/, '');
 *     } else if (props.category === 'decimal') {
 *       return value.replace(/[^\d\.]|\.(?=.*\.)/, '');
 *     } else if (props.category === 'mobile') {
 *       return value.replace(/[^\d\+]|\+(?=.*\+)/, '');
 *     } else {
 *       return value;
 *     }
 *   }
 * });
 */

// vue3.3 1st way (effective)
const emit = defineEmits(["update:modelValue"]);
const updateModel = (e) => {
  if (props.category === 'number') {
    emit('update:modelValue', e.target.value = e.target.value.replace(/[^\d]/gi, ''));
  } else if (props.category === 'decimal') {
    let val = e.target.value;

    // Remove everything except digits, decimal point, and minus sign
    val = val.replace(/[^0-9.-]/g, '');

    // Move minus sign to the front if it exists anywhere
    const isNegative = val.includes('-');
    val = val.replace(/-/g, ''); // remove all minus signs
    if (isNegative) {
      val = '-' + val;
    }

    // Allow only one decimal point
    const parts = val.split('.');
    if (parts.length > 2) {
      val = parts[0] + '.' + parts.slice(1).join('');
    }

    emit('update:modelValue', e.target.value = val);
  } else if (props.category === 'mobile') {
    let digits = e.target.value.replace(/[^\d]/g, ''); // only digits
    let val = digits;
    if (e.target.value.includes('+')) {
      val = '+' + digits;
    }
    emit('update:modelValue', e.target.value = val);
  } else {
    emit('update:modelValue', e.target.value);
  }
};

// vue3.3 2nd way
/**
 * const emit = defineEmits(["update:modelValue"]);
 * const model = computed({
 *   get: () => props.modelValue,
 *   set: (val) => {
 *     if (props.category === 'number') {
 *       emit('update:modelValue', value = value.replace(/[^\d]/gi, ''));
 *     } else if (props.category === 'decimal') {
 *       emit('update:modelValue', value = value.replace(/[^\d\.]|\.(?=.*\.)/gi, ''));
 *     } else if (props.category === 'mobile') {
 *       emit('update:modelValue', value = value.replace(/[^\d\+]|\+(?=.*\+)/gi, ''));
 *     } else {
 *       emit('update:modelValue', value = value);
 *     }
 *   }
 * });
 */

onMounted(() => { props.focus ? nextTick(() => document.querySelector(`#${$attrs.id}`).focus()) : false });
</script>

<style lang="scss" scoped>
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