<template>
    <div class="relative z-10 flex-shrink-0 flex h-16 shadow-sm" :class="bgPrimary">
        <button aria-label="Open sidebar" class="px-4 text-primary-600 focus:outline-none focus:bg-primary-50 focus:text-primary-700 md:hidden" @click="$emit('toggleSidebar')">
            <svg-vue class="h-6 w-6" icon="font-awesome.bars-regular"></svg-vue>
        </button>
        <div class="w-full px-4 flex justify-end">
            <div class="flex">
                <div class="ml-4 flex-1 flex items-center md:ml-6">
                    <!-- Notification Dropdown Component -->
                    <notification-dropdown ref="notificationDropdown" v-on-clickaway="closeNotificationDropdown" class="dashboard-notification-icon" />

                    <!-- Dark Mode Toggle -->
                    <div class="flex items-center ml-2 mr-1">
                        <span class="text-xs mr-2 hidden sm:inline" :class="textTertiary">{{ $store.state.darkMode ? $t('Light') : $t('Dark') }}</span>
                        <dark-mode-toggle />
                    </div>

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
                            <div v-show="dropdownOpen" class="origin-top-right z-50 absolute right-0 mt-2 w-56 rounded-md shadow-lg">
                                <div aria-labelledby="user-menu" aria-orientation="vertical" class="py-1 rounded-md shadow-xs" :class="bgPrimary" role="menu">
                                    <div class="flex items-center px-4 ce py-2 border-b" :class="borderPrimary">
                                        <img
                                            :src="$store.state.user.avatar === 'gravatar' ? $store.state.user.gravatar : $store.state.user.avatar"
                                            alt="User avatar"
                                            class="h-8 w-8 mr-3 align-middle rounded-full"
                                        />
                                        <div class="w-40">
                                            <div class="text-sm font-medium truncate" :class="textPrimary">{{ $store.state.user.name }}</div>
                                            <div class="text-xs truncate" :class="textTertiary">{{ $store.state.user.email }}</div>
                                        </div>
                                    </div>
                                    <router-link
                                        v-if="$store.state.user ? $store.state.user.role.dashboard_access : false"
                                        class="block px-4 py-2 text-sm transition ease-in-out duration-150" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                                        role="menuitem"
                                        to="/dashboard/home"
                                        @click.native="dropdownOpen = false"
                                    >
                                        {{ $t('Dashboard') }}
                                    </router-link>
                                    <router-link
                                        class="block px-4 py-2 text-sm transition ease-in-out duration-150" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                                        role="menuitem"
                                        to="/tickets/list"
                                        @click.native="dropdownOpen = false"
                                    >
                                        {{ $t('My tickets') }}
                                    </router-link>
                                    <router-link
                                        class="block px-4 py-2 text-sm transition ease-in-out duration-150" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                                        role="menuitem"
                                        to="/account"
                                        @click.native="dropdownOpen = false"
                                    >
                                        {{ $t('Account settings') }}
                                    </router-link>
                                    <a
                                        class="block px-4 py-2 text-sm transition ease-in-out duration-150" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
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
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import {mixin as clickaway} from "../../../utilities/vue-clickaway-compat";
import NotificationDropdown from "@/components/notifications/NotificationDropdown";
import DarkModeToggle from "@/components/elements/dark-mode-toggle";
import DarkModeMixin from "@/mixins/dark-mode-mixin";

export default {
    name: "navbar",
    components: {NotificationDropdown, DarkModeToggle},
    mixins: [clickaway, DarkModeMixin],
    data() {
        return {
            dropdownOpen: false
        }
    },
    computed: {
        isAdminDashboard() {
            // Check if the current route is in the admin dashboard
            return this.$route.path.includes('/dashboard/admin');
        }
    },

    methods: {
        signOut() {
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

    }
}
</script>

<style scoped>
/* Dashboard notification icon styling */
.dashboard-notification-icon button {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}

/* Add touch feedback */
.dashboard-notification-icon button:active {
  background-color: rgba(0, 0, 0, 0.05);
}

/* Dark mode support */
.dark .dashboard-notification-icon button:active {
  background-color: rgba(255, 255, 255, 0.1);
}

@media (max-width: 640px) {
  .dashboard-notification-icon {
    margin-right: 0.5rem;
  }
}
</style>
