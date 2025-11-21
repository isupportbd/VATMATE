<template>
  <div class="sidebar">
    <a href="#" role="button"
      class="sidebar-toggle card card-body rounded-circle position-absolute top-0 end-0 d-xl-none"
      title="sidebar-toggle">
      <i class="bi bi-chevron-left fw-bold"></i>
    </a>

    <div class="app-brand">
      <Link :href="route('dashboard')" class="fw-semibold fs-2" aria-label="logo">
      <h2 class="m-0 fw-semibold">VATMATE</h2>
      </Link>
    </div>
    <div class="accordion accordion-flush" id="accordion-sidebar">
      <ul class="list-group px-3">
        <li class="list-group-item" :class="{ 'active': $page.component === 'dashboard/index' }">
          <Link :href="route('dashboard')">
          <div class="menu-icon">
            <i class="bi bi-house"></i>
          </div>
          <span>Dashboard</span>
          </Link>
        </li>
        <li class="split-label">
          <span class="text-uppercase">setup</span>
        </li>
        <li class="list-group-item accordion-item">
          <a href="#" class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
            <div class="menu-icon">
              <i class="bi bi-shield-check"></i>
            </div>
            <span>Admin Menu</span>
          </a>
          <ul class="list-group accordion-collapse collapse" id="flush-collapseOne" data-bs-parent="#accordion-sidebar">
            <li class="list-group-item" :class="{ 'active': $page.component === 'division/index' }">
              <Link :href="route('division')">Division</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'bank/index' }">
              <Link :href="route('bank')">Bank</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'branch/index' }">
              <Link :href="route('branch')">Branch</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'item/index' }">
              <Link :href="route('item')">Item</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'costsheet/index' }">
              <Link :href="route('costsheet')">Costsheet</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'unit/index' }">
              <Link :href="route('unit')">Unit</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'stationcode/index' }">
              <Link :href="route('stationcode')">Station Code</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'vatfactor/index' }">
              <Link :href="route('vatfactor')">Vat Factor</Link>
            </li>
          </ul>
        </li>
        <li class="split-label">
          <span class="text-uppercase">Books of Accounts</span>
        </li>
        <li class="list-group-item accordion-item">
          <a href="#" class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
            <div class="menu-icon">
              <i class="bi bi-shop"></i>
            </div>
            <span>Store</span>
          </a>
          <ul class="list-group accordion-collapse collapse" id="flush-collapseTwo" data-bs-parent="#accordion-sidebar">
            <li class="list-group-item"
              :class="{ 'active': $page.component === 'form43/index' || $page.component === 'addition/index' }">
              <Link :href="route('form43')">Form 4.3</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'purchase/index' }">
              <Link :href="route('purchase')">Purchase</Link>
            </li>
            <li class="list-group-item accordion-item">
              <a href="#" class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#flush-collapseSalesOne" aria-expanded="false" aria-controls="flush-collapseSalesOne">
                <span>Sales</span>
              </a>
              <ul class="list-group accordion-collapse collapse" id="flush-collapseSalesOne"
                data-bs-parent="#accordion-sales">
                <li class="list-group-item" :class="{ 'active': $page.component === 'customer/index' }">
                  <Link :href="route('customer')">Customer</Link>
                </li>
                <li class="list-group-item" :class="{ 'active': $page.component === 'rate/index' }">
                  <Link :href="route('rate')">Rate</Link>
                </li>
                <li class="list-group-item" :class="{ 'active': $page.component === 'sale/index' }">
                  <Link :href="route('sale')">Supply</Link>
                </li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="list-group-item"
          :class="{ 'active': $page.component === 'form621/index' || $page.component === 'form621/Form621IndividualTable' }">
          <Link :href="route('form621')">
          <div class="menu-icon"><i class="bi bi-ui-checks"></i></div>
          <span>Form 6.2.1</span>
          </Link>
        </li>
        <li class="list-group-item" :class="{ 'active': $page.component === 'creditnote/index' }">
          <Link :href="route('creditnote')">
          <div class="menu-icon"><i class="bi bi-list-check "></i></div>
          <span>Credit Note (Form 6.7)</span>
          </Link>
        </li>
        <li class="list-group-item" :class="{ 'active': $page.component === 'debitnote/index' }">
          <Link :href="route('debitnote')">
          <div class="menu-icon"><i class="bi bi-list-task"></i></div>
          <span>Debit Note (Form 6.8)</span>
          </Link>
        </li>
        <li class="list-group-item" :class="{ 'active': $page.component === 'form44/index' }">
          <Link :href="route('form44')">
          <div class="menu-icon"><i class="bi bi-list-stars"></i></div>
          <span>Form 4.4</span>
          </Link>
        </li>
        <li class="list-group-item" :class="{ 'active': $page.component === 'form45/index' }">
          <Link :href="route('form45')">
          <div class="menu-icon"><i class="bi bi-list-ul"></i></div>
          <span>Form 4.5</span>
          </Link>
        </li>
        <li class="list-group-item" :class="{ 'active': $page.component === 'form46/index' }">
          <Link :href="route('form46')">
          <div class="menu-icon"><i class="bi bi-list-ol"></i></div>
          <span>Form 4.6</span>
          </Link>
        </li>
        <li class="list-group-item" :class="{ 'active': $page.component === 'adjustment/index' }">
          <Link :href="route('adjustment')">
          <div class="menu-icon"><i class="bi bi-wrench-adjustable-circle"></i></div>
          <span>Adjustment</span>
          </Link>
        </li>
        <li class="list-group-item" :class="{ 'active': $page.component === 'treasury/index' }">
          <Link :href="route('treasury')">
          <div class="menu-icon"><i class="bi bi-gem"></i></div>
          <span>Treasury</span>
          </Link>
        </li>
        <li class="split-label">
          <span class="text-uppercase">Reports</span>
        </li>
        <li class="list-group-item accordion-item">
          <a href="#" class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
            <div class="menu-icon">
              <i class="bi bi-flag"></i>
            </div>
            <span>Reports</span>
          </a>
          <ul class="list-group accordion-collapse collapse" id="flush-collapseThree"
            data-bs-parent="#accordion-sidebar">
            <li class="list-group-item">
              <Link href="#">Report 4.3</Link>
            </li>
            <li class="list-group-item">
              <Link href="#">Report 6.3</Link>
            </li>
            <li class="list-group-item">
              <Link href="#">Report 6.2.1</Link>
            </li>
            <li class="list-group-item">
              <Link href="#">Report Summary</Link>
            </li>
            <li class="list-group-item">
              <Link href="#">User Summary</Link>
            </li>
            <li class="list-group-item">
              <Link href="#">Search BoE</Link>
            </li>
          </ul>
        </li>
        <li class="split-label">
          <span class="text-uppercase">Auth</span>
        </li>
        <li class="list-group-item accordion-item">
          <a href="#" class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
            <div class="menu-icon">
              <i class="bi bi-person-circle"></i>
            </div>
            <span>Person</span>
          </a>
          <ul class="list-group accordion-collapse collapse" id="flush-collapseFour"
            data-bs-parent="#accordion-sidebar">
            <li class="list-group-item"
              :class="{ 'active': $page.component === 'authentication/index', 'd-none': !role('admin') }">
              <Link :href="route('user')">Users</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'client/index' }">
              <Link :href="route('client')">Clients</Link>
            </li>
            <li class="list-group-item" :class="{ 'active': $page.component === 'economicarea/index' }">
              <Link :href="route('economicarea')">Economic Area</Link>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { role, user } from '../../composables/user';

const page = usePage();
const storageKey = `sidebar:openIds:${page.url}`;

// Keep a Set of open collapse IDs (e.g., ['flush-collapseOne','flush-collapseTwo'])
const openIds = ref(new Set());

// Helpers
const save = () => {
  try {
    localStorage.setItem(storageKey, JSON.stringify([...openIds.value]));
  } catch { }
};

const load = () => {
  try {
    const raw = localStorage.getItem(storageKey);
    const arr = raw ? JSON.parse(raw) : [];
    openIds.value = new Set(Array.isArray(arr) ? arr : []);
  } catch {
    openIds.value = new Set();
  }
};

let cleanup = [];

onMounted(() => {
  load();

  const root = document.querySelector('.sidebar'); // scope to this sidebar only
  if (!root) return;

  // Restore saved "open" states
  openIds.value.forEach((id) => {
    const panel = root.querySelector(`#${CSS.escape(id)}`);
    if (panel) {
      panel.classList.add('show');
      // also un-collapse its trigger button for correct caret/aria
      const trigger = root.querySelector(`[data-bs-target="#${CSS.escape(id)}"]`);
      if (trigger) {
        trigger.classList.remove('collapsed');
        trigger.setAttribute('aria-expanded', 'true');
      }
    }
  });

  // Listen to Bootstrap collapse events to keep list in sync
  const panels = root.querySelectorAll('.accordion-collapse');
  panels.forEach((el) => {
    const onShown = () => {
      openIds.value.add(el.id);
      save();
    }
    const onHidden = () => {
      openIds.value.delete(el.id);
      save();
    }
    el.addEventListener('shown.bs.collapse', onShown);
    el.addEventListener('hidden.bs.collapse', onHidden);
    cleanup.push(() => {
      el.removeEventListener('shown.bs.collapse', onShown);
      el.removeEventListener('hidden.bs.collapse', onHidden);
    });
  });
})

onBeforeUnmount(() => {
  cleanup.forEach((fn) => fn());
  cleanup = [];
});
</script>

<style lang="scss" scoped></style>