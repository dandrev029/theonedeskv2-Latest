<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white text-center">
          {{ $t('Help & FAQs') }}
        </h1>
      </header>

      <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8">
        <!-- Search Bar (Future Enhancement) -->
        <!-- 
        <div class="mb-8">
          <input 
            type="text" 
            v-model="searchTerm" 
            :placeholder="$t('Search FAQs...')"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
          />
        </div>
        -->

        <div v-if="loading" class="text-center py-10">
          <p class="text-gray-600 dark:text-gray-400">{{ $t('Loading FAQs...') }}</p>
          <!-- You can add a spinner here -->
        </div>

        <div v-else-if="error" class="text-center py-10">
          <p class="text-red-500">{{ $t('Error loading FAQs. Please try again later.') }}</p>
        </div>
        
        <div v-else>
          <!-- WiFi Helpdesk FAQs Section -->
          <section class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6 border-b pb-2 border-gray-300 dark:border-gray-700">
              {{ $t('WiFi Helpdesk FAQs') }}
            </h2>
            <div v-if="wifiFaqs.length > 0" class="space-y-4">
              <faq-item v-for="faq in filteredWifiFaqs" :key="faq.id" :faq="faq" />
            </div>
            <div v-else>
              <p class="text-gray-600 dark:text-gray-400">{{ $t('No WiFi Helpdesk FAQs available at the moment.') }}</p>
            </div>
          </section>

          <!-- General Helpdesk FAQs Section -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6 border-b pb-2 border-gray-300 dark:border-gray-700">
              {{ $t('General Helpdesk FAQs') }}
            </h2>
            <div v-if="generalFaqs.length > 0" class="space-y-4">
              <faq-item v-for="faq in filteredGeneralFaqs" :key="faq.id" :faq="faq" />
            </div>
            <div v-else>
              <p class="text-gray-600 dark:text-gray-400">{{ $t('No General Helpdesk FAQs available at the moment.') }}</p>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

// FaqItem component for individual expandable/collapsible FAQs
const FaqItem = {
  props: ['faq'],
  data() {
    return {
      isOpen: false,
    };
  },
  template: `
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
      <button
        @click="isOpen = !isOpen"
        class="w-full flex justify-between items-center p-4 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none"
      >
        <span class="font-medium">{{ faq.question }}</span>
        <svg
          :class="{'transform rotate-180': isOpen}"
          class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
      >
        <div v-show="isOpen" class="p-4 border-t border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">
          <p v-html="faq.answer"></p>
        </div>
      </transition>
    </div>
  `,
};

export default {
  name: 'FaqPage',
  components: {
    FaqItem,
  },
  data() {
    return {
      allFaqs: {},
      wifiFaqs: [],
      generalFaqs: [],
      loading: true,
      error: null,
      searchTerm: '', // For future search functionality
    };
  },
  computed: {
    // For future search functionality
    filteredWifiFaqs() {
      if (!this.searchTerm) return this.wifiFaqs;
      return this.wifiFaqs.filter(faq => 
        faq.question.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        faq.answer.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
    },
    filteredGeneralFaqs() {
      if (!this.searchTerm) return this.generalFaqs;
      return this.generalFaqs.filter(faq => 
        faq.question.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        faq.answer.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
    }
  },
  async created() {
    this.loading = true;
    this.error = null;
    try {
      // Fetch WiFi FAQs
      const wifiResponse = await axios.get('/api/public/faqs/wifi');
      this.wifiFaqs = wifiResponse.data.data || []; // Assuming API returns { data: [...] }

      // Fetch General FAQs
      const generalResponse = await axios.get('/api/public/faqs/general');
      this.generalFaqs = generalResponse.data.data || []; // Assuming API returns { data: [...] }

    } catch (err) {
      console.error('Error fetching FAQs:', err);
      this.error = true;
    } finally {
      this.loading = false;
    }
  },
};
</script>

<style scoped>
/* Add any page-specific styles here if needed */
.container {
  max-width: 900px; /* Adjust max-width as needed */
}
</style>
