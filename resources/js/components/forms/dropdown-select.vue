<template>
  <div class="relative">
    <label v-if="label" class="block text-sm font-medium leading-5" :class="{'text-gray-700': !$store.state.darkMode, 'text-gray-300': $store.state.darkMode}" :for="id">
      {{ label }}
    </label>
    <div class="mt-1 relative">
      <button
        type="button"
        :id="id"
        class="relative w-full rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :class="{'bg-white border-gray-300 text-gray-900': !$store.state.darkMode, 'bg-gray-800 border-gray-700 text-white': $store.state.darkMode}"
        @click="toggleDropdown"
        aria-haspopup="listbox"
        :aria-expanded="open"
      >
        <span class="block truncate">
          {{ selectedOption ? selectedOption.name : placeholder }}
        </span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <svg class="h-5 w-5" :class="{'text-gray-400': !$store.state.darkMode, 'text-gray-500': $store.state.darkMode}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </span>
      </button>
      <div
        v-if="open"
        class="absolute z-50 mt-1 w-full shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" :class="{'bg-white ring-black': !$store.state.darkMode, 'bg-gray-800 ring-gray-700': $store.state.darkMode}"
        style="max-height: 200px; overflow-y: auto;"
      >
        <!-- Add search input field -->
        <div class="px-3 py-2 border-b" :class="{'border-gray-200': !$store.state.darkMode, 'border-gray-700': $store.state.darkMode}" @click.stop>
          <input
            v-model="searchQuery"
            type="text"
            class="w-full border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :class="{'border-gray-300 bg-white text-gray-900': !$store.state.darkMode, 'border-gray-700 bg-gray-700 text-white': $store.state.darkMode}"
            :placeholder="searchPlaceholder"
            @input="onSearch"
          />
        </div>

        <ul tabindex="-1" role="listbox" :aria-labelledby="id">
          <li
            v-for="option in filteredOptions"
            :key="option.id"
            class="cursor-pointer select-none relative py-2 pl-3 pr-9" :class="{
              'text-gray-900 hover:bg-indigo-100': !$store.state.darkMode,
              'text-white hover:bg-indigo-900': $store.state.darkMode,
              'bg-indigo-50': option.id === modelValue && !$store.state.darkMode,
              'bg-indigo-800': option.id === modelValue && $store.state.darkMode
            }"
            @click="selectOption(option.id)"
            role="option"
            :aria-selected="option.id === modelValue"
          >
            <span class="block truncate" :class="{ 'font-semibold': option.id === modelValue }">
              {{ option.name }}
            </span>
            <span v-if="option.id === modelValue" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </span>
          </li>
          <li v-if="filteredOptions.length === 0" class="py-2 pl-3 pr-9" :class="{'text-gray-500': !$store.state.darkMode, 'text-gray-400': $store.state.darkMode}">
            {{ noResultsText }}
          </li>
        </ul>
      </div>
    </div>
    <p v-if="helpText" class="mt-2 text-sm" :class="{'text-gray-500': !$store.state.darkMode, 'text-gray-400': $store.state.darkMode}">{{ helpText }}</p>
  </div>
</template>

<script>
export default {
  name: 'DropdownSelect',
  props: {
    id: {
      type: String,
      required: true
    },
    modelValue: {
      type: [String, Number],
      default: null
    },
    options: {
      type: Array,
      required: true
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Select an option'
    },
    searchPlaceholder: {
      type: String,
      default: 'Search...'
    },
    helpText: {
      type: String,
      default: ''
    },
    noResultsText: {
      type: String,
      default: 'No results found'
    }
  },
  data() {
    return {
      open: false,
      searchQuery: '',
      filteredOptions: []
    }
  },
  watch: {
    options: {
      immediate: true,
      handler(newOptions) {
        this.filteredOptions = [...newOptions];
      }
    }
  },
  computed: {
    selectedOption() {
      if (!this.modelValue) return null;
      return this.options.find(option => option.id === this.modelValue);
    }
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    toggleDropdown() {
      this.open = !this.open;
      if (this.open) {
        this.resetSearch();
      }
    },
    selectOption(value) {
      this.$emit('update:modelValue', value);
      this.open = false;
      this.resetSearch();
    },
    handleClickOutside(event) {
      if (!this.$el.contains(event.target)) {
        this.open = false;
        this.resetSearch();
      }
    },
    onSearch() {
      if (this.searchQuery.trim() === '') {
        this.filteredOptions = [...this.options];
      } else {
        const query = this.searchQuery.toLowerCase();
        this.filteredOptions = this.options.filter(option =>
          option.name.toLowerCase().includes(query)
        );
      }
    },
    resetSearch() {
      this.searchQuery = '';
      this.filteredOptions = [...this.options];
    }
  }
}
</script>

<style scoped>
/* Ensure dropdown appears above other elements */
.z-50 {
  z-index: 50;
}
</style>
