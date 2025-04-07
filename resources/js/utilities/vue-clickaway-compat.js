/**
 * Vue 2 compatibility wrapper for vue-clickaway
 * This file provides a compatibility layer for using vue-clickaway in Vue 2 projects
 */

import { directive as clickOutside } from 'vue-clickaway';

// Export the clickaway mixin for Vue 2 compatibility
export const mixin = {
  directives: {
    clickOutside,
  },
  methods: {
    // This method proxies the v-on-clickaway directive
    closeViaClickaway(callback) {
      return {
        name: 'clickOutside',
        value: callback
      };
    }
  }
};

// Export the directive for direct use
export const directive = clickOutside;