<template>
    <div v-on-clickaway="closeDropdown" class="relative" style="position: relative; z-index: 30;">
        <div class="inline-block w-full rounded-md shadow-sm cursor-pointer">
            <button
                aria-expanded="true"
                aria-haspopup="listbox"
                aria-labelledby="listbox-label"
                class="relative w-full rounded-md border border-gray-400 bg-white text-left focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                type="button"
                @click="openDropdown"
            >
                <template>
                    <div class="flex items-center space-x-3">
                        <template v-if="open && searchable">
                            <div class="relative w-full">
                                <input
                                    ref="search"
                                    v-model="search"
                                    :placeholder="$t('Search')"
                                    aria-label="Search"
                                    class="pl-3 pr-10 py-2 relative w-full rounded-md focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                                    @click.prevent
                                >
                            </div>
                        </template>
                        <div v-else class="flex items-center space-x-3 w-full pl-3 pr-10 py-2">
                            <template v-if="!anySelected">
                                <span class="block truncate">{{ placeholder || $t('Select an option') }}</span>
                            </template>
                            <template v-else-if="multiple">
                                <span class="block truncate">{{ $t('Selected') }} {{ Object.keys(selected).length }} {{ $t('options') }}</span>
                            </template>
                            <template v-else>
                                <template v-for="option in options">
                                    <template v-if="option[optionKey] === selected">
                                        <slot :anySelected="anySelected" :option="option" name="selectedOption">
                                            <span class="block truncate">{{ option[optionLabel] }}</span>
                                        </slot>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </template>
                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 20 20"><path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                </span>
            </button>
        </div>
        <div v-show="open" class="absolute z-50 mt-1 mb-2 w-full rounded-md bg-white shadow-lg" style="position: absolute; top: 100%; left: 0;">
            <ul
                class="max-h-60 rounded-md py-1 text-base leading-6 shadow-xs overflow-auto focus:outline-none sm:text-sm sm:leading-5"
                style="max-height: 300px; overflow-y: auto; position: relative;"
                role="listbox"
                tabindex="-1"
            >
                <template v-if="!searchable">
                    <li
                        v-if="!required"
                        id="listbox-item-0"
                        class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                        role="option"
                        @click="selectOption(null)"
                    >
                        <div class="flex items-center space-x-3">
                            <span class="font-normal block truncate">
                                {{ $t('None') }}
                            </span>
                        </div>
                    </li>
                    <template v-for="(option, index) in filteredOptions">
                        <li
                            :id="'listbox-item-' + index"
                            class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                            role="option"
                            @click="selectOption(option[optionKey])"
                        >
                            <slot :anySelected="anySelected" :option="option" name="selectOption">
                                <div class="flex items-center space-x-3">
                                    <template v-if="multiple">
                                        <div :class="Object.values(selected).indexOf(option[optionKey]) > -1 ? 'font-semibold' : 'font-normal'" class="font-normal block truncate">
                                            {{ option[optionLabel] }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div :class="option[optionKey] === selected ? 'font-semibold' : 'font-normal'" class="font-normal block truncate">
                                            {{ option[optionLabel] }}
                                        </div>
                                    </template>
                                </div>
                            </slot>
                        </li>
                    </template>
                </template>
                <template v-else>
                    <li
                        v-if="!required"
                        id="listbox-item-0"
                        class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                        role="option"
                        @click="selectOption(null)"
                    >
                        <div class="flex items-center space-x-3">
                            <span class="font-normal block truncate">
                                {{ $t('None') }}
                            </span>
                        </div>
                    </li>
                    <template v-for="(option, index) in filteredOptions">
                        <li
                            :id="'listbox-item-' + index"
                            class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                            role="option"
                            @click="selectOption(option[optionKey])"
                        >
                            <slot :anySelected="anySelected" :option="option" name="selectOption">
                                <div class="flex items-center space-x-3">
                                    <template v-if="multiple">
                                        <div :class="Object.values(selected).indexOf(option[optionKey]) > -1 ? 'font-semibold' : 'font-normal'" class="font-normal block truncate">
                                            {{ option[optionLabel] }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div :class="option[optionKey] === selected ? 'font-semibold' : 'font-normal'" class="font-normal block truncate">
                                            {{ option[optionLabel] }}
                                        </div>
                                    </template>
                                </div>
                            </slot>
                        </li>
                    </template>
                </template>
            </ul>
        </div>
    </div>
</template>

<script>
import {mixin as clickaway} from '../../utilities/vue-clickaway-compat';

export default {
    name: "input-select-scrollable",
    mixins: [clickaway],
    props: {
        value: {
            type: [String, Number, Array, Object],
            required: false
        },
        options: {
            type: Array,
            required: true
        },
        optionKey: {
            type: String,
            default: 'id'
        },
        optionLabel: {
            type: String,
            default: 'value'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        searchable: {
            type: Boolean,
            default: false
        },
        required: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: ''
        },
    },
    computed: {
        selected: {
            get() {
                return this.value
            },
            set(selected) {
                this.$emit('input', selected)
            }
        },
        anySelected() {
            if (this.multiple) {
                return Object.keys(this.selected).length !== 0;
            }
            return this.selected !== null;
        },
        filteredOptions() {
            const self = this;
            return self.options.filter(option => {
                if (self.search) {
                    return option[self.optionLabel].toLowerCase().includes(self.search.toLowerCase());
                }
                return true;
            })
        }
    },
    watch: {
        selected() {
            this.$emit('change');
        },
        open(e) {
            const self = this;
            if (e === true && self.searchable) {
                setTimeout(function () {
                    self.$refs.search.focus();
                }, 100);
            }

            if (e === true) {
                // Add resize listener when dropdown opens
                window.addEventListener('resize', this.adjustDropdownPosition);
            } else {
                // Remove resize listener when dropdown closes
                window.removeEventListener('resize', this.adjustDropdownPosition);
            }
        }
    },
    data() {
        return {
            open: false,
            search: null,
        }
    },
    mounted() {
        // Add scroll event listener to handle dropdown position when page scrolls
        window.addEventListener('scroll', this.adjustDropdownPosition, true);
    },
    beforeDestroy() {
        // Clean up event listeners
        window.removeEventListener('resize', this.adjustDropdownPosition);
        window.removeEventListener('scroll', this.adjustDropdownPosition, true);
    },
    methods: {
        openDropdown() {
            const self = this;
            if (self.open && self.searchable && (self.search === null || self.search === '')) {
                self.open = false;
                self.search = null;
            } else if (self.open && !self.searchable) {
                self.open = false;
            } else {
                self.open = true;
                if (self.searchable) {
                    setTimeout(function () {
                        self.$refs.search.focus();
                    }, 100);
                }
                // Ensure dropdown is visible in viewport
                self.$nextTick(() => {
                    self.adjustDropdownPosition();
                });
            }
        },
        closeDropdown() {
            this.open = false;
            this.search = null;
        },
        selectOption(option) {
            if (this.multiple) {
                if (option === null) {
                    this.selected = [];
                } else {
                    if (this.selected.indexOf(option) > -1) {
                        this.selected.splice(this.selected.indexOf(option), 1);
                    } else {
                        this.selected.push(option);
                    }
                }
            } else {
                this.selected = option;
            }
            this.closeDropdown();
        },
        adjustDropdownPosition() {
            // Get dropdown element
            const dropdown = this.$el.querySelector('.absolute.z-50');
            if (!dropdown) return;

            // Get viewport dimensions
            const viewportHeight = window.innerHeight;
            const dropdownRect = dropdown.getBoundingClientRect();

            // Check if dropdown extends beyond viewport
            if (dropdownRect.bottom > viewportHeight) {
                // Position dropdown above the input if it would extend beyond viewport
                dropdown.style.top = 'auto';
                dropdown.style.bottom = '100%';
                dropdown.style.maxHeight = (dropdownRect.top - 20) + 'px'; // 20px buffer
            } else {
                // Position dropdown below the input (default)
                dropdown.style.top = '100%';
                dropdown.style.bottom = 'auto';
            }
        }
    }
}
</script>
