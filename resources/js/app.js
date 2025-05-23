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
const pusherKey = window.app && window.app.pusher_key ? window.app.pusher_key : process.env.MIX_PUSHER_APP_KEY;
const pusherCluster = window.app && window.app.pusher_cluster ? window.app.pusher_cluster : process.env.MIX_PUSHER_APP_CLUSTER;

if (!pusherKey || !pusherCluster) {
    console.error('Pusher key or cluster is missing. Check window.app object or .env file.');
}

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: pusherKey,
    cluster: pusherCluster,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': localStorage.getItem('token') ? 'Bearer ' + localStorage.getItem('token') : ''
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
console.log('Pusher configuration being used:', {
    key: pusherKey,
    cluster: pusherCluster,
    authEndpoint: '/broadcasting/auth'
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
// Vue.use(SvgVue); // Will be configured below
Vue.use(Notifications);
Vue.use(TextareaAutosize);
Vue.use(vueFilterPrettyBytes);
Vue.component('VueElementLoading', VueElementLoading);

// Chat components
Vue.component('ChatHeader', require('./components/chat/ChatHeader.vue').default);
Vue.component('ChatMessage', require('./components/chat/ChatMessage.vue').default);
Vue.component('ChatInput', require('./components/chat/ChatInput.vue').default);
Vue.component('ChatInterface', require('./components/chat/ChatInterface.vue').default);

// Register custom directives
Vue.directive('intersect', IntersectDirective);
Vue.directive('on-clickaway', clickaway);

// SvgVue configuration

// Load non-FontAwesome SVGs from local resources/svg directory
// (e.g., 'undraw/something', 'file-extension/pdf')
// This context excludes the 'font-awesome' subdirectory within resources/svg.
const localSvgContext = require.context('@/../svg', true, /^\.\/(?!font-awesome\/).*\.svg$/);
let allIcons = localSvgContext.keys().reduce((icons, path) => {
    const iconModule = localSvgContext(path);
    // name will be like 'undraw/page-not-found' or 'file-extension/pdf'
    const name = path.replace(/^\.\/(.*)\.svg$/, '$1');
    icons[name] = iconModule.default || iconModule;
    return icons;
}, {});

// Define contexts for Font Awesome styles from node_modules
// Add or remove styles as needed (e.g., 'light', 'duotone' - ensure they are in your FA package)
const faStyleContexts = {
    'solid': require.context('@fortawesome/fontawesome-free/svgs/solid', false, /\.svg$/),
    'regular': require.context('@fortawesome/fontawesome-free/svgs/regular', false, /\.svg$/),
    'brands': require.context('@fortawesome/fontawesome-free/svgs/brands', false, /\.svg$/)
};

// Populate allIcons with Font Awesome icons
for (const style in faStyleContexts) {
    const context = faStyleContexts[style];
    context.keys().forEach(filePath => { // filePath is like './search.svg' or './user.svg'
        const iconModule = context(filePath);
        const iconName = filePath.replace(/^\.\/(.*)\.svg$/, '$1'); // iconName is like 'search' or 'user'
        
        // Construct the key to match original convention used by svg-vue with your templates
        // e.g., template "font-awesome.search-solid" expects key "font-awesome/search-solid"
        const key = `font-awesome/${iconName}-${style}`; 
        allIcons[key] = iconModule.default || iconModule;
    });
}

Vue.use(SvgVue, {
  icons: allIcons,
  defaultAttributes: {
    width: '1em',
    height: '1em',
    fill: 'currentColor',
  },
});


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
