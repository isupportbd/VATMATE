import './echo';
import './bootstrap';

import { createApp, h, ref } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Layout from './layouts/Layout/index.vue';

// resource js already bootstrap load in custom.scss. no need import here
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../assets/js/darkLightBeforeCss';
import '../assets/js/main';
import '../assets/scss/custom.scss';

createInertiaApp({
  title: (title) => `Vatmate | ${title}`,
  resolve: (name) => {
    // Lazy pages (code-splitting)
    const pages = import.meta.glob('./pages/**/*.vue');
    const importer = pages[`./pages/${name}.vue`];
    if (!importer) throw new Error(`Page not found: ./pages/${name}.vue`);
    return importer().then((mod) => {
      const page = mod.default;
      page.layout ??= Layout;   // default layout
      return page;              // return the component
    });
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .component('Head', Head)
      .component('Link', Link);

    // Global toast provide (as you had)
    const toastRef = ref(null);
    app.provide('notify', toastRef);
    app.provide('showToast', (msg, type = 'primary') =>
      toastRef.value?.toastme(msg, type)
    );

    app.mount(el);
  },
  progress: { color: '#29d', includeCSS: true, showSpinner: false },
});
