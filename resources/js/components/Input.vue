<template>
  <div :class="$attrs.topclass ? $attrs.topclass : 'mb-2'">
    <div class="position-relative">
      <label :for="$attrs.id" class="d-flex align-items-center mb-1" :class="{ 'd-none': !$attrs.label }">
        <div class="position-relative">
          <span class="text-capitalize" v-html="$attrs.label"></span>
          <span class="position-absolute text-danger rounded-circle bg-danger must"
            :class="{ 'd-none': !props.must }"></span>
        </div>
        <div class="text-end ms-auto text-primary fw-semibold" :class="{ 'd-none': !props.hood }"
          style="font-size: 12px; margin-top: 0.15rem;" v-html="props.hood"></div>
      </label>
      <input class="form-control" :value="props.modelValue" @input="updateModel" :maxlength="max" :readonly="props.readonly" v-bind="$attrs" :autocomplete="$attrs.autocomplete || ['number', 'decimal'].includes(props.category) ? 'off' : 'on'">
      <!-- <input class="form-control" v-model="model" :maxlength="max" v-bind="$attrs"> vue3.3 (2nd way)/3.4 -->
      <div class="position-absolute bottom-0 end-0 pe-1 text-secondary max" :class="{ 'd-none': !max }">
        {{ isFirefox ? (max - String(props.modelValue).length) : ($attrs.type == 'month') ? '' : (max - String(props.modelValue).length) }}
      </div>
    </div>
    <div :id="`${$attrs.id}Help`" class="form-text mt-1 text-danger" :class="{ 'd-none': !err }">{{ err }}</div>
  </div>
</template>

<script setup>
import { onMounted, nextTick, useAttrs } from 'vue';
import useBrowserDetect from '../composables/useBrowserDetect';

defineOptions({ name: "Input", inheritAttrs: false });
const { isFirefox } = useBrowserDetect();

const $attrs = useAttrs();
const props = defineProps({
  focus: { type: Boolean },
  must: { type: Boolean },
  err: { type: [String, Boolean], default: false },
  modelValue: { type: [String, Number], default: '' },
  category: { type: String },
  hood: { type: [String, Boolean, Number] },
  readonly: { type: Boolean }
});

const max = $attrs.maxlength != null ? Number($attrs.maxlength) : null;


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
    val = val.replace(/[^0-9.]/g, '');

    // Allow only one decimal point
    const parts = val.split('.');
    if (parts.length > 2) {
      val = parts[0] + '.' + parts.slice(1).join('');
    }

    emit('update:modelValue', e.target.value = val);
  } else if (props.category === '+decimal-') {
    let val = e.target.value;

    // Remove everything except digits, decimal point, and minus sign
    val = val.replace(/[^0-9.-]/g, ''); // if need minus sign

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
  } else if (props.category === 'tax-period') {
    let val = e.target.value.replace(/[^\d-]/g, ''); // Only numbers and hyphen
    val = val.replace(/-/g, '');                    // Remove all hyphens
    val = val.slice(0, 6);                          // Only 6 digits (YYYYMM)

    if (val.length > 4) {
      let year = val.slice(0, 4);
      let month = val.slice(4, 6);

      // Only correct if both month digits entered
      if (month.length === 2) {
        let m = parseInt(month, 10);
        // Clamp month between 1 and 12
        if (m < 1) m = 1;
        if (m > 12) m = 12;
        // Always pad to 2 digits
        month = m.toString().padStart(2, '0');
      }

      val = year + '-' + month;
    }

    emit('update:modelValue', e.target.value = val);
  } else if (props.category === 'bin') {
    let digits = e.target.value.replace(/[^\d]/g, '');
    digits = digits.slice(0, 14);
    let formatted = digits;
    if (digits.length > 9) {
      formatted = digits.slice(0, 9) + '-' + digits.slice(9, 14);
    }
    emit('update:modelValue', e.target.value = formatted);
  } else if (props.category === 'challan-number') {
    let digits = e.target.value.replace(/[^\d]/g, '');
    digits = digits.slice(0, 16);
    let formatted = digits;
    if (digits.length > 4) {
      formatted = digits.slice(0, 4) + '-' + digits.slice(4, 16);
    }
    emit('update:modelValue', e.target.value = formatted);
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

onMounted(() => { props.focus ? nextTick(() => document.querySelector(`#${$attrs.id}`).focus()) : false; });
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