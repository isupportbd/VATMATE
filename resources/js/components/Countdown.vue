<template>
  <div v-bind="$attrs">
    <div class="ms-auto timer" v-if="!timeField.finishtime">
      <div class="position-relative">
        <span class="position-absolute start-0 top-0 bg-primary border border-light badge badge-top"
          :class="{ 'bg-danger': timeField.gap < (24 * 60 * 60 * 1000 * 1) }">
          <span>Tax Period</span>
        </span>
        <div class="alert alert-primary py-0 px-1 m-0 text-center"
          :class="{ 'text-danger': timeField.gap < (24 * 60 * 60 * 1000 * 1) }" style="width: 70px; height: 23px;">
          {{ moment(timeField.databaseBindTime?.tax_period).format('MMM YYYY') }}
        </div>
      </div>
      <div class="position-relative">
        <div class="alert alert-primary py-0 px-1 m-0 text-center"
          :class="{ 'text-danger': timeField.gap < (24 * 60 * 60 * 1000 * 1) }" style="width: 100px; height: 23px;">
          {{ timeField.timeleft }}
        </div>
        <span class="position-absolute start-0 top-0 bg-success border border-light badge badge-top"
          :class="{ 'bg-danger': timeField.gap < (24 * 60 * 60 * 1000 * 1) }">
          <span>Time left</span>
        </span>
      </div>
    </div>
    <div class="ms-auto timer" v-else>
      <div class="position-relative">
        <span class="position-absolute start-0 top-0 bg-primary border border-light badge badge-top">
          <span>Tax Period</span>
        </span>
        <div class="alert alert-primary py-0 px-1 m-0 text-center" style="width: 70px; height: 23px;">&nbsp;</div>
      </div>
      <div class="position-relative">
        <div class="alert alert-primary py-0 px-1 m-0 text-center" style="width:100px; height: 23px;">&nbsp;</div>
        <span class="position-absolute end-0 bottom-0 bg-success border border-light badge badge-bottom">
          <span>Time left</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import moment from 'moment-timezone';
import { reactive, onMounted, onBeforeUnmount } from 'vue';

// countdown timer for sector officer and admin data input
const timeField = reactive({ databaseBindTime: '', timeleft: '', gap: '', countDown: '', finishtime: false });

const endtime = async () => await axios.get(route('collectionbind-current-month')).then((res) => {
  timeField.databaseBindTime = res.data.collectionbind;
});

const timer = () => {
  const countDate = timeField.databaseBindTime ? new Date(timeField.databaseBindTime?.end_date).getTime() : 0;
  const now = new Date().getTime();
  const gap = countDate - now;
  timeField.gap = gap || 0;

  // time variable
  const ss = 1000;
  const mm = ss * 60;
  const hh = mm * 60;
  const dd = hh * 24;

  // calculate time
  const textDay = Math.floor(gap / dd) || 0;
  const textHour = Math.floor((gap % dd) / hh) || 0;
  const textMinute = Math.floor((gap % hh) / mm) || 0;
  const textSecond = Math.floor((gap % mm) / ss) || 0;

  const day = (textDay < 2) ? '0' + textDay + 'd' : (textDay < 10) ? '0' + textDay + 'ds' : textDay + 'ds';
  const hour = (textHour < 10) ? '0' + textHour : textHour;
  const minute = (textMinute < 10) ? '0' + textMinute : textMinute;
  const second = (textSecond < 10) ? '0' + textSecond : textSecond;

  timeField.timeleft = `${day} ${hour}:${minute}:${second}`;

  if (gap < ss) {
    timeField.finishtime = true;
    clearInterval(timeField.countDown);
  }
  // console.log(gap, props.databaseBindTime?.end_date);
};

onMounted(async () => {
  await endtime();
  timer(); // immediate render
  timeField.countDown = setInterval(timer, 1000);

  window.Echo.channel('collectionbind-event').listen('.CollectionbindEvent', async (event) => {
    await endtime();
  });
});

onBeforeUnmount(() => {
  clearInterval(timeField.countDown);
  window.Echo.channel('collectionbind-event').stopListening('.CollectionbindEvent');
  window.Echo.leave('collectionbind-event');
});

defineExpose({ timefield: timeField });
</script>

<style lang="scss" scoped>
.timer {
  font-size: 12px;
  display: flex;
  gap: 5px;
}

.badge-position {
  z-index: 10000;
  font-size: 8px;
}

.badge-top {
  @extend .badge-position;
  margin-top: -15px;
}

.badge-bottom {
  @extend .badge-position;
  margin-bottom: -15px;
}
</style>