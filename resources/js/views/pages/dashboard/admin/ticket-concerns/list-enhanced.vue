<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <!-- Header Section -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('Ticket Concerns') }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $t('Manage ticket concern categories for your support system') }}
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <router-link
                        class="gradient-button shadow-sm rounded-md flex items-center px-4 py-2"
                        to="/dashboard/admin/ticket-concerns/new"
                    >
                        {{ $t('Create Ticket Concern') }}
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div class="col-span-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Search') }}</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.search-solid"></svg-vue>
                            </div>
                            <input
                                id="search"
                                v-model="filters.search"
                                type="text"
                                class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                :placeholder="$t('Search by name')"
                                @input="debounceSearch"
                            />
                        </div>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-span-1">
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Department') }}</label>
                        <div class="relative">
                            <select
                                id="department"
                                v-model="filters.department"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md appearance-none"
                                @change="applyFilters"
                            >
                                <option value="">{{ $t('All Departments') }}</option>
                                <option v-for="department in departments" :key="department.id" :value="department.id">
                                    {{ department.name }}
                                </option>
                                <!-- Fallback options if API fails -->
                                <option v-if="!departments.some(d => d.name === 'DASMA General Helpdesk')" value="dasma_general">DASMA General Helpdesk</option>
                                <option v-if="!departments.some(d => d.name === 'CAMPA General Helpdesk')" value="campa_general">CAMPA General Helpdesk</option>
                                <option v-if="!departments.some(d => d.name === 'Dasma WiFi HELPDESK')" value="dasma_wifi">Dasma WiFi HELPDESK</option>
                                <option v-if="!departments.some(d => d.name === 'Campa WiFi Helpdesk')" value="campa_wifi">Campa WiFi Helpdesk</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg-vue class="h-4 w-4" icon="font-awesome.chevron-down-solid"></svg-vue>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-span-1">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Status') }}</label>
                        <div class="relative">
                            <select
                                id="status"
                                v-model="filters.status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md appearance-none"
                                @change="applyFilters"
                            >
                                <option value="">{{ $t('All Statuses') }}</option>
                                <option value="true">{{ $t('Active') }}</option>
                                <option value="false">{{ $t('Inactive') }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg-vue class="h-4 w-4" icon="font-awesome.chevron-down-solid"></svg-vue>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <loading :status="loading"/>

                <!-- Table Header -->
                <div v-if="!loading && ticketConcerns.length > 0" class="border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                    <div class="flex-1 flex items-center">
                        <span class="text-sm text-gray-700">
                            {{ $t('Showing') }} <span class="font-medium">{{ ticketConcerns.length }}</span> {{ $t('results') }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <label for="sort" class="sr-only">{{ $t('Sort by') }}</label>
                        <select
                            id="sort"
                            v-model="sortBy"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                            @change="sortTicketConcerns"
                        >
                            <option value="name_asc">{{ $t('Name (A-Z)') }}</option>
                            <option value="name_desc">{{ $t('Name (Z-A)') }}</option>
                            <option value="created_at_desc">{{ $t('Newest First') }}</option>
                            <option value="created_at_asc">{{ $t('Oldest First') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Ticket Concerns Grid -->
                <div v-if="!loading && ticketConcerns.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                    <div
                        v-for="ticketConcern in ticketConcerns"
                        :key="ticketConcern.id"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden"
                    >
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                                        {{ ticketConcern.name }}
                                    </h3>
                                    <div class="mt-1 flex items-center">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                ticketConcern.status
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ ticketConcern.status ? $t('Active') : $t('Inactive') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <router-link
                                        :to="'/dashboard/admin/ticket-concerns/' + ticketConcern.id + '/edit'"
                                        class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none"
                                        :title="$t('Edit')"
                                    >
                                        <svg-vue class="h-5 w-5" icon="font-awesome.edit-regular"></svg-vue>
                                    </router-link>
                                    <button
                                        @click.prevent="deleteTicketConcern(ticketConcern)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 focus:outline-none"
                                        :title="$t('Delete')"
                                    >
                                        <svg-vue class="h-5 w-5" icon="font-awesome.trash-alt-regular"></svg-vue>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">
                                <!-- Department -->
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-300">
                                    <svg-vue class="flex-shrink-0 mr-1.5 h-4 w-4" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.building-regular"></svg-vue>
                                    <span>{{ $t('Department') }}: {{ ticketConcern.department ? ticketConcern.department.name : $t('Not assigned') }}</span>
                                </div>

                                <!-- Assigned User -->
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-300">
                                    <svg-vue class="flex-shrink-0 mr-1.5 h-4 w-4" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.user-regular"></svg-vue>
                                    <span v-if="ticketConcern.assigned_user">
                                        <img
                                            :src="ticketConcern.assigned_user.avatar || ticketConcern.assigned_user.gravatar"
                                            :alt="ticketConcern.assigned_user.name"
                                            class="h-4 w-4 rounded-full mr-1 inline"
                                        >
                                        {{ ticketConcern.assigned_user.name }}
                                    </span>
                                    <span v-else>{{ $t('Not assigned') }}</span>
                                </div>

                                <!-- Ticket Count -->
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-300">
                                    <svg-vue class="flex-shrink-0 mr-1.5 h-4 w-4" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.ticket-alt-regular"></svg-vue>
                                    <span>{{ $t('Tickets') }}: {{ ticketConcern.tickets_count || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex justify-end">
                            <router-link
                                :to="'/dashboard/admin/ticket-concerns/' + ticketConcern.id + '/edit'"
                                class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                            >
                                {{ $t('View Details') }} →
                            </router-link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <template v-else-if="!loading && ticketConcerns.length === 0">
                    <div class="h-full flex">
                        <div class="m-auto">
                            <div class="grid grid-cols-1 justify-items-center h-full w-full px-4 py-10">
                                <div class="flex justify-center items-center">
                                    <svg-vue class="h-full h-auto w-64 mb-6 text-gray-300" icon="undraw.browsing"></svg-vue>
                                </div>
                                <div class="flex justify-center items-center">
                                    <div class="w-full font-semibold text-2xl text-gray-700">{{ $t('No ticket concerns found') }}</div>
                                </div>
                                <div class="mt-2 text-center text-gray-500">
                                    {{ $t('Try adjusting your search or filter to find what you\'re looking for.') }}
                                    <div v-if="filters.department" class="mt-1 text-sm text-red-500">
                                        {{ $t('No query results for model [App\\Models\\TicketConcern] departments.') }}
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button
                                        @click="resetFilters"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                    >
                                        <svg-vue class="h-4 w-4 mr-2" icon="font-awesome.sync-regular"></svg-vue>
                                        {{ $t('Reset Filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg-vue class="h-6 w-6 text-red-600" icon="font-awesome.exclamation-triangle-solid"></svg-vue>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $t('Delete Ticket Concern') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ $t('Are you sure you want to delete this ticket concern? This action cannot be undone.') }}
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        {{ selectedTicketConcern ? selectedTicketConcern.name : '' }}
                                    </p>
                                    <p v-if="selectedTicketConcern && selectedTicketConcern.tickets_count > 0" class="mt-2 text-sm text-red-600">
                                        {{ $t('Warning: This concern is used by {count} tickets. Deleting it may affect those tickets.', { count: selectedTicketConcern.tickets_count }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="deleting"
                            @click="confirmDelete"
                        >
                            <svg-vue v-if="deleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" icon="font-awesome.spinner-solid"></svg-vue>
                            {{ deleting ? $t('Deleting...') : $t('Delete') }}
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="showDeleteModal = false"
                        >
                            {{ $t('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div
            v-if="notification.show"
            class="fixed bottom-0 right-0 m-6 w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
            :class="{ 'animate-enter': notification.show, 'animate-leave': !notification.show }"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg-vue
                            class="h-6 w-6"
                            :class="notification.type === 'success' ? 'text-green-500' : 'text-red-500'"
                            :icon="notification.type === 'success' ? 'font-awesome.check-circle-solid' : 'font-awesome.exclamation-circle-solid'"
                        ></svg-vue>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">
                            {{ notification.title }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ notification.message }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            @click="notification.show = false"
                        >
                            <span class="sr-only">{{ $t('Close') }}</span>
                            <svg-vue class="h-5 w-5" icon="font-awesome.times-solid"></svg-vue>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import debounce from 'lodash/debounce';

export default {
    name: "ticket-concerns-list-enhanced",
    metaInfo() {
        return {
            title: this.$i18n.t('Ticket Concerns')
        }
    },
    data() {
        return {
            loading: true,
            deleting: false,
            ticketConcerns: [],
            departments: [],
            filters: {
                search: '',
                department: '',
                status: ''
            },
            sortBy: 'name_asc',
            showDeleteModal: false,
            selectedTicketConcern: null,
            notification: {
                show: false,
                type: 'success',
                title: '',
                message: '',
                timeout: null
            }
        }
    },
    mounted() {
        this.initialize();
    },
    methods: {
        initialize() {
            // First get departments, then get ticket concerns
            this.getDepartments()
                .then(() => {
                    this.getTicketConcerns();
                })
                .catch(error => {
                    console.error('Error during initialization:', error);
                    this.loading = false;
                });
        },
        getTicketConcerns() {
            const self = this;
            self.loading = true;

            // Build query parameters
            const params = {};
            if (self.filters.search) params.search = self.filters.search;
            if (self.filters.department) params.department_id = self.filters.department;
            if (self.filters.status !== '') params.status = self.filters.status;

            return axios.get('api/dashboard/admin/ticket-concerns', { params })
                .then(function (response) {
                    self.ticketConcerns = response.data.data;
                    self.sortTicketConcerns();
                    self.loading = false;
                })
                .catch(function (error) {
                    self.showNotification('error', self.$t('Error'), self.$t('Failed to load ticket concerns'));
                    console.error('Error loading ticket concerns:', error);
                    self.loading = false;
                });
        },
        getDepartments() {
            const self = this;
            self.loading = true;

            // First try to fix departments
            return axios.get('fix-ticket-concern-departments.php')
                .then(function(fixResponse) {
                    console.log('Fix departments response:', fixResponse.data);

                    // Now try the main endpoint with public access
                    return axios.get('api/dashboard/admin/ticket-concerns/departments/public')
                        .then(function (response) {
                            if (response.data && response.data.data) {
                                self.departments = response.data.data;

                                // If departments are empty, add fallback departments
                                if (!self.departments || self.departments.length === 0) {
                                    console.warn('No departments returned from API');
                                    self.addFallbackDepartments();
                                    self.showNotification('warning', self.$t('Warning'), self.$t('Using fallback departments'));
                                } else {
                                    console.log('Departments loaded successfully:', self.departments.length);
                                }
                            } else {
                                console.warn('Invalid department data format:', response.data);
                                self.addFallbackDepartments();
                                self.showNotification('warning', self.$t('Warning'), self.$t('Using fallback departments due to invalid data format'));
                            }
                            return self.departments;
                        })
                        .catch(function (error) {
                            console.error('Error loading departments:', error);

                            // Try the public endpoint as a fallback
                            return axios.get('api/ticket-concerns/departments')
                                .then(function(response) {
                                    if (response.data && response.data.data) {
                                        self.departments = response.data.data;
                                        console.log('Departments loaded from public endpoint:', self.departments.length);
                                        return self.departments;
                                    } else {
                                        throw new Error('Invalid data from public endpoint');
                                    }
                                })
                                .catch(function(secondError) {
                                    console.error('Error loading from public endpoint:', secondError);
                                    self.showNotification('error', self.$t('Error'), self.$t('Failed to load departments'));
                                    self.addFallbackDepartments();
                                    return self.departments;
                                });
                        });
                })
                .catch(function(fixError) {
                    console.error('Error fixing departments:', fixError);

                    // Continue with normal flow
                    return axios.get('api/dashboard/admin/ticket-concerns/departments/public')
                        .then(function (response) {
                            if (response.data && response.data.data) {
                                self.departments = response.data.data;
                                return self.departments;
                            } else {
                                throw new Error('Invalid department data format');
                            }
                        })
                        .catch(function (error) {
                            console.error('Error loading departments after fix attempt:', error);
                            self.addFallbackDepartments();
                            return self.departments;
                        });
                });
        },

        addFallbackDepartments() {
            // Add fallback departments with numeric IDs to better match expected format
            this.departments = [
                { id: 1, name: 'DASMA General Helpdesk' },
                { id: 2, name: 'CAMPA General Helpdesk' },
                { id: 3, name: 'Dasma WiFi HELPDESK' },
                { id: 4, name: 'Campa WiFi Helpdesk' }
            ];
            console.log('Using fallback departments:', this.departments);

            // Try to create these departments in the backend
            this.createMissingDepartments();

            // Refresh the page after a short delay to ensure departments are loaded
            setTimeout(() => {
                this.getTicketConcerns();
            }, 1000);
        },

        createMissingDepartments() {
            // This function attempts to create the fallback departments in the database
            // so they'll be available for future requests

            // We'll use the admin department creation endpoint if available
            axios.post('api/dashboard/admin/fix-departments', {
                departments: [
                    { name: 'DASMA General Helpdesk', public: true, all_agents: true },
                    { name: 'CAMPA General Helpdesk', public: true, all_agents: true },
                    { name: 'Dasma WiFi HELPDESK', public: true, all_agents: true },
                    { name: 'Campa WiFi Helpdesk', public: true, all_agents: true }
                ]
            })
            .then(response => {
                console.log('Department creation response:', response.data);
                if (response.data && response.data.success) {
                    console.log('Successfully created missing departments');
                }
            })
            .catch(error => {
                console.error('Failed to create missing departments:', error);
                // We don't show a notification here as this is a background operation
            });
        },
        sortTicketConcerns() {
            const [field, direction] = this.sortBy.split('_');

            this.ticketConcerns.sort((a, b) => {
                let comparison = 0;

                if (field === 'name') {
                    comparison = a.name.localeCompare(b.name);
                } else if (field === 'created_at') {
                    const dateA = new Date(a.created_at);
                    const dateB = new Date(b.created_at);
                    comparison = dateA - dateB;
                }

                return direction === 'asc' ? comparison : -comparison;
            });
        },
        debounceSearch: debounce(function() {
            this.applyFilters();
        }, 300),
        applyFilters() {
            // If using a fallback department, we need to handle it differently
            const fallbackDeptIds = [1, 2, 3, 4]; // Numeric IDs for our fallback departments
            if (this.filters.department && fallbackDeptIds.includes(parseInt(this.filters.department))) {
                // For fallback departments, we'll filter client-side
                this.loading = true;

                // Get all ticket concerns first
                axios.get('api/dashboard/admin/ticket-concerns')
                    .then(response => {
                        if (response.data && response.data.data) {
                            const allConcerns = response.data.data;

                            // Filter based on department name or ID
                            const departmentId = parseInt(this.filters.department);
                            const department = this.departments.find(d => d.id === departmentId);

                            if (department) {
                                this.ticketConcerns = allConcerns.filter(concern => {
                                    // Try to match by department ID first
                                    if (concern.department && concern.department.id === departmentId) {
                                        return true;
                                    }

                                    // Fall back to matching by name (case insensitive partial match)
                                    return concern.department &&
                                           concern.department.name.toLowerCase().includes(department.name.toLowerCase());
                                });
                            } else {
                                this.ticketConcerns = allConcerns;
                            }

                            // Apply other filters
                            if (this.filters.search) {
                                const search = this.filters.search.toLowerCase();
                                this.ticketConcerns = this.ticketConcerns.filter(concern =>
                                    concern.name.toLowerCase().includes(search)
                                );
                            }

                            if (this.filters.status !== '') {
                                const status = this.filters.status === 'true';
                                this.ticketConcerns = this.ticketConcerns.filter(concern =>
                                    concern.status === status
                                );
                            }

                            this.sortTicketConcerns();
                        } else {
                            console.warn('Invalid ticket concerns data format:', response.data);
                            this.ticketConcerns = [];
                            this.showNotification('error', this.$t('Error'), this.$t('Failed to load ticket concerns data'));
                        }
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error applying filters:', error);
                        this.showNotification('error', this.$t('Error'), this.$t('Failed to apply filters'));
                        this.loading = false;
                    });
            } else {
                // Normal API filtering
                this.getTicketConcerns();
            }
        },
        resetFilters() {
            this.filters = {
                search: '',
                department: '',
                status: ''
            };
            this.getTicketConcerns();
        },
        deleteTicketConcern(ticketConcern) {
            this.selectedTicketConcern = ticketConcern;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            if (!this.selectedTicketConcern) return;

            const self = this;
            self.deleting = true;

            axios.delete(`api/dashboard/admin/ticket-concerns/${self.selectedTicketConcern.id}`)
                .then(function (response) {
                    self.showDeleteModal = false;
                    self.showNotification('success', self.$t('Success'), response.data.message || self.$t('Ticket concern deleted successfully'));
                    self.getTicketConcerns();
                })
                .catch(function (error) {
                    self.showNotification('error', self.$t('Error'), error.response?.data?.message || self.$t('Failed to delete ticket concern'));
                })
                .finally(function () {
                    self.deleting = false;
                    self.selectedTicketConcern = null;
                });
        },
        showNotification(type, title, message) {
            // Clear any existing timeout
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }

            // Set notification data
            this.notification.type = type;
            this.notification.title = title;
            this.notification.message = message;
            this.notification.show = true;

            // Auto-hide after 5 seconds
            this.notification.timeout = setTimeout(() => {
                this.notification.show = false;
            }, 5000);
        },
        formatDate(date) {
            return moment(date).format('LL');
        }
    }
}
</script>

<style scoped>
.animate-enter {
    animation: slideIn 0.3s ease-out;
}

.animate-leave {
    animation: slideOut 0.3s ease-in forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>
