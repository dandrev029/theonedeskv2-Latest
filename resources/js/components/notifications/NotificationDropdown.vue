<template>
  <div class="relative notification-dropdown">
    <button
      :class="getDarkModeClasses({
        lightHover: 'hover:bg-gray-100 hover:text-gray-500',
        darkHover: 'hover:bg-gray-700 hover:text-gray-300'
      })"
      class="p-1 text-gray-400 rounded-full focus:outline-none focus:shadow-outline relative"
      aria-label="Notifications"
      @click="toggleDropdown"
    >
      <svg-vue class="h-6 w-6 p-px" icon="font-awesome.bell-regular"></svg-vue>
      <span v-if="unreadCount > 0" class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <transition
      enter-active-class="transition ease-out duration-200"
      enter-class="transform opacity-0 scale-95 sm:scale-95 sm:translate-y-0 translate-y-full"
      enter-to-class="transform opacity-100 scale-100 sm:scale-100 sm:translate-y-0 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-class="transform opacity-100 scale-100 sm:scale-100 sm:translate-y-0 translate-y-0"
      leave-to-class="transform opacity-0 scale-95 sm:scale-95 sm:translate-y-0 translate-y-full"
    >
      <div
        v-show="isOpen"
        class="origin-top-right absolute right-0 mt-2 w-80 md:w-96 rounded-md shadow-lg z-50 mobile-notification-dropdown" :class="bgPrimary"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
      >
        <div class="py-1 rounded-md shadow-xs" :class="bgPrimary">
          <!-- Notification Header -->
          <div class="px-4 py-3 border-b flex justify-between items-center" :class="borderPrimary">
            <h3 class="text-sm font-semibold" :class="textPrimary">{{ $t('Notifications') }}</h3>
            <button
              v-if="notifications.length > 0 && unreadCount > 0"
              @click="markAllAsRead"
              class="text-xs text-blue-600 hover:text-blue-800 focus:outline-none"
            >
              {{ $t('Mark all as read') }}
            </button>
          </div>

          <!-- Notifications List -->
          <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm" :class="textTertiary">
            {{ $t('No notifications') }}
          </div>
          <div v-else class="max-h-80 overflow-y-auto">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              :class="[
                'px-4 py-3 flex items-start cursor-pointer',
                $store.state.darkMode ?
                  (notification.is_read ? 'bg-gray-800 hover:bg-gray-700' : 'bg-blue-900 hover:bg-blue-800') :
                  (notification.is_read ? 'bg-white hover:bg-gray-50' : 'bg-blue-50 hover:bg-blue-100')
              ]"
              @click="openNotification(notification)"
            >
              <div class="flex-shrink-0 mr-3">
                <div class="h-8 w-8 rounded-full bg-blue-500 text-white flex items-center justify-center">
                  <svg-vue v-if="notification.icon" class="h-4 w-4" :icon="mapIconName(notification.icon)"></svg-vue>
                  <svg-vue v-else class="h-4 w-4" icon="font-awesome.bell-regular"></svg-vue>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate" :class="textPrimary">{{ notification.title }}</p>
                <p class="text-sm" :class="textSecondary">{{ notification.message }}</p>
                <p class="text-xs mt-1" :class="textTertiary">{{ formatTime(notification.created_at) }}</p>
              </div>
              <div class="ml-2">
                <button
                  @click.stop="removeNotification(notification.id)"
                  :class="getDarkModeClasses({lightText: 'text-gray-400', darkText: 'text-gray-500', lightHover: 'hover:text-gray-600', darkHover: 'hover:text-gray-300'})"
                  aria-label="Delete notification"
                >
                  <svg-vue class="h-4 w-4" icon="font-awesome.times-solid"></svg-vue>
                </button>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div v-if="notifications.length > 0" class="px-4 py-2 border-t text-center" :class="borderPrimary">
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
      unreadCount: 0,
      touchStartY: 0,
      touchMoveY: 0,
      isSwiping: false,
      swipeThreshold: 50 // Minimum distance to consider a swipe
    };
  },
  created() {
    this.fetchNotifications();
    // Add a small delay to ensure user data is loaded
    setTimeout(() => {
      this.setupPusherListener();
    }, 1000);
  },
  watch: {
    unreadCount(newCount) {
      // Emit the unread count to the root component
      this.$root.$emit('notification-count-updated', newCount);
    }
  },
  methods: {
    mapIconName(iconName) {
      // Map missing solid icons to available regular ones
      const iconMap = {
        'font-awesome.user-tag-solid': 'font-awesome.user-tag-regular',
        'font-awesome.comment-alt-solid': 'font-awesome.comments-alt-regular', // Corrected based on list_dir output
        'font-awesome.bell-solid': 'font-awesome.bell-regular' // Add mapping for the default replacement too
      };
      return iconMap[iconName] || iconName;
    },
    async fetchNotifications() {
      try {
        const response = await this.$store.dispatch("fetchNotifications");

        // Process notifications from both sources
        let allNotifications = [];
        let seenNotifications = new Map(); // Track notifications by content to prevent duplicates

        // Process app_notifications
        if (response.notifications && response.notifications.data) {
          // Add each app notification to the map with a content-based key
          response.notifications.data.forEach(notification => {
            const contentKey = `${notification.title}|${notification.message}`;
            // Only add if we haven't seen this content before
            if (!seenNotifications.has(contentKey)) {
              seenNotifications.set(contentKey, notification);
              allNotifications.push(notification);
            }
          });
        }

        // Process Laravel notifications
        if (response.laravel_notifications && response.laravel_notifications.data) {
          // Convert Laravel notifications to the same format as app_notifications
          response.laravel_notifications.data.forEach(notification => {
            // Extract data from the notification
            const data = notification.data || {};
            let iconName = notification.icon || data.icon || 'font-awesome.bell-regular'; // Default to regular bell
            // Map missing solid icons to available regular ones
            if (iconName === 'font-awesome.user-tag-solid') {
              iconName = 'font-awesome.user-tag-regular';
            }
            if (iconName === 'font-awesome.comment-alt-solid') {
              iconName = 'font-awesome.comments-alt-regular'; // Corrected based on list_dir output
            }
            // Ensure the default bell is also regular if it was solid
            if (iconName === 'font-awesome.bell-solid') {
                 iconName = 'font-awesome.bell-regular';
            }
            const formattedNotification = {
              id: notification.id,
              title: notification.title || data.title || 'Notification',
              message: notification.message || data.message || '',
              type: notification.type || data.type || 'general',
              icon: iconName, // Use the potentially replaced icon name
              link: notification.link || data.link || null,
              created_at: notification.created_at,
              is_read: notification.is_read || notification.read_at !== null,
              _source: 'laravel' // Mark the source for internal tracking
            };

            // Check if we've already seen this content
            const contentKey = `${formattedNotification.title}|${formattedNotification.message}`;
            if (!seenNotifications.has(contentKey)) {
              seenNotifications.set(contentKey, formattedNotification);
              allNotifications.push(formattedNotification);
            }
          });
        }

        // Sort notifications by created_at (newest first)
        allNotifications.sort((a, b) => {
          const dateA = new Date(a.created_at);
          const dateB = new Date(b.created_at);
          return dateB - dateA;
        });

        this.notifications = allNotifications;
        this.unreadCount = response.unread_count;
      } catch (error) {
        console.error("Error fetching notifications:", error);
      }
    },
    setupPusherListener() {
      if (this.$store.state.user) {
        console.log('Setting up Pusher listener for user ID:', this.$store.state.user.id);
        try {
          // First, remove any existing listeners to prevent duplicates
          if (window.Echo) {
            try {
              window.Echo.leave(`notifications.${this.$store.state.user.id}`);
              console.log('Removed existing Pusher listener');
            } catch (leaveError) {
              console.warn('Error removing existing listener:', leaveError);
            }
          }

          // Set up the new listener
          window.Echo.private(`notifications.${this.$store.state.user.id}`)
            .listen(".notification.new", (e) => {
              console.log('Received notification:', e);
              this.addNotification(e);
            })
            .error((err) => {
              console.error('Pusher channel error:', err);
            });

          console.log('Pusher listener setup complete');

          // Test the connection
          window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('Pusher connected successfully');
          });

          window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('Pusher connection error:', err);
          });
        } catch (error) {
          console.error('Error setting up Pusher listener:', error);
        }
      } else {
        console.warn('No user found in store, cannot setup Pusher listener');
      }
    },
    addNotification(notification) {
      console.log('Adding notification to UI:', notification);

      // Validate notification data
      if (!notification || !notification.id || !notification.title) {
        console.error('Invalid notification data:', notification);
        return;
      }

      // Generate a unique ID for the notification if it doesn't have one
      // This helps prevent duplicates when receiving notifications from different sources
      const notificationId = notification.id + '-' + (notification.timestamp || Date.now());

      // Check if notification already exists to prevent duplicates
      const contentKey = `${notification.title}|${notification.message}`;
      const timeThreshold = Date.now() - (5 * 60 * 1000); // 5 minutes ago

      // Check for duplicates by content and time
      const exists = this.notifications.some(n => {
        // Check by ID
        if (n.id === notification.id) return true;

        // Check by content if received within the last 5 minutes
        if (n.title === notification.title && n.message === notification.message) {
          const notificationTime = new Date(n.created_at).getTime();
          if (notificationTime > timeThreshold) {
            return true;
          }
        }
        return false;
      });

      if (exists) {
        console.warn('Notification already exists, not adding duplicate:', notification.id);
        return;
      }

      // Apply icon replacement logic for Pusher notifications
      let iconName = notification.icon || notification.data?.icon || 'font-awesome.bell-solid';
      if (iconName === 'font-awesome.user-tag-solid') {
        iconName = 'font-awesome.user-tag-regular'; // Map to available regular icon
      }
      if (iconName === 'font-awesome.comment-alt-solid') {
        iconName = 'font-awesome.comments-alt-regular'; // Map to available regular icon (corrected name)
      }

      // Add to local state
      this.notifications.unshift({
        ...notification,
        icon: iconName, // Use the potentially replaced icon name
        ...notification,
        _uniqueId: notificationId // Add a unique ID for internal tracking
      });
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

        // Play notification sound if available
        try {
          const audio = new Audio('/notification.mp3');
          audio.play();
        } catch (soundError) {
          console.warn('Could not play notification sound:', soundError);
        }
      } catch (error) {
        console.error('Error displaying toast notification:', error);
      }

      // Update the store
      try {
        this.$store.commit('addNotification', notification);
      } catch (storeError) {
        console.error('Error updating store with notification:', storeError);
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

      // Add a backdrop for mobile view when opening
      if (this.isOpen && window.innerWidth < 640) {
        // Check if backdrop already exists
        if (!document.getElementById('notification-backdrop-inline')) {
          const backdrop = document.createElement('div');
          backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 z-150';
          backdrop.id = 'notification-backdrop-inline';
          backdrop.addEventListener('click', () => {
            this.closeDropdown();
          });
          document.body.appendChild(backdrop);

          // Prevent body scrolling
          document.body.classList.add('notification-dropdown-open');

          // Add touch event listener to the backdrop for better mobile experience
          backdrop.addEventListener('touchmove', (e) => {
            e.preventDefault(); // Prevent scrolling on the backdrop
          }, { passive: false });
        }
      } else {
        // Remove backdrop when closing
        const backdrop = document.getElementById('notification-backdrop-inline');
        if (backdrop) {
          document.body.removeChild(backdrop);

          // Re-enable body scrolling
          document.body.classList.remove('notification-dropdown-open');
        }
      }
    },
    closeDropdown() {
      this.isOpen = false;

      // Remove backdrop when closing
      const backdrop = document.getElementById('notification-backdrop-inline');
      if (backdrop) {
        document.body.removeChild(backdrop);

        // Re-enable body scrolling
        document.body.classList.remove('notification-dropdown-open');
      }

      this.$emit('closed');
    },
    formatTime(timestamp) {
      return moment(timestamp).fromNow();
    },
    // Touch event handlers for mobile swipe
    handleTouchStart(event) {
      if (window.innerWidth >= 640) return; // Only handle touch events on mobile
      this.touchStartY = event.touches[0].clientY;
      this.isSwiping = false;
    },
    handleTouchMove(event) {
      if (window.innerWidth >= 640) return; // Only handle touch events on mobile
      this.touchMoveY = event.touches[0].clientY;

      // Calculate the distance moved
      const deltaY = this.touchMoveY - this.touchStartY;

      // If swiping down, prevent default to avoid page scrolling
      if (deltaY > 0) {
        this.isSwiping = true;
        event.preventDefault();

        // Apply a transform to follow the finger
        const dropdown = event.currentTarget;
        const translateY = Math.min(deltaY * 0.5, 200); // Limit the movement
        dropdown.style.transform = `translateY(${translateY}px)`;
      }
    },
    handleTouchEnd(event) {
      if (window.innerWidth >= 640) return; // Only handle touch events on mobile

      const dropdown = event.currentTarget;
      dropdown.style.transform = ''; // Reset the transform

      // Calculate the distance moved
      const deltaY = this.touchMoveY - this.touchStartY;

      // If swiped down far enough, close the dropdown
      if (deltaY > this.swipeThreshold && this.isSwiping) {
        this.closeDropdown();
      }

      this.isSwiping = false;
    }
  }
};
</script>

<style>
/* Mobile notification dropdown positioning */
@media (max-width: 640px) {
  .mobile-notification-dropdown {
    position: fixed;
    top: auto;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100% !important;
    max-height: 85vh;
    margin-top: 0;
    border-radius: 16px 16px 0 0;
    z-index: 200; /* Increased z-index to ensure it appears above other elements */
    transform: translateZ(0); /* Force hardware acceleration */
    will-change: transform; /* Optimize for animations */
    transition: transform 0.3s ease-out;
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
  }

  .mobile-notification-dropdown .max-h-80 {
    max-height: 65vh;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    overscroll-behavior: contain; /* Prevent scroll chaining */
  }

  /* Fix for mobile menu overlap */
  .mobile-notification-dropdown .py-1 {
    padding-bottom: calc(env(safe-area-inset-bottom, 16px) + 8px);
  }

  /* Add a handle for better UX */
  .mobile-notification-dropdown::before {
    content: '';
    display: block;
    width: 40px;
    height: 5px;
    background-color: #e2e8f0;
    border-radius: 2.5px;
    margin: 8px auto;
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    transition: background-color 0.2s ease;
  }

  /* Dark mode support for the handle */
  .dark .mobile-notification-dropdown::before {
    background-color: #4a5568;
  }

  /* Add touch feedback to notification items */
  .mobile-notification-dropdown .px-4.py-3.flex.items-start.cursor-pointer {
    transition: background-color 0.15s ease;
  }

  /* Add active state for touch feedback */
  .mobile-notification-dropdown .px-4.py-3.flex.items-start.cursor-pointer:active {
    opacity: 0.8;
  }
}

/* Fix for notification dropdown in mobile menu */
.sm\:hidden .notification-dropdown {
  position: static;
}

.sm\:hidden .notification-dropdown button {
  padding: 0;
  height: 40px;
  width: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Ensure the notification bell is properly sized and positioned in mobile view */
.sm\:hidden .notification-dropdown .h-6.w-6 {
  height: 1.5rem;
  width: 1.5rem;
}

/* Fix for notification badge in mobile view */
.sm\:hidden .notification-dropdown .absolute.top-0.right-0 {
  top: -2px;
  right: -2px;
}

/* Prevent body scrolling when dropdown is open */
body.notification-dropdown-open {
  overflow: hidden;
  position: fixed;
  width: 100%;
}
</style>