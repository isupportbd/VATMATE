<template>
  <!-- Content -->

  <Head title="Forget Password" />
  <div class="container position-absolute start-50 top-50 translate-middle">
    <div class="auth">
      <div class="card card-body border-0">
        <div class="d-block mb-4">
          <h3 class="m-0 text-uppercase text-center fw-semibold">vatmate</h3>
        </div>
        <h4 class="text-center">Forget Password</h4>
        <p class="text-center alert alert-success text-success py-1" v-if="msg">{{ msg }}</p>
        <form id="formAuthentication" @submit.prevent="submitData">
          <div class="mb-4">
            <Input type="email" label="email" id="email" placeholder="Enter your email..." v-model="form.email"
              :err="form.errors.email" focus must />
          </div>
          <Button type="submit" label="Send Reset Link" class="btn btn-primary d-grid w-100" icon="bi bi-send"
            :disabled="form.processing" />
        </form>
        <div class="text-center">
          <Link :href="route('login')">
          <i class="bx bx-chevron-left scaleX-n1-rtl me-1"></i>
          <span>Back to login</span>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";

import AuthLayout from "../../layouts/AuthLayout.vue";
import Input from "../../components/Input.vue";
import Button from "../../components/Button.vue";

defineOptions({ layout: AuthLayout });

const msg = ref()
const form = useForm({
  'email': ''
});

const submitData = async () => {
  form.post(route('user-forgetpassword'), {
    preserveScroll: true,
    onSuccess: (e) => {
      msg.value = e.props.flash.msg;
      form.reset();
      setTimeout(() => {
        msg.value = "";
      }, 3000);
    },
  });
};
</script>

<style lang="scss" scoped></style>