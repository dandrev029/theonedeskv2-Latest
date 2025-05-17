<template>
  <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
    <!-- Header content with minimal design similar to the image -->
    <div class="flex items-center p-3">
      <!-- Back button (purple arrow) -->
      <router-link
        v-if="returnUrl"
        :to="returnUrl"
        class="flex items-center justify-center mr-3 text-purple-600 hover:text-purple-700 transition-colors"
      >
        <svg-vue icon="font-awesome/chevron-left-solid" class="h-5 w-5"></svg-vue>
      </router-link>

      <!-- Avatar with colored border -->
      <div class="relative mr-3">
        <div class="avatar-circle">
          <img :src="contactAvatar" alt="Avatar" class="h-10 w-10 rounded-full object-cover" />
        </div>
      </div>

      <!-- Title and activity info -->
      <div class="flex-grow">
        <div class="flex flex-col">
          <!-- Title with department name -->
          <div class="flex items-center">
            <h3 class="font-medium text-gray-800 dark:text-gray-200">
              {{ department || contactName }}
            </h3>

            <!-- Status badge (small dot) -->
            <div v-if="ticketStatus"
                 class="ml-2 h-2 w-2 rounded-full"
                 :style="{ backgroundColor: ticketStatus.color }">
            </div>
          </div>

          <!-- Activity timestamp -->
          <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ isOnline ? $t('Active') : '' }} {{ lastActiveTime }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';

export default {
  name: 'ChatHeader',
  props: {
    contactName: {
      type: String,
      required: true,
    },
    contactAvatar: {
      type: String,
      required: true,
    },
    ticketStatus: {
      type: Object, // Expecting { name: 'Status Name', color: '#hexcolor' }
      default: null,
    },
    isOnline: {
      type: Boolean,
      default: false,
    },
    department: {
      type: String,
      default: null,
    },
    returnUrl: {
      type: String,
      default: null,
    },
    lastActive: {
      type: String,
      default: null,
    },
  },
  computed: {
    lastActiveTime() {
      // If we have a lastActive prop, use it
      if (this.lastActive) {
        return moment(this.lastActive).fromNow();
      }

      // Otherwise show a default message based on online status
      return this.isOnline ? this.$t('now') : '';
    }
  },
  filters: {
    formatDate(value) {
      if (!value) return '';
      return moment(value).format('MMM D, h:mm A');
    },
  },
  methods: {
    getStatusColor(status) {
      if (!status) return '#777777';
      return status.color;
    },
  },
};
</script>

<style scoped>
/* Minimal, clean styles for the chat header */

/* Avatar styling with colored border */
.avatar-circle {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #6366f1; /* Indigo color for the circle */
  padding: 2px;
}

.avatar-circle img {
  border: 2px solid white;
}

/* Back button hover effect */
router-link {
  transition: all 0.2s ease;
}

router-link:hover {
  transform: translateX(-2px);
}

/* Status badge styling */
.rounded-full {
  transition: all 0.2s ease;
}

/* Clean typography */
h3 {
  font-size: 0.95rem;
  line-height: 1.2;
}

.text-xs {
  font-size: 0.75rem;
  line-height: 1;
  opacity: 0.8;
}
</style>
