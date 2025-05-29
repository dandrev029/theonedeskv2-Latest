<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <!-- Header Section -->
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-primary-700">{{ $t('Edit Ticket Concern') }}</h1>
                    <p class="mt-2 text-sm text-secondary-600">{{ $t('Update the details of this concern category') }}</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-2">
                    <button
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                        type="button"
                        @click="deleteTicketConcernModal = true"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        {{ $t('Delete Concern') }}
                    </button>
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

                <form @submit.prevent="updateTicketConcern" class="p-8 space-y-8" v-if="!loadingInitialData">
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
                                <option :value="null">{{ loadingCondoLocations ? $t('Loading locations...') : $t('Select a condominium location (optional)') }}</option>
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
                                id="submit-ticket-concern-update"
                                type="submit"
                                :disabled="saving"
                                class="inline-flex items-center justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 active:bg-primary-800 transition duration-150 ease-in-out"
                            >
                                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ saving ? $t('Updating...') : $t('Update Concern') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Associated Tickets Section -->
        <div v-if="!loadingInitialData && form.id" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                {{ $t('Associated Tickets') }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                {{ $t('Tickets that have been created using this concern category') }}
                            </p>
                        </div>
                        <div v-if="associatedTickets.pagination.total > 0" class="text-sm text-gray-500 dark:text-gray-400">
                            {{ associatedTickets.pagination.total }} {{ $t('tickets found') }}
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Loading State -->
                    <div v-if="loadingTickets" class="space-y-4">
                        <ticket-skeleton v-for="n in 3" :key="`skeleton-${n}`" :is-grid="false" />
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="associatedTickets.data.length === 0" class="text-center py-12">
                        <svg-vue class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" icon="font-awesome/ticket-alt-regular"></svg-vue>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ $t('No tickets found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $t('No tickets have been created using this concern category yet.') }}
                        </p>
                    </div>

                    <!-- Tickets List -->
                    <div v-else>
                        <!-- Sort Controls -->
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Sort by') }}:</label>
                                <select
                                    v-model="ticketSort.column"
                                    @change="loadAssociatedTickets()"
                                    class="form-select text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                >
                                    <option value="created_at">{{ $t('Created Date') }}</option>
                                    <option value="updated_at">{{ $t('Last Updated') }}</option>
                                    <option value="subject">{{ $t('Subject') }}</option>
                                    <option value="status_id">{{ $t('Status') }}</option>
                                    <option value="priority_id">{{ $t('Priority') }}</option>
                                </select>
                                <select
                                    v-model="ticketSort.order"
                                    @change="loadAssociatedTickets()"
                                    class="form-select text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                >
                                    <option value="desc">{{ $t('Descending') }}</option>
                                    <option value="asc">{{ $t('Ascending') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden sm:block">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                {{ $t('Ticket') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                {{ $t('Status') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                {{ $t('Priority') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                {{ $t('Assigned Agent') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                {{ $t('Created') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr
                                            v-for="ticket in associatedTickets.data"
                                            :key="ticket.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-150"
                                            @click="navigateToTicket(ticket)"
                                        >
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <img
                                                            :alt="$t('Avatar')"
                                                            :src="ticket.user.avatar !== 'gravatar' ? ticket.user.avatar : ticket.user.gravatar"
                                                            class="h-8 w-8 rounded-full"
                                                        >
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ ticket.subject }}
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $t('by') }} {{ ticket.user.name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                                    :style="{
                                                        backgroundColor: ticket.status.color + '20',
                                                        color: ticket.status.color,
                                                        borderColor: ticket.status.color + '40'
                                                    }"
                                                >
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full mr-1.5"
                                                        :style="{ backgroundColor: ticket.status.color }"
                                                    ></span>
                                                    {{ ticket.status.name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    v-if="ticket.priority"
                                                    :class="[
                                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                        ticket.priority.value === 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                                        ticket.priority.value === 2 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                        ticket.priority.value === 3 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' :
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                    ]"
                                                >
                                                    {{ ticket.priority.name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ ticket.agent ? ticket.agent.name : $t('Unassigned') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ ticket.created_at | momentFormatDateTimeAgo }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="sm:hidden space-y-4">
                            <div
                                v-for="ticket in associatedTickets.data"
                                :key="`mobile-${ticket.id}`"
                                class="ticket-card cursor-pointer relative overflow-hidden"
                                @click="navigateToTicket(ticket)"
                            >
                                <!-- Status Indicator Bar -->
                                <div
                                    class="absolute top-0 left-0 h-1 w-full"
                                    :style="{ backgroundColor: ticket.status.color }"
                                ></div>

                                <div class="ticket-card-header pt-5">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center space-x-2">
                                            <img
                                                :alt="$t('Avatar')"
                                                :src="ticket.user.avatar !== 'gravatar' ? ticket.user.avatar : ticket.user.gravatar"
                                                class="h-8 w-8 rounded-full"
                                            >
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ ticket.user.name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ ticket.user.email }}</div>
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
                                    <h3 class="text-lg font-medium mb-2 text-gray-900 dark:text-white">{{ ticket.subject }}</h3>
                                    <div class="text-sm mb-2 text-gray-500 dark:text-gray-400">
                                        <div v-if="ticket.department" class="mb-1">
                                            <span class="font-medium">{{ $t('Department') }}:</span> {{ ticket.department.name }}
                                        </div>
                                        <div v-if="ticket.agent" class="mb-1">
                                            <span class="font-medium">{{ $t('Agent') }}:</span> {{ ticket.agent.name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="ticket-card-footer">
                                    <div class="flex justify-between items-center">
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ ticket.created_at | momentFormatDateTimeAgo }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="associatedTickets.pagination.last_page > 1" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $t('Showing') }} {{ associatedTickets.pagination.from }} {{ $t('to') }} {{ associatedTickets.pagination.to }}
                                {{ $t('of') }} {{ associatedTickets.pagination.total }} {{ $t('results') }}
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="loadAssociatedTickets(ticketCurrentPage - 1)"
                                    :disabled="ticketCurrentPage <= 1"
                                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    {{ $t('Previous') }}
                                </button>
                                <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $t('Page') }} {{ ticketCurrentPage }} {{ $t('of') }} {{ associatedTickets.pagination.last_page }}
                                </span>
                                <button
                                    @click="loadAssociatedTickets(ticketCurrentPage + 1)"
                                    :disabled="ticketCurrentPage >= associatedTickets.pagination.last_page"
                                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    {{ $t('Next') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteTicketConcernModal" class="fixed z-20 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg-vue class="h-6 w-6 text-red-600" icon="font-awesome/exclamation-circle-solid"></svg-vue>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $t('Delete Ticket Concern') }}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ $t('Are you sure you want to delete this ticket concern?') }}
                                        {{ $t('All data will be permanently removed') }}.
                                        {{ $t('This action cannot be undone') }}.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="confirmDeleteTicketConcern"
                            :disabled="deleting"
                        >
                            <svg v-if="deleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ deleting ? $t('Deleting...') : $t('Delete') }}
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="deleteTicketConcernModal = false"
                            :disabled="deleting"
                        >
                            {{ $t('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import axios from 'axios';
import moment from 'moment'; // Import moment for filters

export default {
    name: "EditTicketConcern",
    metaInfo() {
        return {
            title: this.$i18n.t('Edit Ticket Concern')
        }
    },
    data() {
        return {
            loadingInitialData: true,
            saving: false,
            deleting: false,
            deleteTicketConcernModal: false,
            loadingUsers: false,
            loadingDepartments: false,
            loadingCondoLocations: false,
            loadingTickets: false,
            
            form: {
                id: null,
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

            // Associated tickets data
            associatedTickets: {
                data: [],
                pagination: {
                    current_page: 1,
                    per_page: 15,
                    total: 0,
                    last_page: 1,
                    from: null,
                    to: null
                }
            },
            ticketCurrentPage: 1,
            ticketSort: {
                column: 'created_at',
                order: 'desc'
            }
        };
    },
    watch: {
        'form.assignment_type'(newType, oldType) {
            // Reset specific assignment IDs when type changes to prevent sending both
            // Only reset if it's a genuine type change, not during initial load
            if (oldType !== null) { // Check if oldType is not null to avoid clearing on initial population
                 if (newType === 'user') {
                    this.form.department_id = null;
                } else if (newType === 'department') {
                    this.form.assigned_to = null;
                }
            }
            // Clear related errors
            this.formErrors.assigned_to = null;
            this.formErrors.department_id = null;
            this.formErrors.assignment_type = null;
        }
    },
    mounted() {
        this.initializePage();
    },
    methods: {
        async initializePage() {
            this.loadingInitialData = true;
            this.successMessage = '';
            this.errorMessage = '';
            try {
                const concernId = this.$route.params.id;
                await this.fetchTicketConcern(concernId); // Fetch specific concern
                // Fetch dropdown data in parallel
                await Promise.all([
                    this.fetchUsers(),
                    this.fetchDepartments(),
                    this.fetchCondoLocations()
                ]);
                this.populateFormWithFetchedData(); // Populate form based on fetched concern
                if (this.form.id) {
                    this.loadAssociatedTickets(); // Load associated tickets
                }
            } catch (error) {
                this.errorMessage = this.$i18n.t('Error loading page data. Please try again.');
                console.error("Error initializing page:", error);
            } finally {
                this.loadingInitialData = false;
            }
        },
        async fetchTicketConcern(id) {
            try {
                const response = await axios.get(`/api/dashboard/admin/ticket-concerns/${id}`);
                const concernData = response.data.data;
                this.form.id = concernData.id;
                this.form.name = concernData.name;
                this.form.status = concernData.status;
                this.form.condo_location_id = concernData.condo_location_id;
                
                // Determine assignment_type and set assigned_to or department_id
                if (concernData.assigned_to) {
                    this.form.assignment_type = 'user';
                    this.form.assigned_to = concernData.assigned_to;
                    this.form.department_id = null;
                } else if (concernData.department_id) {
                    this.form.assignment_type = 'department';
                    this.form.department_id = concernData.department_id;
                    this.form.assigned_to = null;
                } else {
                    this.form.assignment_type = null; // Or a default if applicable
                    this.form.assigned_to = null;
                    this.form.department_id = null;
                }
            } catch (error) {
                console.error("Error fetching ticket concern:", error);
                this.$notify({
                    title: this.$i18n.t('Error').toString(),
                    text: error.response?.data?.message || this.$i18n.t('Could not load ticket concern details.'),
                    type: 'error'
                });
                this.$router.push('/dashboard/admin/ticket-concerns');
                throw error; // Re-throw to be caught by initializePage
            }
        },
        populateFormWithFetchedData() {
            // This method is to ensure reactivity after all dropdown data is fetched
            // The actual population happens in fetchTicketConcern and then dropdowns are selected
            // If users/departments are already loaded, we can try to pre-select here,
            // but it's safer to rely on v-model binding after data is available.
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
                } else if (response.data && Array.isArray(response.data)) { // Handle direct array response
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
            return isValid;
        },
        async updateTicketConcern() {
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
                payload.assigned_to_user_id = this.form.assigned_to; // Ensure correct key for backend
                payload.department_id = null; // Explicitly nullify other type
            } else if (this.form.assignment_type === 'department') {
                payload.department_id = this.form.department_id;
                payload.assigned_to_user_id = null; // Explicitly nullify other type
            }

            try {
                const response = await axios.put(`/api/dashboard/admin/ticket-concerns/${this.form.id}`, payload);
                this.successMessage = response.data.message || this.$i18n.t('Ticket concern updated successfully!');
                this.$notify({
                    title: this.$i18n.t('Success').toString(),
                    text: this.successMessage,
                    type: 'success'
                });
                this.$router.push('/dashboard/admin/ticket-concerns');
            } catch (error) {
                console.error("Error updating ticket concern:", error);
                if (error.response && error.response.data) {
                    if (error.response.data.errors) {
                        const errors = error.response.data.errors;
                        for (const key in errors) {
                            if (Object.hasOwnProperty.call(errors, key)) {
                                // Map backend keys (like assigned_to_user_id) to form keys if needed
                                const formKey = key === 'assigned_to_user_id' ? 'assigned_to' : key;
                                this.formErrors[formKey] = errors[key][0];
                            }
                        }
                        this.errorMessage = this.$i18n.t('Please correct the validation errors.');
                    } else {
                        this.errorMessage = error.response.data.message || this.$i18n.t('An unexpected error occurred while saving.');
                    }
                } else {
                    this.errorMessage = this.$i18n.t('A network error occurred. Please try again.');
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
        confirmDeleteTicketConcern() {
            this.deleting = true;
            axios.delete(`/api/dashboard/admin/ticket-concerns/${this.form.id}`)
                .then(() => {
                    this.$notify({
                        title: this.$i18n.t('Success').toString(),
                        text: this.$i18n.t('Ticket concern deleted successfully').toString(),
                        type: 'success'
                    });
                    this.$router.push('/dashboard/admin/ticket-concerns');
                })
                .catch(error => {
                    this.errorMessage = error.response?.data?.message || this.$i18n.t('Could not delete ticket concern.');
                    this.$notify({ title: this.$i18n.t('Error').toString(), text: this.errorMessage, type: 'error' });
                })
                .finally(() => {
                    this.deleting = false;
                    this.deleteTicketConcernModal = false;
                });
        },
        loadAssociatedTickets(page = 1) {
            if (!this.form.id) return;
            this.loadingTickets = true;
            this.ticketCurrentPage = page;
            const params = {
                page: page,
                perPage: 15, // Or your desired perPage
                sort: JSON.stringify(this.ticketSort)
            };
            axios.get(`/api/dashboard/admin/ticket-concerns/${this.form.id}/tickets`, { params })
                .then(response => {
                    this.associatedTickets.data = response.data.data || [];
                    this.associatedTickets.pagination = response.data.pagination || { total: 0, last_page: 1, from: 0, to: 0 };
                })
                .catch(error => {
                    console.error('Error loading associated tickets:', error);
                    this.$notify({
                        title: this.$i18n.t('Error').toString(),
                        text: this.$i18n.t('Could not load associated tickets.'),
                        type: 'error'
                    });
                })
                .finally(() => {
                    this.loadingTickets = false;
                });
        },
        navigateToTicket(ticket) {
            this.$router.push(`/dashboard/tickets/${ticket.uuid}/manage`);
        }
    },
    filters: {
        momentFormatDateTimeAgo: function (value) {
            return moment(value).locale(window.app.app_date_locale).fromNow();
        },
        momentFormatDateTime: function (value) {
            return moment.utc(value).tz(window.app.app_timezone).locale(window.app.app_date_locale).format(window.app.app_date_format + ' h:mm A');
        },
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

/* Ticket Card Styles from old edit.vue, if needed for associated tickets */
.ticket-card {
    background-color: white;
    border: 1px solid #e5e7eb; /* theme('colors.gray.200') */
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s ease-in-out;
}
.dark .ticket-card {
    background-color: #1f2937; /* theme('colors.gray.800') */
    border-color: #374151; /* theme('colors.gray.700') */
}
.ticket-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.ticket-card-header { margin-bottom: 0.75rem; }
.ticket-card-body { margin-bottom: 0.75rem; }
.ticket-card-footer { padding-top: 0.75rem; border-top: 1px solid #f3f4f6; /* theme('colors.gray.100') */ }
.dark .ticket-card-footer { border-top-color: #374151; /* theme('colors.gray.700') */ }

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    border: 1px solid; /* Color applied dynamically */
}
.priority-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.priority-badge-low { background-color: #dcfce7; color: #166534; }
.dark .priority-badge-low { background-color: #10B98133; color: #A7F3D0; } /* Adjusted for dark mode */
.priority-badge-medium { background-color: #fef3c7; color: #92400e; }
.dark .priority-badge-medium { background-color: #F59E0B33; color: #FDE68A; }
.priority-badge-high { background-color: #fed7aa; color: #c2410c; }
.dark .priority-badge-high { background-color: #F9731633; color: #FED7AA; }
.priority-badge-urgent { background-color: #fecaca; color: #dc2626; }
.dark .priority-badge-urgent { background-color: #EF444433; color: #FECACA; }
</style>
