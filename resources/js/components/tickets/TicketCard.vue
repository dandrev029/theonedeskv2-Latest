<template>
  <div class="ticket-card relative">
    <!-- New/Updated indicator -->
    <div v-if="isNew" class="ticket-updated-indicator"></div>

    <div class="ticket-card-header">
      <div class="flex justify-between items-start">
        <div class="flex items-center space-x-2">
          <img
            :alt="$t('Avatar')"
            :src="ticket.user.avatar !== 'gravatar' ? ticket.user.avatar : ticket.user.gravatar"
            class="h-8 w-8 rounded-full"
          >
          <div>
            <div class="font-medium" :class="$store.state.darkMode ? 'text-white' : 'text-gray-900'">{{ ticket.user.name }}</div>
            <div class="text-xs" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-500'">{{ ticket.user.email }}</div>
          </div>
        </div>
        <div class="flex space-x-1">
          <div v-if="ticket.status" class="status-badge" :style="{ backgroundColor: ticket.status.color + '20', color: ticket.status.color }">
            <span class="w-2 h-2 rounded-full mr-1.5" :style="{ backgroundColor: ticket.status.color }"></span>
            {{ ticket.status.name }}
          </div>
          <div v-if="ticket.priority" :class="['priority-badge',
            ticket.priority.value === 1 ? 'priority-badge-low' :
            ticket.priority.value === 2 ? 'priority-badge-medium' :
            ticket.priority.value === 3 ? 'priority-badge-high' :
            'priority-badge-urgent']">
            {{ ticket.priority.name }}
          </div>
        </div>
      </div>
    </div>

    <div class="ticket-card-body">
      <h3 class="text-lg font-medium mb-2" :class="$store.state.darkMode ? 'text-white' : 'text-gray-900'">{{ ticket.subject }}</h3>
      <div class="text-sm mb-2" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-500'">
        <div v-if="ticket.department" class="mb-1">
          <span class="font-medium">{{ $t('Department') }}:</span> {{ ticket.department.name }}
        </div>
        <div v-if="ticket.concern" class="mb-1">
          <span class="font-medium">{{ $t('Concern') }}:</span> {{ ticket.concern.name }}
        </div>
        <div v-if="isWifiHelpdesk && ticket.voucher_code" class="mb-1">
          <span class="font-medium">{{ $t('Voucher') }}:</span> {{ ticket.voucher_code }}
        </div>
        <div v-if="ticket.scheduled_visit_at" class="flex items-center mb-1">
          <svg-vue class="h-4 w-4 mr-1" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-600'" icon="font-awesome.calendar-alt-light"></svg-vue>
          {{ ticket.scheduled_visit_at | momentFormatDateTime }}
        </div>
      </div>
    </div>

    <div class="ticket-card-footer">
      <div class="flex justify-between items-center">
        <div class="text-xs" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-500'">
          {{ ticket.updated_at | momentFormatDateTimeAgo }}
        </div>
        <div class="flex items-center">
          <div v-if="ticket.agent" class="flex items-center text-sm" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-500'">
            <span class="mr-1">{{ $t('Agent') }}:</span>
            <span class="font-medium">{{ ticket.agent.name }}</span>
          </div>
          <div v-else class="text-sm" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-500'">{{ $t('Unassigned') }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TicketCard",
  props: {
    ticket: {
      type: Object,
      required: true
    },
    isNew: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    isWifiHelpdesk() {
      return this.$store.state.user &&
             this.$store.state.user.departments &&
             this.$store.state.user.departments.some(dept => dept.name.toLowerCase().includes('wifi'));
    }
  },
  filters: {
    momentFormatDateTimeAgo: function (value) {
      return moment(value).locale(window.app.app_date_locale).fromNow();
    },
    momentFormatDateTime: function (value) {
      // Parse the ISO string with the timezone information preserved
      return moment.utc(value).tz(window.app.app_timezone).locale(window.app.app_date_locale).format(window.app.app_date_format + ' h:mm A');
    },
  }
}
</script>
