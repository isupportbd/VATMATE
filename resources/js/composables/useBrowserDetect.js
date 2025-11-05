// src/composables/useBrowserDetect.js
import { computed } from 'vue';

export default function useBrowserDetect() {
  const userAgent = typeof navigator !== 'undefined' ? navigator.userAgent.toLowerCase() : '';

  const isFirefox = computed(() => userAgent.includes('firefox'));
  const isChrome = computed(() => userAgent.includes('chrome') && !userAgent.includes('edg') && !userAgent.includes('opr') && !userAgent.includes('brave'));
  const isSafari = computed(() => userAgent.includes('safari') && !userAgent.includes('chrome') && !userAgent.includes('android'));
  const isEdge = computed(() => userAgent.includes('edg'));
  const isOpera = computed(() => userAgent.includes('opr') || userAgent.includes('opera'));
  const isBrave = computed(() => userAgent.includes('brave'));
  const isAndroid = computed(() => userAgent.includes('android'));
  const isIOS = computed(() => /iphone|ipad|ipod/.test(userAgent));
  const isWindows = computed(() => userAgent.includes('windows'));
  const isMac = computed(() => userAgent.includes('macintosh'));

  return {
    isFirefox,
    isChrome,
    isSafari,
    isEdge,
    isOpera,
    isBrave,
    isAndroid,
    isIOS,
    isWindows,
    isMac,
    userAgent, // (if you want to check raw UA string)
  }
}
