<template>

  <Head title="4.3 Forwarding" />
  <PageBar>
    <p class="m-0 fs-4">4.3 Forwarding</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.id, route().params.client_id, route().params.monthYear)" />
    <Link title="back"
      :href="route('report-43', { client_id: route().params.client_id, monthYear: route().params.monthYear })"
      class="nav-btn">
    <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <div class="row printarea">
    <div class="col-12">
      <div class="card card-body border p-5">
        <div class="row">
          <div class="col-12">
            <div class="fw-bold text-center">
              <h3 class="m-0">{{ props.report43?.client?.company_name }}</h3>
              <p class="m-0">{{ props.report43?.client?.address }}</p>
              <p class="m-0">BIN: {{ props.report43?.bin }}</p>
              <p class="m-0">Mobile: {{ props.report43?.client?.mobile }}</p>
            </div>
            <div class="row my-3">
              <div class="d-flex align-items-center justify-content-between">
                <span class="m-0">Ref:</span>
                <span class="m-0" style="padding-right: 10rem">Date:</span>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <p class="m-0">The Divisional Officer</p>
                <p class="m-0">Customs, Excise & VAT Division, {{ props.report43?.client?.division?.name }}</p>
              </div>
              <div class="col-12 my-3">
                <h6> Subject:
                  <u>Submission of Input-Output Coefficient (Mushak-4.3) declaration.</u>
                </h6>
              </div>
              <div class="col-12">
                <p class="text-justify">
                  Dear Sir, <br />
                  We are pleased to enclose herewith the following documents
                  relating to the Input-Output Coefficient declaration of the
                  products under section 32(5) of the VAT & SD Act, 2012 and
                  under rule 21 of the VAT & SD Rules, 2016 for your kind
                  approval.
                  <br /><br />
                  Please acknowledge receipt.
                  <br /><br />
                  Sincerely Yours,
                  <br /><br /><br /><br /><br /><br />
                  <span class="border-top border-dark">Authorized Person</span><br />
                </p>
              </div>
              <div class="col-12 mt-3">
                <span class="border-bottom border-dark">Enclosure: </span><br />
                <span>1) Form "Mushak-4.3" duly filed & signed</span><br />
                <span>2) Bill of Entry for imported materials</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { user, role } from "../../../composables/user";

import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import Button from '../../../components/Button.vue';
import { Link, Head, router } from '@inertiajs/vue3';

const props = defineProps({ report43: Object });

const refresh = (id, client_id, monthYear) => router.get(route('report-43-forwarding', { id, client_id, monthYear }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['report43'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style lang="scss" scoped></style>