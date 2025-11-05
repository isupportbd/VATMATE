<template>
  <div :class="$attrs.topclass ? $attrs.topclass : 'mb-3'">
    <div class="position-relative">
      <label :for="$attrs.id" :class="$attrs.label ? 'd-flex' : 'd-none'">
        <span :class="`text-capitalize ${$attrs.label ? 'mb-1' : ''}`" v-html="$attrs.label"></span>
        <span :class="props.must ? 'text-danger rounded-circle bg-danger must' : 'd-none'"></span>
        <div :class="props.hood ? 'text-end ms-auto text-primary align-self-center' : 'd-none'"
          style="font-size: 13px;">
          {{ props.hood }}
        </div>
      </label>

      <div class="input-group mb-3">
        <input class="form-control" :type="showPassword ? 'text' : 'password'" :value="props.modelValue"
          @input="updateModel" :maxlength="max" :readonly="props.readonly" v-bind="$attrs">
        <button class="input-group-text p-0 border-0 border-start border-2" type="button" @click="togglePassword">
          <i class="py-2 px-3" :class="showPassword ? 'bi bi-eye' : 'bi bi-eye-slash'" style="font-size: 15px;"></i>
        </button>
      </div>
      <!-- <input class="form-control" v-model="model" :maxlength="max" v-bind="$attrs"> vue3.3 (2nd way)/3.4 -->
      <div :class="max ? 'position-absolute bottom-0 end-0 pe-1 text-secondary max' : 'd-none'">
        {{ isFirefox ? (max - String(props.modelValue).length) : ($attrs.type == 'month') ? '' : (max -
          String(props.modelValue).length) }}
      </div>
    </div>
    <div :id="`${$attrs.id}Help`" :class="err ? 'form-text mt-1 text-danger' : 'd-none'">{{ err }}</div>
  </div>
</template>

<script setup>
import { onMounted, nextTick, useAttrs, toRef, ref } from 'vue';
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
    let val = e.target.value.replace(/[^\d.]/g, '');
    const parts = val.split('.');
    val = parts[0];
    if (parts.length > 1) {
      val += '.' + parts.slice(1).join('');
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

const showPassword = ref(false);
const togglePassword = () => showPassword.value = !showPassword.value;

onMounted(() => { props.focus ? nextTick(() => document.querySelector(`#${$attrs.id}`).focus()) : false });
</script>

<style lang="scss" scoped>
input {
  border-color: #CCCCCC;

  &:read-only {
    background-color: #F8F8F8;
  }

  &::placeholder {
    color: #BFBFBF;
  }
}

button:focus-visible {
  border: none;
  box-shadow: none;
}

.form-text {
  font-size: 0.75rem;
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