<template>
    <nav :class="[bgPrimary, 'shadow-sm border-b', borderColor]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <router-link class="flex" to="/">
                    <logo div-padding :text-class="$store.state.darkMode ? 'text-white' : 'text-gray-800'"></logo>
                </router-link>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <!-- Notification Dropdown Component -->
                    <notification-dropdown ref="notificationDropdown" v-on-clickaway="closeNotificationDropdown" />

                    <!-- Dark Mode Toggle -->
                    <div class="flex items-center ml-2 mr-1">
                        <span class="text-xs mr-2 hidden sm:inline" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'">{{ $store.state.darkMode ? $t('Light') : $t('Dark') }}</span>
                        <dark-mode-toggle />
                    </div>

                    <div class="ml-3 relative">
                        <template v-if="$store.state.user">
                            <div v-on-clickaway="closeDropdown" class="ml-3 relative">
                                <button
                                    id="user-menu"
                                    aria-haspopup="true"
                                    aria-label="User menu"
                                    class="max-w-xs flex items-center text-sm rounded-full focus:outline-none focus:shadow-outline"
                                    @click="dropdownOpen = !dropdownOpen"
                                >
                                    <img
                                        :src="$store.state.user.avatar === 'gravatar' ? $store.state.user.gravatar : $store.state.user.avatar"
                                        alt="User avatar"
                                        class="h-8 w-8 rounded-full"
                                    />
                                </button>
                                <transition
                                    duration="100"
                                    enter-active-class="transition ease-out duration-100"
                                    enter-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95"
                                >
                                    <div v-show="dropdownOpen" class="origin-top-right z-10 absolute right-0 mt-2 w-56 rounded-md shadow-lg">
                                        <div aria-labelledby="user-menu" aria-orientation="vertical" class="py-1 rounded-md bg-white shadow-xs" role="menu">
                                            <div class="flex items-center px-4 ce py-2 border-b border-gray-100">
                                                <img
                                                    :src="$store.state.user.avatar === 'gravatar' ? $store.state.user.gravatar : $store.state.user.avatar"
                                                    alt="User avatar"
                                                    class="h-8 w-8 mr-3 align-middle rounded-full"
                                                />
                                                <div class="w-40">
                                                    <div class="text-sm font-medium truncate text-gray-800">{{ $store.state.user.name }}</div>
                                                    <div class="text-xs truncate text-gray-500">{{ $store.state.user.email }}</div>
                                                </div>
                                            </div>
                                            <router-link
                                                v-if="$store.state.user.role.dashboard_access"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition ease-in-out duration-150"
                                                role="menuitem"
                                                to="/dashboard/home"
                                                @click.native="dropdownOpen = false"
                                            >
                                                {{ $t('Dashboard') }}
                                            </router-link>
                                            <router-link
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition ease-in-out duration-150"
                                                role="menuitem"
                                                to="/tickets/list"
                                                @click.native="dropdownOpen = false"
                                            >
                                                {{ $t('My tickets') }}
                                            </router-link>
                                            <router-link
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition ease-in-out duration-150"
                                                role="menuitem"
                                                to="/account"
                                                @click.native="dropdownOpen = false"
                                            >
                                                {{ $t('Account settings') }}
                                            </router-link>
                                            <a
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition ease-in-out duration-150"
                                                href="/auth/logout"
                                                role="menuitem"
                                                @click.prevent="signOut"
                                            >
                                                {{ $t('Sign out') }}
                                            </a>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </template>
                        <template v-else>
                            <router-link
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                to="/auth/login"
                            >
                                {{ $t('Sign In') }}
                            </router-link>
                        </template>
                    </div>
                </div>
                <div class="-mr-2 flex items-center sm:hidden">
                    <button
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                        @click="menuOpen = !menuOpen"
                    >
                        <svg class="block h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                        <svg class="hidden h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div v-show="menuOpen" class="sm:hidden">
            <div class="py-3 border-t" :class="[bgPrimary, borderColor]">
                <!-- Mobile notifications -->
                <div v-if="$store.state.user" class="flex items-center px-4 py-3 border-b" :class="borderColor">
                    <button
                        class="flex items-center w-full focus:outline-none"
                        @click="openMobileNotifications"
                    >
                        <div class="relative">
                            <svg-vue class="h-6 w-6 p-px text-gray-400" icon="font-awesome.bell-regular"></svg-vue>
                            <span v-if="unreadNotificationCount > 0" class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                {{ unreadNotificationCount > 9 ? '9+' : unreadNotificationCount }}
                            </span>
                        </div>
                        <span class="ml-3 text-sm font-medium" :class="textSecondary">{{ $t('Notifications') }}</span>
                    </button>
                </div>

                <!-- Dark Mode Toggle for Mobile -->
                <div class="flex items-center justify-between px-4 py-3 border-t" :class="borderColor">
                    <span class="text-sm font-medium" :class="textSecondary">{{ $t('Dark Mode') }}</span>
                    <dark-mode-toggle />
                </div>

                <template v-if="$store.state.user">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <img
                                :src="$store.state.user.avatar === 'gravatar' ? $store.state.user.gravatar : $store.state.user.avatar"
                                alt="User avatar"
                                class="h-10 w-10 align-middle rounded-full"
                            />
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium leading-6" :class="$store.state.darkMode ? 'text-white' : 'text-gray-800'">{{ $store.state.user.name }}</div>
                            <div class="text-sm font-medium leading-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'">{{ $store.state.user.email }}</div>
                        </div>
                    </div>
                    <div aria-labelledby="user-menu" aria-orientation="vertical" class="mt-3" role="menu">
                        <router-link
                            v-if="$store.state.user.role.dashboard_access"
                            class="block px-4 py-2 text-sm transition ease-in-out duration-150"
                            :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                            role="menuitem"
                            to="/dashboard/home"
                            @click.native="menuOpen = false"
                        >
                            {{ $t('Dashboard') }}
                        </router-link>
                        <router-link
                            class="block px-4 py-2 text-sm transition ease-in-out duration-150"
                            :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                            role="menuitem"
                            to="/tickets/list"
                            @click.native="menuOpen = false"
                        >
                            {{ $t('My tickets') }}
                        </router-link>
                        <router-link
                            class="block px-4 py-2 text-sm transition ease-in-out duration-150"
                            :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                            role="menuitem"
                            to="/account"
                            @click.native="menuOpen = false"
                        >
                            {{ $t('Account settings') }}
                        </router-link>
                        <a
                            class="block px-4 py-2 text-sm transition ease-in-out duration-150"
                            :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                            href="/auth/logout"
                            role="menuitem"
                            @click.prevent="signOut"
                        >
                            {{ $t('Sign out') }}
                        </a>
                    </div>
                </template>
                <template v-else>
                    <router-link
                        class="block px-4 py-2 text-sm transition ease-in-out duration-150"
                        :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                        role="menuitem"
                        to="/auth/login"
                    >
                        {{ $t('Sign In') }}
                    </router-link>
                </template>
            </div>
        </div>
    </nav>
</template>

<script>
import Vue from 'vue';
import {mixin as clickaway} from '../../../utilities/vue-clickaway-compat';
import Logo from "@/components/layout/shared/logo";
import NotificationDropdown from "@/components/notifications/NotificationDropdown";
import DarkModeToggle from "@/components/elements/dark-mode-toggle";
import DarkModeMixin from "@/mixins/dark-mode-mixin";

export default {
    name: "navbar",
    components: {Logo, NotificationDropdown, DarkModeToggle},
    mixins: [clickaway, DarkModeMixin],
    data() {
        return {
            menuOpen: false,
            dropdownOpen: false,
            unreadNotificationCount: 0,
            mobileNotificationsOpen: false
        }
    },
    created() {
        // Listen for notification updates
        this.$root.$on('notification-count-updated', (count) => {
            this.unreadNotificationCount = count;
        });
    },
    methods: {
        signOut() {
            this.menuOpen = false;
            this.dropdownOpen = false;
            this.$store.commit('logout');
            this.$router.push('/auth/login');
        },
        closeDropdown() {
            this.dropdownOpen = false;
        },
        closeNotificationDropdown() {
            if (this.$refs.notificationDropdown) {
                this.$refs.notificationDropdown.closeDropdown();
            }
        },
        openMobileNotifications() {
            // Close the mobile menu
            this.menuOpen = false;

            // Create a standalone notification dropdown
            const NotificationDropdownComponent = Vue.extend(NotificationDropdown);
            const instance = new NotificationDropdownComponent({
                propsData: {},
                store: this.$store,
                i18n: this.$i18n,
                router: this.$router
            });

            // Mount the component
            instance.$mount();
            document.body.appendChild(instance.$el);

            // Open the dropdown
            instance.toggleDropdown();

            // Add a backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 z-40';
            backdrop.id = 'notification-backdrop';
            backdrop.addEventListener('click', () => {
                instance.closeDropdown();
                document.body.removeChild(backdrop);
                setTimeout(() => {
                    document.body.removeChild(instance.$el);
                    instance.$destroy();
                }, 300);
            });
            document.body.appendChild(backdrop);

            // Listen for close event
            instance.$on('closed', () => {
                if (document.getElementById('notification-backdrop')) {
                    document.body.removeChild(backdrop);
                }
                setTimeout(() => {
                    document.body.removeChild(instance.$el);
                    instance.$destroy();
                }, 300);
            });
        }
    }
}
</script>
