<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <!-- Header Section -->
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-primary-700">{{ $t('Create New Ticket Concern') }}</h1>
                    <p class="mt-2 text-sm text-secondary-600">{{ $t('Set up a new concern category for ticket management') }}</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <router-link
                        class="inline-flex items-center px-4 py-2 border border-secondary-300 rounded-md shadow-sm text-sm font-medium text-secondary-700 bg-white hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                        to="/dashboard/admin/ticket-concerns"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        {{ $t('Back to List') }}
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <loading :status="loadingInitialData"/>

                <!-- Global Success Message -->
                <div v-if="successMessage" class="bg-green-50 border-l-4 border-green-400 p-4 m-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ successMessage }}</p>
                        </div>
                    </div>
                </div>

                <!-- Global Error Message -->
                <div v-if="errorMessage" class="bg-red-50 border-l-4 border-red-400 p-4 m-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ errorMessage }}</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="saveTicketConcern" class="p-8 space-y-8" v-if="!loadingInitialData">
                    <!-- Concern Name Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-secondary-800" for="name">
                            {{ $t('Concern Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            :class="['block w-full px-4 py-3 border rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent', formErrors.name ? 'border-red-300 bg-red-50 text-red-900 placeholder-red-400' : 'border-secondary-300 bg-white text-secondary-900 placeholder-secondary-500 hover:border-secondary-400']"
                            :placeholder="$t('Enter a descriptive name for this concern category')"
                            type="text"
                            autocomplete="off"
                        />
                        <p v-if="formErrors.name" class="text-sm text-red-600">{{ formErrors.name }}</p>
                        <p v-else class="text-sm text-secondary-600">{{ $t('This will be the display name for the concern category.') }}</p>
                    </div>

                    <!-- Status Field -->
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-secondary-800">{{ $t('Status') }}</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input id="status-active" v-model="form.status" type="radio" :value="true" class="h-4 w-4 text-primary-600 border-secondary-300 focus:ring-primary-500">
                                <label for="status-active" class="ml-3 block text-sm font-medium text-secondary-700">
                                    <span class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>{{ $t('Active') }}</span>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="status-inactive" v-model="form.status" type="radio" :value="false" class="h-4 w-4 text-primary-600 border-secondary-300 focus:ring-primary-500">
                                <label for="status-inactive" class="ml-3 block text-sm font-medium text-secondary-700">
                                    <span class="flex items-center"><span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>{{ $t('Inactive') }}</span>
                                </label>
                            </div>
                        </div>
                        <p v-if="formErrors.status" class="text-sm text-red-600">{{ formErrors.status }}</p>
                        <p v-else class="text-sm text-secondary-600">{{ $t('Active concerns will be available for ticket creation.') }}</p>
                    </div>

                    <!-- Condominium Location Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-secondary-800" for="condo_location_id">
                            {{ $t('Condominium Location') }}
                        </label>
                        <div class="relative">
                            <select
                                id="condo_location_id"
                                v-model="form.condo_location_id"
                                :disabled="loadingCondoLocations"
                                :class="['block w-full px-4 py-3 border rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent', formErrors.condo_location_id ? 'border-red-300 bg-red-50 text-red-900' : 'border-secondary-300 bg-white text-secondary-900 hover:border-secondary-400']"
                            >
                                <option :value="null" disabled>{{ loadingCondoLocations ? $t('Loading locations...') : $t('Select a condominium location') }}</option>
                                <option v-for="location in condoLocations" :key="location.id" :value="location.id">
                                    {{ location.name }}
                                </option>
                            </select>
                            <div v-if="loadingCondoLocations" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <p v-if="formErrors.condo_location_id" class="text-sm text-red-600">{{ formErrors.condo_location_id }}</p>
                        <p v-else class="text-sm text-secondary-600">{{ $t('Select the condominium location for this concern.') }}</p>
                    </div>

                    <!-- Assignment Type Field -->
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-secondary-800">{{ $t('Assign To') }} <span class="text-red-500">*</span></label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input id="assign-type-user" v-model="form.assignment_type" type="radio" value="user" class="h-4 w-4 text-primary-600 border-secondary-300 focus:ring-primary-500">
                                <label for="assign-type-user" class="ml-3 block text-sm font-medium text-secondary-700">{{ $t('User') }}</label>
                            </div>
                            <div class="flex items-center">
                                <input id="assign-type-department" v-model="form.assignment_type" type="radio" value="department" class="h-4 w-4 text-primary-600 border-secondary-300 focus:ring-primary-500">
                                <label for="assign-type-department" class="ml-3 block text-sm font-medium text-secondary-700">{{ $t('Department Queue') }}</label>
                            </div>
                        </div>
                         <p v-if="formErrors.assignment_type || formErrors.assigned_to || formErrors.department_id" class="text-sm text-red-600">
                            {{ formErrors.assignment_type || formErrors.assigned_to || formErrors.department_id || $t('An assignment is required.') }}
                         </p>
                    </div>

                    <!-- Assign to User Field (Conditional) -->
                    <div v-if="form.assignment_type === 'user'" class="space-y-2">
                        <label class="block text-sm font-semibold text-secondary-800" for="assigned_to_user_id">
                            {{ $t('Select User') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="assigned_to_user_id"
                                v-model="form.assigned_to"
                                :disabled="loadingUsers"
                                :class="['block w-full px-4 py-3 border rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent', formErrors.assigned_to ? 'border-red-300 bg-red-50 text-red-900' : 'border-secondary-300 bg-white text-secondary-900 hover:border-secondary-400']"
                            >
                                <option :value="null" disabled>{{ loadingUsers ? $t('Loading users...') : $t('Select a user') }}</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                            <div v-if="loadingUsers" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <p v-if="formErrors.assigned_to" class="text-sm text-red-600">{{ formErrors.assigned_to }}</p>
                        <p v-else class="text-sm text-secondary-600">{{ $t('Select a user with dashboard access.') }}</p>
                    </div>

                    <!-- Assign to Department Queue Field (Conditional) -->
                    <div v-if="form.assignment_type === 'department'" class="space-y-2">
                        <label class="block text-sm font-semibold text-secondary-800" for="department_id">
                            {{ $t('Select Department Queue') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                :disabled="loadingDepartments"
                                :class="['block w-full px-4 py-3 border rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent', formErrors.department_id ? 'border-red-300 bg-red-50 text-red-900' : 'border-secondary-300 bg-white text-secondary-900 hover:border-secondary-400']"
                            >
                                <option :value="null" disabled>{{ loadingDepartments ? $t('Loading departments...') : $t('Select a department queue') }}</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                             <div v-if="loadingDepartments" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <p v-if="formErrors.department_id" class="text-sm text-red-600">{{ formErrors.department_id }}</p>
                        <p v-else class="text-sm text-secondary-600">{{ $t('Select the department queue to handle this concern.') }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 border-t border-secondary-200 pt-5">
                        <div class="flex justify-end space-x-3">
                            <router-link
                                to="/dashboard/admin/ticket-concerns"
                                class="py-2 px-4 border border-secondary-300 rounded-md text-sm leading-5 font-medium text-secondary-700 hover:bg-secondary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-400 active:bg-secondary-200 active:text-secondary-800 transition duration-150 ease-in-out"
                            >
                                {{ $t('Cancel') }}
                            </router-link>
                            <button
                                id="submit-ticket-concern"
                                type="submit"
                                :disabled="saving"
                                class="inline-flex items-center justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 active:bg-primary-800 transition duration-150 ease-in-out"
                            >
                                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ saving ? $t('Saving...') : $t('Save Concern') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>

<script>
import axios from 'axios'; // Assuming axios is globally available or imported

export default {
    name: "NewTicketConcern",
    metaInfo() {
        return {
            title: this.$i18n.t('Create New Ticket Concern')
        }
    },
    data() {
        return {
            loadingInitialData: true,
            saving: false,
            loadingUsers: false,
            loadingDepartments: false,
            loadingCondoLocations: false,
            
            form: {
                name: '',
                status: true,
                condo_location_id: null,
                assignment_type: null, // 'user' or 'department'
                assigned_to: null,    // Stores user_id if assignment_type is 'user'
                department_id: null,  // Stores department_id if assignment_type is 'department'
            },
            
            users: [],
            departments: [],
            condoLocations: [],
            
            formErrors: {},
            successMessage: '',
            errorMessage: '',
        };
    },
    watch: {
        'form.assignment_type'(newType) {
            // Reset specific assignment IDs when type changes to prevent sending both
            if (newType === 'user') {
                this.form.department_id = null;
            } else if (newType === 'department') {
                this.form.assigned_to = null;
            }
            // Clear related errors
            this.formErrors.assigned_to = null;
            this.formErrors.department_id = null;
            this.formErrors.assignment_type = null;
        }
    },
    mounted() {
        this.fetchInitialData();
    },
    methods: {
        async fetchInitialData() {
            this.loadingInitialData = true;
            this.successMessage = '';
            this.errorMessage = '';
            try {
                await Promise.all([
                    this.fetchUsers(),
                    this.fetchDepartments(),
                    this.fetchCondoLocations()
                ]);
            } catch (error) {
                this.errorMessage = this.$i18n.t('Error loading initial data. Please try again.');
                console.error("Error fetching initial data:", error);
            } finally {
                this.loadingInitialData = false;
            }
        },
        async fetchUsers() {
            this.loadingUsers = true;
            try {
                const response = await axios.get('/api/dashboard/admin/ticket-concerns/users/dashboard');
                this.users = response.data.data || [];
            } catch (error) {
                console.error("Error fetching users:", error);
                this.formErrors.assigned_to = this.$i18n.t('Could not load users.');
                this.users = [];
            } finally {
                this.loadingUsers = false;
            }
        },
        async fetchDepartments() {
            this.loadingDepartments = true;
            try {
                const response = await axios.get('/api/dashboard/admin/ticket-concerns/user-accessible-departments');
                this.departments = response.data.data || [];
            } catch (error) {
                console.error("Error fetching departments:", error);
                this.formErrors.department_id = this.$i18n.t('Could not load departments.');
                this.departments = [];
            } finally {
                this.loadingDepartments = false;
            }
        },
        async fetchCondoLocations() {
            this.loadingCondoLocations = true;
            try {
                const response = await axios.get('/api/condo-locations/select');
                 if (response.data && Array.isArray(response.data.data)) {
                    this.condoLocations = response.data.data;
                } else if (response.data && Array.isArray(response.data)) {
                    this.condoLocations = response.data;
                } else {
                    this.condoLocations = [];
                }
            } catch (error) {
                console.error("Error fetching condo locations:", error);
                this.formErrors.condo_location_id = this.$i18n.t('Could not load condominium locations.');
                this.condoLocations = [];
            } finally {
                this.loadingCondoLocations = false;
            }
        },
        validateForm() {
            this.formErrors = {};
            let isValid = true;

            if (!this.form.name.trim()) {
                this.formErrors.name = this.$i18n.t('Concern name is required.');
                isValid = false;
            }

            if (!this.form.assignment_type) {
                this.formErrors.assignment_type = this.$i18n.t('Please select an assignment type (User or Department Queue).');
                isValid = false;
            } else {
                if (this.form.assignment_type === 'user' && !this.form.assigned_to) {
                    this.formErrors.assigned_to = this.$i18n.t('Please select a user to assign to.');
                    isValid = false;
                }
                if (this.form.assignment_type === 'department' && !this.form.department_id) {
                    this.formErrors.department_id = this.$i18n.t('Please select a department queue to assign to.');
                    isValid = false;
                }
            }
            
            // condo_location_id is nullable, so no specific validation here unless it becomes required.

            return isValid;
        },
        async saveTicketConcern() {
            this.successMessage = '';
            this.errorMessage = '';
            if (!this.validateForm()) {
                 this.$notify({
                    title: this.$i18n.t('Validation Error').toString(),
                    text: this.$i18n.t('Please correct the errors in the form.'),
                    type: 'error'
                });
                return;
            }

            this.saving = true;
            let payload = {
                name: this.form.name,
                status: this.form.status,
                condo_location_id: this.form.condo_location_id,
            };

            if (this.form.assignment_type === 'user') {
                payload.assigned_to = this.form.assigned_to;
            } else if (this.form.assignment_type === 'department') {
                payload.department_id = this.form.department_id;
            }

            try {
                const response = await axios.post('/api/dashboard/admin/ticket-concerns', payload);
                this.successMessage = response.data.message || this.$i18n.t('Ticket concern created successfully!');
                this.$notify({
                    title: this.$i18n.t('Success').toString(),
                    text: this.successMessage,
                    type: 'success'
                });
                // Optionally reset form or redirect
                // this.resetForm(); 
                this.$router.push('/dashboard/admin/ticket-concerns');
            } catch (error) {
                console.error("Error saving ticket concern:", error);
                if (error.response && error.response.data) {
                    if (error.response.data.errors) {
                        // Flatten laravel errors
                        const errors = error.response.data.errors;
                        for (const key in errors) {
                            if (Object.hasOwnProperty.call(errors, key)) {
                                this.formErrors[key] = errors[key][0]; // Take the first error message for each field
                            }
                        }
                        this.errorMessage = this.$i18n.t('Please correct the validation errors.');
                    } else {
                        this.errorMessage = error.response.data.message || this.$i18n.t('An unexpected error occurred while saving.');
                    }
                } else {
                    this.errorMessage = this.$i18n.t('An network error occurred. Please try again.');
                }
                 this.$notify({
                    title: this.$i18n.t('Error').toString(),
                    text: this.errorMessage,
                    type: 'error'
                });
            } finally {
                this.saving = false;
            }
        },
        resetForm() {
            this.form = {
                name: '',
                status: true,
                condo_location_id: null,
                assignment_type: null,
                assigned_to: null,
                department_id: null,
            };
            this.formErrors = {};
            this.successMessage = '';
            this.errorMessage = '';
        }
    }
};
</script>

<style scoped>
/* Basic styling for select elements to match input fields */
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem; /* Make space for the arrow */
}
</style>
