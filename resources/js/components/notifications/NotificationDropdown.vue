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
      <svg-vue class="h-6 w-6 p-px" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.bell-regular"></svg-vue>
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
                  <svg-vue v-if="notification.icon" class="h-4 w-4 text-white" :icon="mapIconName(notification.icon)"></svg-vue>
                  <svg-vue v-else class="h-4 w-4 text-white" icon="font-awesome.bell-light"></svg-vue>
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
                  <svg-vue class="h-4 w-4" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-500'" icon="font-awesome.times-solid"></svg-vue>
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

    // Set up a more reliable way to initialize Pusher
    this.initializePusher();
  },

  mounted() {
    // Add event listener for when user data is updated
    this.$root.$on('user-loaded', this.setupPusherListener);
  },

  beforeDestroy() {
    // Clean up event listeners
    this.$root.$off('user-loaded', this.setupPusherListener);

    // Clean up Pusher listener
    this.cleanupPusherListener();
  },
  watch: {
    unreadCount(newCount) {
      // Emit the unread count to the root component
      this.$root.$emit('notification-count-updated', newCount);
    }
  },
  methods: {
    mapIconName(iconName) {
      // Map icons to light versions for better compatibility with both light and dark modes
      const iconMap = {
        // Map solid icons to light versions
        'font-awesome.user-tag-solid': 'font-awesome.user-tag-light',
        'font-awesome.comment-alt-solid': 'font-awesome.comment-alt-light',
        'font-awesome.bell-solid': 'font-awesome.bell-light',
        'font-awesome.ticket-alt-solid': 'font-awesome.ticket-alt-light',
        'font-awesome.envelope-solid': 'font-awesome.envelope-light',
        'font-awesome.check-circle-solid': 'font-awesome.check-circle-light',
        'font-awesome.exclamation-circle-solid': 'font-awesome.exclamation-circle-light',
        'font-awesome.info-circle-solid': 'font-awesome.info-circle-light',

        // Also map regular icons to light versions
        'font-awesome.user-tag-regular': 'font-awesome.user-tag-light',
        'font-awesome.comments-alt-regular': 'font-awesome.comment-alt-light',
        'font-awesome.bell-regular': 'font-awesome.bell-light',
        'font-awesome.ticket-alt-regular': 'font-awesome.ticket-alt-light',
        'font-awesome.envelope-regular': 'font-awesome.envelope-light',
        'font-awesome.check-circle-regular': 'font-awesome.check-circle-light',
        'font-awesome.exclamation-circle-regular': 'font-awesome.exclamation-circle-light',
        'font-awesome.info-circle-regular': 'font-awesome.info-circle-light'
      };

      return iconMap[iconName] || iconName;
    },
    async fetchNotifications() {
      try {
        // Check if user is authenticated
        if (!this.$store.state.user) {
          console.log('User not authenticated, skipping notification fetch');
          this.notifications = [];
          this.unreadCount = 0;
          return;
        }

        const response = await this.$store.dispatch("fetchNotifications");

        // If response is undefined or null, return early
        if (!response) {
          console.warn('No response from fetchNotifications');
          return;
        }

        // Process notifications from both sources
        let allNotifications = [];
        let seenNotifications = new Map(); // Track notifications by content to prevent duplicates
        let seenTicketNotifications = new Map(); // Special tracking for ticket notifications
        const maxNotificationsToDisplay = 20; // Limit the number of notifications to display

        // Process app_notifications
        if (response.notifications && response.notifications.data) {
          // Add each app notification to the map with a content-based key
          response.notifications.data.forEach(notification => {
            // Skip notifications without title or message
            if (!notification.title || !notification.message) {
              console.warn('Skipping notification with missing title or message:', notification);
              return;
            }

            // Skip notifications with generic title "Notification"
            if (notification.title === 'Notification') {
              console.log('Skipping generic notification with title "Notification"');
              return;
            }

            // Create a unique key for exact duplicate detection
            const contentKey = `${notification.title}|${notification.message}`;

            // Check for exact duplicates first
            if (seenNotifications.has(contentKey)) {
              console.log('Skipping exact duplicate notification:', notification.title);
              return;
            }

            // For ticket notifications, do additional similarity checking
            if (notification.title.toLowerCase().includes('ticket') ||
                notification.message.toLowerCase().includes('ticket')) {

              // Create a simplified key for ticket notifications (first part of the message)
              const baseMessage = notification.message.split(':')[0];
              const ticketKey = `${notification.title}|${baseMessage}`;

              if (seenTicketNotifications.has(ticketKey)) {
                console.log('Skipping similar ticket notification:', notification.title);
                return;
              }

              // Add to both maps
              seenTicketNotifications.set(ticketKey, notification);
            }

            // Add to regular tracking and notification list
            seenNotifications.set(contentKey, notification);
            allNotifications.push(notification);
          });
        }

        // Process Laravel notifications
        if (response.laravel_notifications && response.laravel_notifications.data) {
          // Convert Laravel notifications to the same format as app_notifications
          response.laravel_notifications.data.forEach(notification => {
            // Extract data from the notification
            const data = notification.data || {};

            // Skip notifications without title or message
            const title = notification.title || data.title || null;
            const message = notification.message || data.message || null;

            if (!title || !message) {
              console.warn('Skipping Laravel notification with missing title or message:', notification);
              return;
            }

            // Skip notifications with generic title "Notification"
            if (title === 'Notification') {
              console.log('Skipping generic Laravel notification with title "Notification"');
              return;
            }

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
            if (iconName === 'font-awesome.ticket-alt-solid') { // Add mapping for ticket icon
                iconName = 'font-awesome.ticket-alt-regular';
            }
            const formattedNotification = {
              id: notification.id,
              title: title,
              message: message,
              type: notification.type || data.type || 'general',
              icon: iconName, // Use the potentially replaced icon name
              link: notification.link || data.link || null,
              created_at: notification.created_at,
              is_read: notification.is_read || notification.read_at !== null,
              _source: 'laravel' // Mark the source for internal tracking
            };

            // Create a unique key for exact duplicate detection
            const contentKey = `${formattedNotification.title}|${formattedNotification.message}`;

            // Check for exact duplicates first
            if (seenNotifications.has(contentKey)) {
              console.log('Skipping exact duplicate Laravel notification:', formattedNotification.title);
              return;
            }

            // For ticket notifications, do additional similarity checking
            if (formattedNotification.title.toLowerCase().includes('ticket') ||
                formattedNotification.message.toLowerCase().includes('ticket')) {

              // Create a simplified key for ticket notifications (first part of the message)
              const baseMessage = formattedNotification.message.split(':')[0];
              const ticketKey = `${formattedNotification.title}|${baseMessage}`;

              if (seenTicketNotifications.has(ticketKey)) {
                console.log('Skipping similar ticket Laravel notification:', formattedNotification.title);
                return;
              }

              // Add to both maps
              seenTicketNotifications.set(ticketKey, formattedNotification);
            }

            // Add to regular tracking and notification list
            seenNotifications.set(contentKey, formattedNotification);
            allNotifications.push(formattedNotification);
          });
        }

        // Sort notifications by created_at (newest first)
        allNotifications.sort((a, b) => {
          const dateA = new Date(a.created_at);
          const dateB = new Date(b.created_at);
          return dateB - dateA;
        });

        // Limit the number of notifications to display
        this.notifications = allNotifications.slice(0, maxNotificationsToDisplay);

        // Log how many notifications were filtered out
        if (allNotifications.length > maxNotificationsToDisplay) {
          console.log(`Limiting notifications display to ${maxNotificationsToDisplay} out of ${allNotifications.length} total notifications`);
        }

        // Calculate the actual unread count from the notifications array
        const actualUnreadCount = this.notifications.filter(notification => !notification.is_read).length;

        // Use the calculated count if it's different from the response count
        // This ensures the UI accurately reflects the actual unread notifications
        if (actualUnreadCount !== response.unread_count) {
          console.log(`Unread count mismatch: API reports ${response.unread_count}, actual count is ${actualUnreadCount}`);
          this.unreadCount = actualUnreadCount;
        } else {
          this.unreadCount = response.unread_count;
        }
      } catch (error) {
        console.error("Error fetching notifications:", error);
      }
    },
    setupPusherListener() {
      // Skip if user is not authenticated
      if (!this.$store.state.user) {
        console.warn('No user found in store, cannot setup Pusher listener');
        return;
      }

      // Skip if user ID is not available
      if (!this.$store.state.user.id) {
        console.warn('User ID not available, cannot setup Pusher listener');
        return;
      }

      console.log('Setting up Pusher listener for user ID:', this.$store.state.user.id);

      try {
        // Check if Echo is available
        if (!window.Echo) {
          console.warn('Echo not available, cannot setup Pusher listener');
          return;
        }

        // Check if token exists
        const token = localStorage.getItem('token');
        if (!token) {
          console.warn('No authentication token found, cannot setup Pusher listener');
          return;
        }

        // First, remove any existing listeners to prevent duplicates
        try {
          window.Echo.leave(`notifications.${this.$store.state.user.id}`);
          console.log('Removed existing Pusher listener');
        } catch (leaveError) {
          console.warn('Error removing existing listener:', leaveError);
        }

        // Ensure Echo has the latest auth token
        if (window.Echo.connector && window.Echo.connector.options && window.Echo.connector.options.auth) {
          window.Echo.connector.options.auth.headers.Authorization = `Bearer ${token}`;
          console.log('Updated Pusher auth token');
        }

        // Set up the new listener
        window.Echo.private(`notifications.${this.$store.state.user.id}`)
          .listen(".notification.new", (e) => {
            console.log('[PUSHER] Received raw notification event:', JSON.stringify(e));
            // Call addNotification directly, Vue handles 'this' context
            this.addNotification(e);
          })
          .error((err) => {
            console.error('[PUSHER] Channel subscription error:', err);
            // Log specific error details if available
            if (err.error && err.error.data) {
              console.error('[PUSHER] Error details:', JSON.stringify(err.error.data));
            }
          });

        console.log('Pusher listener setup complete');

        // Test the connection
        if (window.Echo.connector && window.Echo.connector.pusher) {
          window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('Pusher connected successfully');
          });

          window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('[PUSHER] Connection error:', err);
            // Log specific error details if available
            if (err.error && err.error.data) {
              console.error('[PUSHER] Connection error details:', JSON.stringify(err.error.data));
            }
            // You might want to add logic here to attempt reconnection or notify the user
          });
        }
      } catch (error) {
        console.error('Error setting up Pusher listener:', error);
      }
    },
    addNotification(notification) {
      console.log('Adding notification to UI:', notification);

      // Prepare the notification object for both toast and store
      const newNotification = {
        id: notification.id || Date.now(), // Use provided ID or generate one
        title: notification.title,
        message: notification.message,
        icon: notification.icon || 'font-awesome.bell-regular', // Default icon
        link: notification.link || null,
        created_at: notification.created_at || new Date().toISOString(),
        is_read: false,
        timestamp: notification.timestamp || Date.now(), // Use provided timestamp or current time
      };

      // Skip notifications with generic title "Notification"
      if (newNotification.title === 'Notification') {
        console.log('Skipping generic notification with title "Notification"');
        return;
      }

      // Check for duplicates before adding
      const isDuplicate = this.checkForDuplicate(newNotification);
      if (isDuplicate) {
        console.log('Skipping duplicate notification:', newNotification.title);
        return;
      }

      // Display notification using vue-notification
      try {
        this.$notify({
          group: 'app', // Assuming 'app' is the notification group defined in app.vue
          title: newNotification.title,
          text: newNotification.message,
          type: 'info', // Or map based on notification type if needed
          duration: 5000,
          data: { // Pass data for potential click handling
            link: newNotification.link,
            notification: newNotification
          }
        });
        console.log('Displayed notification via this.$notify.');

        // Play sound
        try {
          const audio = new Audio('/notification-2-269292.mp3');
          audio.play();
        } catch (soundError) {
          console.warn('Could not play notification sound:', soundError);
        }
      } catch (error) {
        console.error('Error displaying notification via this.$notify:', error);
      }

      // Update the store and local notifications array
      try {
        this.$store.commit('addNotification', newNotification); // Commit the prepared object

        // Add to local notifications array at the beginning (newest first)
        this.notifications.unshift(newNotification);

        // Increment unread count
        this.unreadCount++;

        // Limit the number of notifications to display (prevent overflow)
        const maxNotificationsToDisplay = 20;
        if (this.notifications.length > maxNotificationsToDisplay) {
          this.notifications = this.notifications.slice(0, maxNotificationsToDisplay);
        }

        console.log('[addNotification] Notification added to UI and store');
      } catch (storeError) {
        console.error('[addNotification] Error updating notifications:', storeError);
      }
    },

    checkForDuplicate(newNotification) {
      // If no notifications yet, it's not a duplicate
      if (!this.notifications || this.notifications.length === 0) {
        return false;
      }

      // Check for exact ID match (same notification)
      const idMatch = this.notifications.some(n => n.id === newNotification.id && newNotification.id);
      if (idMatch) {
        console.log('Duplicate notification with same ID detected');
        return true;
      }

      // Check for content match (duplicate content)
      const contentKey = `${newNotification.title}|${newNotification.message}`;

      // Check for exact content match within the last 10 seconds
      const recentDuplicate = this.notifications.some(n => {
        const nContentKey = `${n.title}|${n.message}`;
        if (nContentKey === contentKey) {
          // Check if the notification was created within the last 10 seconds
          const existingTime = n.timestamp || new Date(n.created_at).getTime();
          const newTime = newNotification.timestamp || new Date(newNotification.created_at).getTime();
          const timeDiff = Math.abs(newTime - existingTime);
          return timeDiff < 10000; // 10 seconds in milliseconds
        }
        return false;
      });

      return recentDuplicate;
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
    },

    initializePusher() {
      // Check if user is already loaded
      if (this.$store.state.user && this.$store.state.user.id) {
        console.log('User already loaded, setting up Pusher immediately');
        this.setupPusherListener();
        return;
      }

      // If user is not loaded yet, set up a retry mechanism
      console.log('User not loaded yet, will retry Pusher setup');

      // Try again in 1 second
      setTimeout(() => {
        if (this.$store.state.user && this.$store.state.user.id) {
          console.log('User loaded on retry, setting up Pusher');
          this.setupPusherListener();
        } else {
          console.log('User still not loaded, will try again');
          // Try one more time after 2 seconds
          setTimeout(() => {
            if (this.$store.state.user && this.$store.state.user.id) {
              console.log('User loaded on second retry, setting up Pusher');
              this.setupPusherListener();
            } else {
              console.warn('User still not loaded after retries, giving up on Pusher setup');
            }
          }, 2000);
        }
      }, 1000);
    },

    cleanupPusherListener() {
      // Only attempt cleanup if user is loaded and Echo is available
      if (window.Echo && this.$store.state.user && this.$store.state.user.id) {
        try {
          window.Echo.leave(`notifications.${this.$store.state.user.id}`);
          console.log('Cleaned up Pusher listener on component destroy');
        } catch (error) {
          console.warn('Error cleaning up Pusher listener:', error);
        }
      }
    }
  }
};
</script>

<style scoped>
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