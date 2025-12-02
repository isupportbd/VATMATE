<template>
  <header class="removeprintarea">
    <nav class="navbar bg-transparent p-0 row-gap-1 column-gap-1">
      <button type="button" class="nav-btn order-0" id="off-canvas" title="Toggle Button">
        <i class="bi bi-layout-text-sidebar-reverse"></i>
      </button>
      <div id="pagebar" class="card card-body nav-card order-5 order-sm-1"></div>
      <div class="btn-group order-3 order-sm-3">
        <button type="button" class="nav-btn w-100" data-bs-toggle="dropdown" data-bs-display="static"
          aria-expanded="false" aria-label="button">
          <i class="bi bi-person-circle"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="#">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                  <i class="bi bi-person-circle fs-1"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0 text-capitalize">{{ user?.name }}</h6>
                  <small class="text-muted">{{ user?.role }}</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <Link class="dropdown-item" :href="route('profile-get')">
            <i class="bi bi-person-check me-3"></i><span>My Profile</span>
            </Link>
          </li>
          <li>
            <Link class="dropdown-item" :href="route('user-logout')" method="post" as="button" type="button">
            <i class="bi bi-power me-3"></i><span>Log Out</span>
            </Link>
          </li>
        </ul>
      </div>
      <div id="refresh" class="order-4 order-sm-2 d-flex row-gap-1 column-gap-1"></div>
      <button type="button" class="nav-btn order-1 order-sm-4" id="theme-switch" title="mode" aria-label="button">
        <i class="bi bi-circle-half"></i>
      </button>
    </nav>
  </header>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { role, user } from '../../composables/user';
</script>

<style lang="scss" scoped></style>