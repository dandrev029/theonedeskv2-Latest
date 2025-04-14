<template>
  <div class="relative">
    <button
      class="p-1 text-gray-400 rounded-full hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:shadow-outline focus:text-gray-500 relative"
      aria-label="Notifications"
      @click="toggleDropdown"
    >
      <svg-vue class="h-6 w-6 p-px" icon="font-awesome.bell-regular"></svg-vue>
      <span v-if="unreadCount > 0" class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="isOpen"
        class="origin-top-right absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-md shadow-lg z-50"
      >
        <div class="py-1 rounded-md bg-white shadow-xs">
          <!-- Notification Header -->
          <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">{{ $t('Notifications') }}</h3>
            <button
              v-if="notifications.length > 0 && unreadCount > 0"
              @click="markAllAsRead"
              class="text-xs text-blue-600 hover:text-blue-800 focus:outline-none"
            >
              {{ $t('Mark all as read') }}
            </button>
          </div>

          <!-- Notifications List -->
          <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
            {{ $t('No notifications') }}
          </div>
          <div v-else class="max-h-80 overflow-y-auto">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              :class="[
                'px-4 py-3 hover:bg-gray-50 flex items-start cursor-pointer',
                notification.is_read ? 'bg-white' : 'bg-blue-50'
              ]"
              @click="openNotification(notification)"
            >
              <div class="flex-shrink-0 mr-3">
                <div class="h-8 w-8 rounded-full bg-blue-500 text-white flex items-center justify-center">
                  <svg-vue v-if="notification.icon" class="h-4 w-4" :icon="notification.icon"></svg-vue>
                  <svg-vue v-else class="h-4 w-4" icon="font-awesome.bell-regular"></svg-vue>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ notification.title }}</p>
                <p class="text-sm text-gray-600 truncate">{{ notification.message }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatTime(notification.created_at) }}</p>
              </div>
              <div class="ml-2">
                <button
                  @click.stop="removeNotification(notification.id)"
                  class="text-gray-400 hover:text-gray-600"
                  aria-label="Delete notification"
                >
                  <svg-vue class="h-4 w-4" icon="font-awesome.times-solid"></svg-vue>
                </button>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div v-if="notifications.length > 0" class="px-4 py-2 border-t border-gray-200 text-center">
            <button
              @click="viewAllNotifications"
              class="text-xs text-blue-600 hover:text-blue-800 focus:outline-none"
            >
              {{ $t('View all notifications') }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { mixin as clickaway } from "../../utilities/vue-clickaway-compat";

export default {
  name: "NotificationDropdown",
  mixins: [clickaway],
  props: {
    initialNotifications: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isOpen: false,
      notifications: this.initialNotifications || [],
      unreadCount: 0
    };
  },
  created() {
    this.fetchNotifications();
    // Add a small delay to ensure user data is loaded
    setTimeout(() => {
      this.setupPusherListener();
    }, 1000);
  },
  methods: {
    async fetchNotifications() {
      try {
        const response = await this.$store.dispatch("fetchNotifications");
        this.notifications = response.notifications.data;
        this.unreadCount = response.unread_count;
      } catch (error) {
        console.error("Error fetching notifications:", error);
      }
    },
    setupPusherListener() {
      if (this.$store.state.user) {
        console.log('Setting up Pusher listener for user ID:', this.$store.state.user.id);
        try {
          window.Echo.private(`notifications.${this.$store.state.user.id}`)
            .listen(".notification.new", (e) => {
              console.log('Received notification:', e);
              this.addNotification(e);
            });
          console.log('Pusher listener setup complete');
        } catch (error) {
          console.error('Error setting up Pusher listener:', error);
        }
      } else {
        console.warn('No user found in store, cannot setup Pusher listener');
      }
    },
    addNotification(notification) {
      console.log('Adding notification to UI:', notification);
      // Add to local state
      this.notifications.unshift(notification);
      this.unreadCount++;

      // Show toast notification
      try {
        this.$notify({
          group: "app",
          title: notification.title,
          text: notification.message,
          duration: 5000,
          type: "info"
        });
        console.log('Toast notification displayed');
      } catch (error) {
        console.error('Error displaying toast notification:', error);
      }
    },
    async openNotification(notification) {
      if (!notification.is_read) {
        await this.$store.dispatch("markNotificationAsRead", notification.id);
        notification.is_read = true;
        this.unreadCount--;
      }

      if (notification.link) {
        this.$router.push(notification.link);
      }
      this.isOpen = false;
    },
    async markAllAsRead() {
      await this.$store.dispatch("markAllNotificationsAsRead");
      this.notifications.forEach(notification => {
        notification.is_read = true;
      });
      this.unreadCount = 0;
    },
    async removeNotification(id) {
      await this.$store.dispatch("deleteNotification", id);
      this.notifications = this.notifications.filter(n => n.id !== id);
    },
    viewAllNotifications() {
      // If you have a dedicated notifications page, navigate to it
      // this.$router.push("/notifications");
      this.isOpen = false;
    },
    toggleDropdown() {
      this.isOpen = !this.isOpen;
    },
    closeDropdown() {
      this.isOpen = false;
    },
    formatTime(timestamp) {
      return moment(timestamp).fromNow();
    }
  }
};
</script>