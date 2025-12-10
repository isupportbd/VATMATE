<template>

  <Head title="Files" />
  <PageBar>
    <p class="m-0 fs-4">Files</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat' @click="refresh" />
  </Refresh>

  <div class="card card-body mb-2" :class="{ 'd-none': !role('admin') }">
    <form class="mx-auto" @submit.prevent="uploadFile">
      <div :class="{ 'border border-danger rounded-2': err }">
        <!-- <div class="input-group" role="group">
          <Input type="file" @change="handleFileChange" topclass="mb-0" :disabled="form.processing" />
          <Button type="submit" class="btn btn-outline-secondary py-0 px-3" title="upload" :disabled="form.processing">
            <span v-if="form.progress">{{ form.progress.percentage }}%</span>
            <span class="fw-bold" v-else><i class="bi bi-upload fs-4"></i></span>
          </Button>
          <Button type="button" @click="resetForm" class="btn btn-outline-secondary" icon="bi bi-stars" title="reset" />
        </div> -->
        <div class="input-group">
          <input type="file" @change="handleFileChange" class="form-control" id="upload" :disabled="form.processing" />
          <button class="btn btn-outline-secondary" type="submit" id="upload">
            <span v-if="form.progress">{{ form.progress.percentage }}%</span>
            <span class="fw-bold" v-else><i class="bi bi-upload"></i></span>
          </button>
          <Button type="button" @click="resetForm" class="btn btn-outline-secondary" icon="bi bi-stars" title="reset" />
        </div>
      </div>
      <div class="text-danger" style="font-size: 11px;">{{ err }}</div>
    </form>
  </div>

  <DataTableSkeleton row="5" col="4" v-if="empty(props.files)" />
  <DataTable :data="props.files" :search="props.search" :option="[5, 10, 15]" :removable="false" v-else>
    <template #thead>
      <th class="col-7">Name</th>
      <th class="col-2 text-center">Size</th>
      <th class="col-2">Date</th>
      <th class="col-1 text-center">Action</th>
    </template>
    <template #tbody="{ td }">
      <td>{{ td.name }}</td>
      <td class="text-center">{{ td.size }}</td>
      <td>{{ moment(td.created_at) }}</td>
      <td class="text-center">
        <div class="btn-group" role="group">
          <Href :to="route('report-file-download', { file: td.id })" icon="bi bi-download" class="btn btn-outline-dark"
            :class="{ 'rounded': !role('admin') }" />
          <Button type="button" title="delete" class="btn btn-outline-danger" :class="{ 'd-none': !role('admin') }"
            icon="bi bi-trash" @click="deleteFile(td)" />
        </div>
      </td>
    </template>
  </DataTable>
</template>

<script setup>
import { inject, ref, onMounted, onBeforeUnmount } from 'vue';
import moment from 'moment-timezone';
import { router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { empty } from '../../../helpers/utils';
import { user, role } from '../../../composables/user';

import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import Confirm from '../../../helpers/customConfirm';
import DataTableSkeleton from '../../../components/datatable/DataTableSkeleton.vue';
import DataTable from '../../../components/datatable/index.vue';
import Input from '../../../components/Input.vue';
import Button from '../../../components/Button.vue';
import Href from '../../../components/Href.vue';

const props = defineProps({
  files: { type: [Array, Object] },
  search: { type: String }
});

const notify = inject("showToast"); // toast

// ✅ Allow both .xlsx and .xlsb files
const allowedTypes = [
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
  'application/vnd.ms-excel.sheet.binary.macroEnabled.12'              // .xlsb
];

// Helper function to validate MIME type or extension
const isAllowedFile = (file) => {
  const ext = file.name.split('.').pop().toLowerCase();
  return allowedTypes.includes(file.type) || ['xlsx', 'xlsb'].includes(ext);
};

const MAX_FILE_SIZE = 10 * 1024 * 1024;
const err = ref(null);
const uploadProgress = ref(0);

const form = useForm({
  file: null,
  fileInput: null // We'll store the event target here
});

const handleFileChange = (event) => {
  form.file = event.target.files[0];
  form.fileInput = event.target; // Store the input element
};

const uploadFile = (e) => {
  uploadProgress.value = 0;
  form.processing = true;

  if (!form.file) {
    form.processing = false;
    return err.value = 'File required';
  }

  if (!isAllowedFile(form.file)) {
    form.processing = false;
    return err.value = 'File must be .xlsx or .xlsb';
  }

  if (form.file?.size > MAX_FILE_SIZE) {
    form.processing = false;
    return err.value = `Too large, max size will be ${((MAX_FILE_SIZE / 1024) / 1024)}MB`;
  }

  form.post(route('report-file-store'), {
    forceFormData: true,
    preserveScroll: true,
    onProgress: (progress) => {
      // uploadProgress.value = Math.round(progress.percentage);
      if ((form.file?.size <= MAX_FILE_SIZE) && (progress.lengthComputable)) {
        uploadProgress.value = Math.round((progress.loaded * 100) / progress.total);
      }
    },
    onSuccess: (e) => {
      (uploadProgress.value = 0);
      form.fileInput && (form.fileInput.value = '');
      form.reset();
      form.fileInput = null;
      notify(e.props.flash.msg, 'success');
    },
    onError: (errors) => {
      let er = {};
      Object.keys(errors).map((key) => er[key] = errors[key].toString());
      err.value = er.file;
      uploadProgress.value = 0;
    },
    onFinish: () => {
      form.processing = false;
    }
  });
};

const deleteFile = (file) => {
  Confirm('Do you want to delete ?', async (res) => {
    if (res) {
      router.delete(route('report-file-destroy', file.id), {
        preserveScroll: true,
        onSuccess: (e) => {
          notify(e.props.flash.msg, 'danger');
        }
      });
    }
  });
};

const resetForm = () => {
  err.value = "";
  form.fileInput && (form.fileInput.value = '');
  form.reset();
  form.fileInput = null;
  uploadProgress.value = 0;

  console.log(form.fileInput);
};

// reload item
const refresh = () => router.get(route('report-files'), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['files'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<style lang="scss" scoped></style>