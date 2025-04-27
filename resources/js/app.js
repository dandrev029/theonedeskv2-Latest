import '@/plugins/lodash';
import '@/plugins/moment';
import '@/plugins/ladda';
import '@/plugins/axios';
import '@/plugins/filters';
import '@/plugins/darkmode';
import '@/utilities/dark-mode-index';
import "@/components";
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Enable Pusher logging in development
Pusher.logToConsole = process.env.NODE_ENV !== 'production';

// Initialize Laravel Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: window.app && window.app.pusher_key ? window.app.pusher_key : process.env.MIX_PUSHER_APP_KEY,
    cluster: window.app && window.app.pusher_cluster ? window.app.pusher_cluster : process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    }
});

// Log connection status
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Pusher connected successfully');
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('Pusher connection error:', err);
});

// Log Pusher configuration for debugging
console.log('Pusher configuration:', {
    key: window.app && window.app.pusher_key ? window.app.pusher_key : process.env.MIX_PUSHER_APP_KEY,
    cluster: window.app && window.app.pusher_cluster ? window.app.pusher_cluster : process.env.MIX_PUSHER_APP_CLUSTER
});

import Vue from "vue";
import Vuex from 'vuex';
import Meta from 'vue-meta';
import SvgVue from 'svg-vue';
import Notifications from 'vue-notification';
import {VueReCaptcha} from "vue-recaptcha-v3";
import VueElementLoading from 'vue-element-loading';
import TextareaAutosize from 'vue-textarea-autosize';
import vueFilterPrettyBytes from 'vue-filter-pretty-bytes';
import { mixin as clickaway } from 'vue-clickaway';

// Custom directives
import IntersectDirective from '@/directives/intersect';
import '@/directives/dark-mode';

import store from '@/store';
import App from "@/views/app";
import i18n from "@/language";
import router from "@/views/router";

Vue.use(Vuex);
Vue.use(Meta);
Vue.use(SvgVue);
Vue.use(Notifications);
Vue.use(TextareaAutosize);
Vue.use(vueFilterPrettyBytes);
Vue.component('VueElementLoading', VueElementLoading);

// Register custom directives
Vue.directive('intersect', IntersectDirective);
Vue.directive('on-clickaway', clickaway);
if (window.app.recaptcha_enabled) {
    Vue.use(VueReCaptcha, {siteKey: window.app.recaptcha_public});
}

Vue.config.productionTip = false;

new Vue({
    i18n,
    store,
    router,
    render: h => h(App),
    mounted() {
        this.initI18n();
        this.$store.commit('setUser');
        this.$store.commit('setSettings', window.app);
        this.initDarkMode();
    },
    methods: {
        initI18n() {
            this.$i18n.locale = document.documentElement.lang;
            this.loadTranslations();
        },
        loadTranslations() {
            let self = this;
            axios.get('api/lang/' + self.$i18n.locale).then(function (response) {
                self.$i18n.setLocaleMessage(self.$i18n.locale, response.data);
            });
        },
        initDarkMode() {
            // Apply dark mode if it's enabled in the store
            if (this.$store.state.darkMode) {
                document.body.classList.add('dark-mode');
                // Set a data attribute on the html element for CSS targeting
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                document.documentElement.setAttribute('data-theme', 'light');
            }

            // Add a listener for system preference changes
            this.setupSystemDarkModeListener();
        },

        setupSystemDarkModeListener() {
            // Check if the browser supports matchMedia
            if (window.matchMedia) {
                const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

                // Add change listener
                try {
                    // Chrome & Firefox
                    darkModeMediaQuery.addEventListener('change', e => {
                        this.handleSystemDarkModeChange(e.matches);
                    });
                } catch (e1) {
                    try {
                        // Safari
                        darkModeMediaQuery.addListener(e => {
                            this.handleSystemDarkModeChange(e.matches);
                        });
                    } catch (e2) {
                        console.error('Could not setup dark mode listener:', e2);
                    }
                }
            }
        },

        handleSystemDarkModeChange(isDarkMode) {
            // Only apply system preference if user hasn't explicitly set a preference
            if (localStorage.getItem('darkMode') === null) {
                this.$store.state.darkMode = isDarkMode;
                if (isDarkMode) {
                    document.body.classList.add('dark-mode');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.body.classList.remove('dark-mode');
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            }
        },
    }
}).$mount("#app");
