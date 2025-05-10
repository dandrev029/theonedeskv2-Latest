<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('New Ticket Concern') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <router-link
                        class="btn btn-white shadow-sm rounded-md"
                        to="/dashboard/admin/ticket-concerns"
                    >
                        {{ $t('Back to List') }}
                    </router-link>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="my-6 bg-white shadow overflow-hidden sm:rounded-md">
                <loading :status="loading"/>
                <form @submit.prevent="saveTicketConcern" class="p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium leading-5 text-gray-700" for="name">
                            {{ $t('Name') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input
                                id="name"
                                v-model="ticketConcern.name"
                                class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                type="text"
                                required
                                :placeholder="$t('Enter concern name')"
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $t('A descriptive name for this ticket concern') }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium leading-5 text-gray-700">
                            {{ $t('Status') }}
                        </label>
                        <div class="mt-2">
                            <div class="flex items-center">
                                <input
                                    id="status-active"
                                    v-model="ticketConcern.status"
                                    name="status"
                                    type="radio"
                                    :value="true"
                                    class="form-radio h-5 w-5 text-indigo-600 transition duration-150 ease-in-out"
                                >
                                <label for="status-active" class="ml-3">
                                    <span class="block text-sm leading-5 text-gray-700">{{ $t('Active') }}</span>
                                </label>
                            </div>
                            <div class="mt-2 flex items-center">
                                <input
                                    id="status-inactive"
                                    v-model="ticketConcern.status"
                                    name="status"
                                    type="radio"
                                    :value="false"
                                    class="form-radio h-5 w-5 text-indigo-600 transition duration-150 ease-in-out"
                                >
                                <label for="status-inactive" class="ml-3">
                                    <span class="block text-sm leading-5 text-gray-700">{{ $t('Inactive') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Department and Assigned To fields removed as they are considered pointless -->
                    <div class="mb-6">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        {{ $t('Department and user assignment have been removed from ticket concerns. Tickets will be automatically assigned based on user permissions.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 border-t border-gray-200 pt-5">
                        <div class="flex justify-end">
                            <span class="inline-flex rounded-md shadow-sm">
                                <router-link
                                    to="/dashboard/admin/ticket-concerns"
                                    class="py-2 px-4 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out"
                                >
                                    {{ $t('Cancel') }}
                                </router-link>
                            </span>
                            <span class="ml-3 inline-flex rounded-md shadow-sm">
                                <button
                                    id="submit-ticket-concern"
                                    type="submit"
                                    class="inline-flex items-center justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out"
                                    data-style="zoom-in"
                                    :disabled="saving"
                                >
                                    <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ saving ? $t('Saving...') : $t('Save') }}
                                </button>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "ticket-concerns-new",
    metaInfo() {
        return {
            title: this.$i18n.t('New Ticket Concern')
        }
    },
    data() {
        return {
            loading: false,
            saving: false,
            loadingUsers: false,
            loadingDepartments: false,
            dashboardUsers: [],
            departments: [],
            userDepartments: [],
            isAdmin: false,
            ticketConcern: {
                name: '',
                status: true,
                assigned_to: null,
                department_id: null
            },
            formErrors: {}
        }
    },
    computed: {
        userHasOnlyOneDepartment() {
            return this.userDepartments.length === 1;
        },
        singleDepartmentName() {
            return this.userHasOnlyOneDepartment ? this.userDepartments[0].name : '';
        }
    },
    mounted() {
        this.getUserInfo();
        this.getDashboardUsers();
    },
    methods: {
        getDashboardUsers() {
            const self = this;
            self.loadingUsers = true;
            self.loading = true;

            axios.get('api/dashboard/admin/ticket-concerns/users/dashboard')
                .then(function (response) {
                    if (response.data && response.data.data) {
                        self.dashboardUsers = response.data.data;
                    } else {
                        self.$notify({
                            title: self.$i18n.t('Warning').toString(),
                            text: self.$i18n.t('Could not load users data correctly').toString(),
                            type: 'warning'
                        });
                        self.dashboardUsers = [];
                    }
                })
                .catch(function (error) {
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response ? error.response.data.message || self.$i18n.t('Could not load users') : self.$i18n.t('Could not load users'),
                        type: 'error'
                    });
                    self.dashboardUsers = [];
                })
                .finally(function () {
                    self.loadingUsers = false;
                    if (!self.loadingDepartments) {
                        self.loading = false;
                    }
                });
        },
        getUserInfo() {
            const self = this;
            self.loading = true;

            // Get user info to determine if admin and get departments
            axios.get('/api/user')
                .then(function (response) {
                    if (response.data) {
                        // Check if user is admin (role_id = 1)
                        self.isAdmin = response.data.role_id === 1;

                        // If user has departments property, use it
                        if (response.data.departments && Array.isArray(response.data.departments)) {
                            self.userDepartments = response.data.departments;

                            // If user has only one department, automatically set it
                            if (self.userDepartments.length === 1 && !self.isAdmin) {
                                self.ticketConcern.department_id = self.userDepartments[0].id;
                            }
                        }

                        // Now get all departments (for admins) or just user's departments
                        self.getDepartments();
                    }
                })
                .catch(function (error) {
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response ? error.response.data.message || self.$i18n.t('Could not load user info') : self.$i18n.t('Could not load user info'),
                        type: 'error'
                    });
                    // Still try to load departments as fallback
                    self.getDepartments();
                });
        },

        getDepartments() {
            const self = this;
            self.loadingDepartments = true;
            self.loading = true;

            axios.get('/api/ticket-concerns/departments')
                .then(function (response) {
                    let allDepartments = [];

                    if (response.data && response.data.data) {
                        allDepartments = response.data.data;
                    } else if (Array.isArray(response.data)) {
                        allDepartments = response.data;
                    } else {
                        self.$notify({
                            title: self.$i18n.t('Warning').toString(),
                            text: self.$i18n.t('Could not load departments data correctly').toString(),
                            type: 'warning'
                        });
                    }

                    // If user is not admin and has departments, filter to only show their departments
                    if (!self.isAdmin && self.userDepartments.length > 0) {
                        const userDepartmentIds = self.userDepartments.map(dept => dept.id);
                        self.departments = allDepartments.filter(dept => userDepartmentIds.includes(dept.id));
                    } else {
                        self.departments = allDepartments;
                    }
                })
                .catch(function (error) {
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response ? error.response.data.error || error.response.data.message || self.$i18n.t('Could not load departments') : self.$i18n.t('Could not load departments'),
                        type: 'error'
                    });
                    self.departments = [];
                })
                .finally(function () {
                    self.loadingDepartments = false;
                    if (!self.loadingUsers) {
                        self.loading = false;
                    }
                });
        },
        saveTicketConcern() {
            const self = this;
            self.saving = true;
            self.formErrors = {};

            // Validate form before submitting
            if (!self.ticketConcern.name || self.ticketConcern.name.trim() === '') {
                self.formErrors.name = self.$i18n.t('Name is required');
                self.saving = false;
                self.$notify({
                    title: self.$i18n.t('Validation Error').toString(),
                    text: self.formErrors.name,
                    type: 'error'
                });
                return;
            }

            // Set department_id to user's department if they have only one, or null otherwise
            if (self.userHasOnlyOneDepartment && !self.isAdmin && self.userDepartments.length === 1) {
                self.ticketConcern.department_id = self.userDepartments[0].id;
            } else if (self.isAdmin) {
                // For admins, set to null - will be handled by backend logic
                self.ticketConcern.department_id = null;
            }

            // Set assigned_to to null - will be handled by backend logic
            self.ticketConcern.assigned_to = null;

            const ladda = typeof Ladda !== 'undefined' ? Ladda.create(document.querySelector('#submit-ticket-concern')) : null;
            if (ladda) ladda.start();

            axios.post('api/dashboard/admin/ticket-concerns', this.ticketConcern)
                .then(function (response) {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: response.data.message || self.$i18n.t('Ticket concern created successfully'),
                        type: 'success'
                    });
                    self.$router.push('/dashboard/admin/ticket-concerns');
                })
                .catch(function (error) {
                    if (error.response && error.response.data) {
                        if (error.response.data.errors) {
                            self.formErrors = error.response.data.errors;
                            const firstError = Object.values(error.response.data.errors)[0];
                            self.$notify({
                                title: self.$i18n.t('Error').toString(),
                                text: Array.isArray(firstError) ? firstError[0] : firstError,
                                type: 'error'
                            });
                        } else if (error.response.data.message) {
                            self.$notify({
                                title: self.$i18n.t('Error').toString(),
                                text: error.response.data.message,
                                type: 'error'
                            });
                        }
                    } else {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: self.$i18n.t('An unexpected error occurred'),
                            type: 'error'
                        });
                    }

                    if (ladda) ladda.stop();
                })
                .finally(function () {
                    self.saving = false;
                });
        }
    }
}
</script>

<style scoped>
.dropdown-improved :deep(.multiselect__tags) {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    border-color: #d2d6dc;
    min-height: 38px;
}

.dropdown-improved :deep(.multiselect__placeholder) {
    padding-top: 0;
    margin-bottom: 0;
    color: #6b7280;
}

.dropdown-improved :deep(.multiselect__single) {
    padding-top: 0;
    margin-bottom: 0;
}

.dropdown-improved :deep(.multiselect__input) {
    margin-bottom: 0;
}

.dropdown-improved :deep(.multiselect__content-wrapper) {
    border-color: #d2d6dc;
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    max-height: 240px !important; /* Force a larger height for the dropdown */
    overflow-y: auto !important; /* Ensure scrolling is enabled */
    position: absolute !important; /* Ensure the dropdown isn't constrained */
    width: 100% !important; /* Full width */
    z-index: 999 !important; /* Higher z-index to avoid being cut off */
}

.dropdown-improved :deep(.multiselect__element) {
    display: block !important; /* Ensure each option is properly displayed */
}

.dropdown-improved :deep(.multiselect__option--highlight) {
    background-color: #5850ec;
}

.dropdown-improved :deep(.multiselect__option--highlight::after) {
    background-color: #5850ec;
}

/* Fix for mobile view */
@media (max-width: 640px) {
    .dropdown-improved :deep(.multiselect__content-wrapper) {
        position: fixed !important;
        top: auto !important;
        bottom: auto !important;
    }
}
</style>
