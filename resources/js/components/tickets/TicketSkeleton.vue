<template>
  <div class="animate-pulse">
    <div v-if="isGrid" class="ticket-card">
      <div class="ticket-card-header">
        <div class="flex justify-between items-start">
          <div class="flex items-center space-x-2">
            <div class="h-8 w-8 rounded-full bg-gray-200"></div>
            <div>
              <div class="h-4 w-24 bg-gray-200 rounded"></div>
              <div class="h-3 w-32 bg-gray-200 rounded mt-1"></div>
            </div>
          </div>
          <div class="flex space-x-1">
            <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
            <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
          </div>
        </div>
      </div>
      
      <div class="ticket-card-body">
        <div class="h-5 w-3/4 bg-gray-200 rounded mb-2"></div>
        <div class="h-4 w-1/2 bg-gray-200 rounded mb-2"></div>
        <div class="h-4 w-2/3 bg-gray-200 rounded mb-2"></div>
        <div class="h-4 w-1/3 bg-gray-200 rounded mb-2"></div>
      </div>
      
      <div class="ticket-card-footer">
        <div class="flex justify-between items-center">
          <div class="h-3 w-20 bg-gray-200 rounded"></div>
          <div class="h-4 w-24 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>
    
    <div v-else class="border-b border-gray-200 py-4 px-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="h-4 w-4 bg-gray-200 rounded"></div>
          <div class="h-10 w-10 rounded-full bg-gray-200"></div>
          <div>
            <div class="h-4 w-32 bg-gray-200 rounded mb-2"></div>
            <div class="h-3 w-48 bg-gray-200 rounded"></div>
          </div>
        </div>
        <div class="flex space-x-4">
          <div class="h-5 w-16 bg-gray-200 rounded"></div>
          <div class="h-5 w-16 bg-gray-200 rounded"></div>
          <div class="h-5 w-16 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TicketSkeleton",
  props: {
    isGrid: {
      type: Boolean,
      default: false
    }
  }
}
</script>
