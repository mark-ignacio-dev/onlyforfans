/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const isProduction = process.env.NODE_ENV === 'production';

import store from './store';

require('./bootstrap');
require('./bootstrap/fontAwesome');
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//axios.defaults.baseURL = '/';


import Vue from 'vue';

/**
 * Enable $log
 * Use: `this.$log.error(error)`
 * logLevels : ['debug', 'info', 'warn', 'error', 'fatal']
 */
import VueLogger from 'vuejs-logger';
const options = {
    isEnabled: true,
    logLevel : isProduction ? 'error' : 'debug',
    stringifyArguments : false,
    showLogLevel : true,
    showMethodName : !isProduction,
    separator: '|',
    showConsoleColors: true
};
Vue.use(VueLogger, options);

import VueAxios from 'vue-axios';
Vue.use(VueAxios, window.axios);

import VueI18n from 'vue-i18n';
Vue.use(VueI18n);

import ForceCompute from './plugins/forceCompute';
Vue.use(ForceCompute);

import WhenAvailable from './plugins/whenAvailable';
Vue.use(WhenAvailable);

//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
Vue.use(BootstrapVue) // Telling Vue to use this in whole application
Vue.use(BootstrapVueIcons)

import VueTimeago from 'vue-timeago';
Vue.use(VueTimeago, {
  name: 'Timeago', // Component name, `Timeago` by default
  locale: 'en', // Default locale
  /*
  // We use `date-fns` under the hood
  // So you can use all locales from it
  locales: {
    'zh-CN': require('date-fns/locale/zh_cn'),
    ja: require('date-fns/locale/ja')
  }
  */
});

// ---

/**
 * Loading localization translations
 */
import i18n from './i18n'

/**
 * Route Loading for Guest
 */
import VueRouter from 'vue-router'
Vue.use(VueRouter)
import router from './routes/guest.routes'
import App from './views/templates/Guest.vue'

const app = new Vue({
  router,
  i18n,
  store,
  render: h => h(App),
}).$mount('#app');
