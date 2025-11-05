<template>

  <Head title="Login" />
  <div class="container position-absolute start-50 top-50 translate-middle">
    <div class="auth">
      <div class="card card-body border-0">
        <div class="d-block mb-4">
          <h3 class="m-0 text-uppercase text-center fw-semibold">vatmate</h3>
        </div>
        <p class="text-center alert alert-danger text-danger py-1" v-if="form.errors.error">{{ form.errors.error }}</p>
        <form @submit.prevent="submitData">
          <div class="mb-4">
            <Input type="email" label="email" id="email" placeholder="Enter your email..." v-model="form.email" focus />
          </div>
          <div class="mb-4">
            <InputPasswordToggle label="password" placeholder="·······&#xb7;" v-model="form.password" />
          </div>
          <div class="mb-4 d-flex align-items-center justify-content-between">
            <Checkbox label="Remember Me" id="remember-me" v-model="form.remember"
              :checked="form.remember ? true : false" />
            <Link :href="route('forget-password')">Forget Password?</Link>
          </div>
          <Button type="submit" label="Login" class="btn btn-primary w-100 justify-content-center"
            icon="bi bi-box-arrow-in-right" :disabled="form.processing" />
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import Input from "../../components/Input.vue";
import InputPasswordToggle from "../../components/InputPasswordToggle.vue";
import Button from "../../components/Button.vue";
import Checkbox from "../../components/Checkbox.vue";

defineOptions({ layout: AuthLayout });

const form = useForm({
  'email': '',
  'password': '',
  'remember': false
});

const submitData = async () => {
  form.post(route('user-login'), {
    preserveScroll: true,
    onError: () => {
      form.reset('password');
      setTimeout(() => {
        form.reset();
      }, 2000);
    },
    onSuccess: () => form.reset(),
  });
};
</script>

<style lang="scss" scoped></style>