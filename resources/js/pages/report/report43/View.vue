<template>

  <Head title="Report 4.3 View" />
  <PageBar>
    <p class="m-0 fs-4">Report 4.3 View</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" icon="bi bi-file-earmark-pdf-fill" @click="openPdf" />
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.id, route().params.client_id, route().params.monthYear)" />
    <Link title="back"
      :href="route('report-43', { client_id: route().params.client_id, monthYear: route().params.monthYear })"
      class="nav-btn">
      <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>
  <div class="card card-body">
    <div class="row m-4">
      <div class="col-12 text-center position-relative mb-3">
        <img :src="logo" width="60" alt="bd_logo" class="fw-bold position-absolute start-0 ms-3 mt-3">
        <label class="fw-bold border border-secondary border-1 position-absolute p-1 end-0 top-50 me-3">
          Mushak-4.3
        </label>
        <p>Government of the People's Republic of Bangladesh</p>
        <p>National Board of Revenue</p>
        <p class="fw-bold m-0">Mushak-4.3 Material-Product Coefficient (Input-Output Coefficient) Declaration</p>
        <p>[See Rule 21]</p>
        <p>[Please read the instructions before filling up this form]</p>
      </div>
      <div class="col-12 mb-2">
        <div class="table-responsive">
          <table class="table table-bordered border-secondary align-middle">
            <thead>
              <tr class="table-success border-secondary">
                <th colspan="3">PART - 1: APPLICANT'S INFORMATION</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="col-4">Name of the Entity</td>
                <td class="text-center">:</td>
                <td class="col-8">{{ props.view?.client?.company_name }}</td>
              </tr>
              <tr>
                <td>Address</td>
                <td class="text-center">:</td>
                <td>{{ props.view?.client?.address }}</td>
              </tr>
              <tr>
                <td>BIN</td>
                <td class="text-center">:</td>
                <td>{{ props.view?.client?.bin }}</td>
              </tr>
              <tr>
                <td>Date of Submission</td>
                <td class="text-center">:</td>
                <td>{{ moment(props.view?.submission_date).format("DD/MM/YYYY") }}</td>
              </tr>
              <tr>
                <td>Date of First Delivery of Goods /
                  Services as per Declared Coefficient <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td>
                  {{
                    props.view?.first_delivery_date ? moment(props.view?.first_delivery_date).format("DD/MM/YYYY")
                      : ""
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="table-responsive">
          <table class="table table-bordered border-secondary align-middle">
            <thead>
              <tr class="table-success border-secondary">
                <th colspan="3">PART - 2: INFORMATION OF INPUT-OUTPUT COEFFICIENT <span class="text-danger">*</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="col-4">a. Goods HS Code <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td class="col-8">{{ props.view?.item?.hs_code }}</td>
              </tr>
              <tr>
                <td>b. Description of Goods <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td>{{ props.view?.item?.description }}</td>
              </tr>
              <tr>
                <td>c. Commercial Description (including Brand Name if applicable)</td>
                <td class="text-center">:</td>
                <td>{{ props.view?.item?.name }}</td>
              </tr>
              <tr>
                <td>d. Item Code <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td>{{ props.view?.item_code }}</td>
              </tr>
              <tr>
                <td>Unit of Supplies <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td>{{ props.view?.saleunit?.name }}</td>
              </tr>
              <tr>
                <td colspan="3">f. Please click here to fill in the table of “Details of Raw Materials” <span
                    class="text-danger">*</span></td>
              </tr>
              <tr>
                <td colspan="3">g. Please click here to fill in the table of “Details of Value Additions” <span
                    class="text-danger">*</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="table-responsive">
          <table class="table table-bordered border-secondary align-middle">
            <thead>
              <tr class="table-success border-secondary">
                <th colspan="3">PART - 3 : DECLARATION <span class="text-danger">*</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="col-4">Name <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td class="col-8">{{ props.view?.client?.name }}</td>
              </tr>
              <tr>
                <td>Designation <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td>Owner</td>
              </tr>
              <tr>
                <td>Mobile Number <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td>{{ props.view?.client?.mobile }}</td>
              </tr>
              <tr>
                <td>National ID/Passport Number <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td></td>
              </tr>
              <tr>
                <td>Email <span class="text-danger">*</span></td>
                <td class="text-center">:</td>
                <td>{{ props.view?.client?.email }}</td>
              </tr>
              <tr>
                <td colspan="3">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" checked>
                    <p class="m-0 ms-3">
                      <span class="text-danger">*</span> I hereby declare that all information provided in this Refund
                      Form are complete, true & accurate. In case of any untrue/incomplete statement, I may be
                      subjected
                      to penal action under The Value Added Tax and Supplementary Duty Act, 2012 or any other
                      applicable
                      Act prevailing at present.
                    </p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="border border-secondary p-3">
          <p>Special Notes:</p>
          <p>1. Within 15 working days prior to the first delivery of any product or service, the
            declaration of materials-production co-declaration must be submitted online to the VAT computer system
            or to the office of the concerned departmental officer.</p>
          <p>2. If the price of the product or the total material / raw material changes by more than
            7.5%, a new declaration has to be submitted.</p>
          <p>3. A copy of the bill of entry or invoice should be attached as an authentic document in
            support of the purchase of materials.</p>
        </div>
      </div>
      <div class="col-12 mb-2">
        <h4>Sub-form of PART - 2: f. Details of Raw Materials</h4>
        <div class="table-responsive">
          <table class="table table-bordered border-secondary text-center">
            <thead class="align-middle">
              <tr class="table-success border-secondary text-center">
                <th>Goods/ Service Code <span class="text-danger">*</span></th>
                <th>Description of Goods/Service <span class="text-danger">*</span></th>
                <th>Commercial Description (including Brand Name if applicable)</th>
                <th>Unit of Supplies <span class="text-danger">*</span></th>
                <th>Taxpayer - Product Description <span class="text-danger">*</span></th>
                <th>Taxpayer - Per Unit Product Qty. including Wastage <span class="text-danger">*</span></th>
                <th>Taxpayer - Per Unit Product Purchase Price <span class="text-danger">*</span></th>
                <th>Taxpayer - Per Unit Product Qty. of Wastage <span class="text-danger">*</span></th>
                <th>Taxpayer - Per Unit Product Wastage Percentage (%) <span class="text-danger">*</span></th>
                <th>Taxpayer - Notes</th>
              </tr>
            </thead>
            <tbody class="align-middle">
              <tr>
                <td>{{ props.view?.item?.hs_code }}</td>
                <td>{{ props.view?.item?.description }}</td>
                <td>{{ props.view?.item?.name }}</td>
                <td>{{ props.view?.saleunit?.name }}</td>
                <td>{{ props.view?.item?.name }}</td>
                <td>{{ (props.view?.purchase_unit_total).toFixed(2) }}</td>
                <td>{{ (props.view?.unit_price).toFixed(2) }}</td>
                <td>{{ `${(+(props.view?.purchase_unit_total) * +(+(props.view?.wastage) / 100)).toFixed(2)}` }}</td>
                <td>{{ (props.view?.wastage).toFixed(2) }}%</td>
                <td>
                  <p class="m-0">BoE: {{ props.view?.boe_no }}</p>
                  <p class="m-0">Date: {{ moment(props.view?.boe_date).format("DD/MM/YYYY") }}</p>
                </td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Total</td>
                <td></td>
                <td></td>
                <td>{{ props.view?.unit_price }}</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12 mb-2">
        <h4>Divisional Officer Observation:</h4>
        <div class="table-responsive">
          <table class="table table-bordered border-secondary text-center">
            <thead class="align-middle">
              <tr class="table-success border-secondary text-center">
                <th>Goods/ Service Code <span class="text-danger">*</span></th>
                <th>Description of Goods/Service <span class="text-danger">*</span></th>
                <th>Commercial Description (including Brand Name if applicable)</th>
                <th>Unit of Supplies <span class="text-danger">*</span></th>
                <th>Divisional Officer - Status <span class="text-danger">*</span></th>
                <th>Divisional Officer - Product Description <span class="text-danger">*</span></th>
                <th>Divisional Officer - Per Unit Product Qty. including Wastage <span class="text-danger">*</span>
                </th>
                <th>Divisional Officer - Per Unit Product Purchase Price <span class="text-danger">*</span></th>
                <th>Divisional Officer - Per Unit Product Qty. of Wastage <span class="text-danger">*</span></th>
                <th>Divisional Officer - Per Unit Product Wastage Percentage (%) <span class="text-danger">*</span>
                </th>
                <th>Divisional Officer - Notes</th>
              </tr>
            </thead>
            <tbody class="align-middle">
              <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12 mb-2">
        <h4>Commissioner Observation:</h4>
        <div class="table-responsive">
          <table class="table table-bordered border-secondary text-center">
            <thead class="align-middle">
              <tr class="table-success border-secondary text-center">
                <th>Goods/ Service Code <span class="text-danger">*</span></th>
                <th>Description of Goods/Service <span class="text-danger">*</span></th>
                <th>Commercial Description (including Brand Name if applicable)</th>
                <th>Unit of Supplies <span class="text-danger">*</span></th>
                <th>Commission er - Status <span class="text-danger">*</span></th>
                <th>Commission er - Product Description <span class="text-danger">*</span></th>
                <th>Commission er - Per Unit Product Qty. including Wastage <span class="text-danger">*</span></th>
                <th>Commission er - Per Unit Product Purchase Price <span class="text-danger">*</span></th>
                <th>Commission er - Per Unit Product Qty. of Wastage <span class="text-danger">*</span></th>
                <th>Commission er - Per Unit Product Wastage Percentage (%) <span class="text-danger">*</span></th>
                <th>Commission er - Notes</th>
              </tr>
            </thead>
            <tbody class="align-middle">
              <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12 mb-2">
        <h4>Sub-form of PART - 2: g. Details of Value Additions</h4>
        <div class="table-responsive">
          <table class="table table-bordered border-secondary align-middle">
            <thead>
              <tr class="table-success border-secondary">
                <th class="col-4">Taxpayer - Value Addition Items <span class="text-danger">*</span></th>
                <th class="col-4">Taxpayer - Value <span class="text-danger">*</span></th>
                <th class="col-4">Taxpayer - Notes</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="dt in props.view?.additions">
                <td>{{ dt?.costsheet?.name }}</td>
                <td>{{ dt?.value }}</td>
                <td>{{ dt?.rate }}%</td>
              </tr>
              <tr>
                <td>Total</td>
                <td>{{ props.view?.additionsum[0]?.valuesum }}</td>
                <td>{{ props.view?.additionsum[0]?.ratesum }}%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12 pmt-5">
        <h4>Divisional Officer Observation:</h4>
        <div class="table-responsive">
          <table class="table table-bordered border-secondary">
            <thead class="align-middle">
              <tr class="table-success border-secondary">
                <th class="col-4">Divisional Officer - Status <span class="text-danger">*</span></th>
                <th class="col-4">Divisional Officer - Value Addition Items <span class="text-danger">*</span></th>
                <th class="col-4">Divisional Officer - Value <span class="text-danger">*</span></th>
                <th class="col-4">Divisional Officer - Notes</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td></td>
                <td>Total</td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12">
        <h4>Commissioner Observation:</h4>
        <div class="table-responsive">
          <table class="table table-bordered border-secondary">
            <thead class="align-middle">
              <tr class="table-success border-secondary">
                <th class="col-4">Commissioner - Status <span class="text-danger">*</span></th>
                <th class="col-4">Commissioner - Value Addition Items <span class="text-danger">*</span></th>
                <th class="col-4">Commissioner - Value <span class="text-danger">*</span></th>
                <th class="col-4">Commissioner - Notes</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td></td>
                <td>Total</td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import moment from 'moment';
import { role } from '../../../composables/user';
import { empty } from '../../../helpers/utils';
import { router, Link } from '@inertiajs/vue3';

import logo from "@/assets/images/gov.png";
import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import Button from '../../../components/Button.vue';

const props = defineProps({ view: { type: Object } });

const openPdf = () => {
  if (typeof window !== 'undefined') {
    const id = route().params.id;
    window.open(route('report-43-pdf', { id: id, bin: props.view?.client?.bin }), '_blank');
  }
};

const refresh = (id, client_id, monthYear) => router.get(route('report-43-view', { id }), { client_id, monthYear }, { preserveScroll: true });
</script>

<style lang="scss" scoped></style>