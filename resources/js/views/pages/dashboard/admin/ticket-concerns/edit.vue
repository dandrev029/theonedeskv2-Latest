<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('Edit Ticket Concern') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button
                        class="btn btn-red shadow-sm rounded-md mr-2"
                        type="button"
                        @click="deleteTicketConcernModal = true"
                    >
                        {{ $t('Delete Ticket Concern') }}
                    </button>
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
                <form v-if="!loading" @submit.prevent="updateTicketConcern" class="p-6">
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
                        <p v-if="formErrors.name" class="mt-1 text-xs text-red-500">{{ formErrors.name }}</p>
                        <p v-else class="mt-1 text-xs text-gray-500">{{ $t('A descriptive name for this ticket concern') }}</p>
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
                            <p v-if="formErrors.status" class="mt-1 text-xs text-red-500">{{ formErrors.status }}</p>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium leading-5 text-gray-700" for="department_id">
                            {{ $t('Department') }}
                        </label>
                        <div class="mt-1 relative">
                            <div :class="{'opacity-50 pointer-events-none': loadingDepartments}" class="rounded-md shadow-sm">
                                <input-select-scrollable
                                    id="department_id"
                                    v-model="ticketConcern.department_id"
                                    :options="departments"
                                    option-label="name"
                                    :searchable="true"
                                    :placeholder="loadingDepartments ? $t('Loading departments...') : $t('Select a department for this concern')"
                                    :clear-on-select="false"
                                    :show-labels="false"
                                    class="dropdown-improved"
                                >
                                    <template v-slot:noResult>
                                        <div class="py-2 px-4 text-gray-500">{{ $t('No departments found') }}</div>
                                    </template>
                                    <template v-slot:noOptions>
                                        <div class="py-2 px-4 text-gray-500">{{ $t('No departments available') }}</div>
                                    </template>
                                </input-select-scrollable>
                            </div>
                            <div v-if="loadingDepartments" class="absolute right-0 top-0 bottom-0 flex items-center pr-3">
                                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <p v-if="formErrors.department_id" class="mt-1 text-xs text-red-500">{{ formErrors.department_id }}</p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            {{ $t('Select the department that this concern belongs to.') }}
                        </p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium leading-5 text-gray-700" for="assigned_to">
                            {{ $t('Assigned To') }}
                        </label>
                        <div class="mt-1 relative">
                            <div :class="{'opacity-50 pointer-events-none': loadingUsers || !ticketConcern.department_id}" class="rounded-md shadow-sm">
                                <input-select-scrollable
                                    id="assigned_to"
                                    v-model="ticketConcern.assigned_to"
                                    :options="dashboardUsers"
                                    option-label="name"
                                    :searchable="true"
                                    :placeholder="loadingUsers ? $t('Loading users...') : (!ticketConcern.department_id ? $t('Select a department first') : $t('Select a user to handle this concern'))"
                                    :clear-on-select="false"
                                    :show-labels="false"
                                    :disabled="!ticketConcern.department_id || loadingUsers"
                                    class="dropdown-improved"
                                >
                                    <template v-slot:option="{ option }">
                                        <div class="flex items-center">
                                            <img
                                                :src="option.avatar || option.gravatar"
                                                :alt="option.name"
                                                class="h-6 w-6 rounded-full mr-2"
                                            >
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ option.name }}</div>
                                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ option.email }}</div>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-slot:selectedOption="{ option }">
                                        <div v-if="option" class="flex items-center">
                                            <img
                                                :src="option.avatar || option.gravatar"
                                                :alt="option.name"
                                                class="h-5 w-5 rounded-full mr-2"
                                            >
                                            <span class="truncate">{{ option.name }}</span>
                                        </div>
                                        <span v-else class="text-gray-500">{{ $t('Select a user') }}</span>
                                    </template>
                                    <template v-slot:noResult>
                                        <div class="py-2 px-4 text-gray-500">{{ $t('No users found') }}</div>
                                    </template>
                                    <template v-slot:noOptions>
                                        <div class="py-2 px-4 text-gray-500">{{ $t('No users available') }}</div>
                                    </template>
                                </input-select-scrollable>
                            </div>
                            <div v-if="loadingUsers" class="absolute right-0 top-0 bottom-0 flex items-center pr-3">
                                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <p v-if="formErrors.assigned_to" class="mt-1 text-xs text-red-500">{{ formErrors.assigned_to }}</p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            {{ $t('Select a user with dashboard access who will handle tickets with this concern category.') }}
                        </p>
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
                                    {{ saving ? $t('Updating...') : $t('Update') }}
                                </button>
                            </span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Associated Tickets Section -->
            <div v-if="!loading && ticketConcern.id" class="my-6 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
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
                        <ticket-skeleton v-for="n in 3" :key="n" :is-grid="false" />
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
                                    @change="loadAssociatedTickets"
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
                                    @change="loadAssociatedTickets"
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
                                :key="ticket.id"
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
        <div v-if="deleteTicketConcernModal" class="fixed z-10 inset-0 overflow-y-auto">
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
                            @click="deleteTicketConcern"
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
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
export default {
    name: "ticket-concerns-edit",
    metaInfo() {
        return {
            title: this.$i18n.t('Edit Ticket Concern')
        }
    },
    data() {
        return {
            loading: true,
            saving: false,
            deleting: false,
            deleteTicketConcernModal: false,
            loadingUsers: false,
            loadingDepartments: false,
            loadingTickets: false,
            dashboardUsers: [],
            departments: [],
            ticketConcern: {
                id: null,
                name: '',
                status: true,
                assigned_to: null,
                department_id: null
            },
            formErrors: {},
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
        }
    },
    mounted() {
        this.initializeEditForm();
    },
    watch: {
        'ticketConcern.department_id': function (newDepartmentId, oldDepartmentId) {
            if (newDepartmentId !== oldDepartmentId) {
                this.ticketConcern.assigned_to = null; // Clear assigned user
                this.dashboardUsers = []; // Clear current user list
                if (newDepartmentId) {
                    this.getDashboardUsers(newDepartmentId);
                }
            }
        }
    },
    methods: {
        initializeEditForm() {
            this.loading = true;
            this.getTicketConcern().then(() => {
                // Once ticket concern is loaded, then load departments and users
                // This ensures department_id is available for fetching relevant users if already set
                const promises = [this.getDepartments()];
                if (this.ticketConcern.department_id) {
                    promises.push(this.getDashboardUsers(this.ticketConcern.department_id));
                } else {
                    // If no department is initially set, we might not load any users or load all dashboard users
                    // For now, let's not load users until a department is selected or if one was pre-selected.
                    this.dashboardUsers = [];
                }
                return Promise.all(promises);
            }).then(() => {
                // Load associated tickets after the ticket concern is loaded
                if (this.ticketConcern.id) {
                    this.loadAssociatedTickets();
                }
            }).finally(() => {
                this.loading = false;
            });
        },
        getDashboardUsers(departmentId = null) {
            const self = this;
            self.loadingUsers = true;
            let apiUrl = 'api/dashboard/admin/ticket-concerns/users/dashboard';
            if (departmentId) {
                apiUrl += `?department_id=${departmentId}`;
            }

            return axios.get(apiUrl)
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
                    console.error('Error fetching dashboard users:', error);
                    self.dashboardUsers = [];
                })
                .finally(function () {
                    self.loadingUsers = false;
                });
        },
        getDepartments() {
            const self = this;
            self.loadingDepartments = true;
            // Use the new endpoint for user-accessible departments
            return axios.get('/api/dashboard/admin/ticket-concerns/user-accessible-departments')
                .then(function (response) {
                    if (response.data && response.data.data) {
                        self.departments = response.data.data;
                        // If the current ticketConcern.department_id is not in the new list, clear it.
                        if (self.ticketConcern.department_id && !self.departments.find(d => d.id === self.ticketConcern.department_id)) {
                            self.ticketConcern.department_id = null;
                            self.ticketConcern.assigned_to = null; // Also clear assigned user
                            self.dashboardUsers = [];
                        }
                    } else {
                        self.departments = [];
                         self.$notify({
                            title: self.$i18n.t('Warning').toString(),
                            text: self.$i18n.t('No accessible departments found or could not load them.').toString(),
                            type: 'warning'
                        });
                    }
                })
                .catch(function (error) {
                    console.error('Error fetching departments:', error);
                    self.departments = [];
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response ? error.response.data.error || error.response.data.message || self.$i18n.t('Could not load departments') : self.$i18n.t('Could not load departments'),
                        type: 'error'
                    });
                })
                .finally(function () {
                    self.loadingDepartments = false;
                });
        },
        getTicketConcern() {
            const self = this;

            return axios.get('api/dashboard/admin/ticket-concerns/' + this.$route.params.id)
                .then(function (response) {
                    if (response.data && response.data.data) {
                        self.ticketConcern = response.data.data;
                    } else {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: self.$i18n.t('Could not load ticket concern data'),
                            type: 'error'
                        });
                        self.$router.push('/dashboard/admin/ticket-concerns');
                    }
                })
                .catch(function (error) {
                    console.error('Error fetching ticket concern:', error);
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response ? error.response.data.message || self.$i18n.t('Could not load ticket concern') : self.$i18n.t('Could not load ticket concern'),
                        type: 'error'
                    });
                    self.$router.push('/dashboard/admin/ticket-concerns');
                });
        },
        updateTicketConcern() {
            const self = this;
            self.saving = true;
            self.formErrors = {};

            // Basic form validation
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

            const ladda = typeof Ladda !== 'undefined' ? Ladda.create(document.querySelector('#submit-ticket-concern')) : null;
            if (ladda) ladda.start();

            axios.put('api/dashboard/admin/ticket-concerns/' + this.ticketConcern.id, this.ticketConcern)
                .then(function (response) {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: response.data.message || self.$i18n.t('Ticket concern updated successfully'),
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
        },
        loadAssociatedTickets(page = 1) {
            const self = this;
            if (!self.ticketConcern.id) return;

            self.loadingTickets = true;
            self.ticketCurrentPage = page;

            const params = {
                page: page,
                perPage: 15,
                sort: JSON.stringify(self.ticketSort)
            };

            axios.get(`api/dashboard/admin/ticket-concerns/${self.ticketConcern.id}/tickets`, { params })
                .then(function (response) {
                    if (response.data && response.data.data) {
                        self.associatedTickets.data = response.data.data;
                        self.associatedTickets.pagination = response.data.pagination;
                    } else {
                        self.associatedTickets.data = [];
                        self.associatedTickets.pagination = {
                            current_page: 1,
                            per_page: 15,
                            total: 0,
                            last_page: 1,
                            from: null,
                            to: null
                        };
                    }
                })
                .catch(function (error) {
                    console.error('Error loading associated tickets:', error);
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response ? error.response.data.message || self.$i18n.t('Could not load associated tickets') : self.$i18n.t('Could not load associated tickets'),
                        type: 'error'
                    });
                    self.associatedTickets.data = [];
                    self.associatedTickets.pagination = {
                        current_page: 1,
                        per_page: 15,
                        total: 0,
                        last_page: 1,
                        from: null,
                        to: null
                    };
                })
                .finally(function () {
                    self.loadingTickets = false;
                });
        },
        navigateToTicket(ticket) {
            // Navigate to the ticket management page
            this.$router.push(`/dashboard/tickets/${ticket.uuid}/manage`);
        },
        deleteTicketConcern() {
            const self = this;
            self.deleting = true;

            axios.delete('api/dashboard/admin/ticket-concerns/' + this.ticketConcern.id)
                .then(function () {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: self.$i18n.t('Ticket concern deleted successfully').toString(),
                        type: 'success'
                    });
                    self.$router.push('/dashboard/admin/ticket-concerns');
                })
                .catch(function (error) {
                    self.deleteTicketConcernModal = false;
                    if (error.response && error.response.data && error.response.data.message) {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: error.response.data.message,
                            type: 'error'
                        });
                    } else {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: self.$i18n.t('An unexpected error occurred while deleting'),
                            type: 'error'
                        });
                    }
                })
                .finally(function() {
                    self.deleting = false;
                });
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

<style scoped>
/* Ticket Card Styles */
.ticket-card {
    background-color: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s ease-in-out;
}

.ticket-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.ticket-card-header {
    margin-bottom: 0.75rem;
}

.ticket-card-body {
    margin-bottom: 0.75rem;
}

.ticket-card-footer {
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    border: 1px solid;
}

.priority-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.priority-badge-low {
    background-color: #dcfce7;
    color: #166534;
}

.priority-badge-medium {
    background-color: #fef3c7;
    color: #92400e;
}

.priority-badge-high {
    background-color: #fed7aa;
    color: #c2410c;
}

.priority-badge-urgent {
    background-color: #fecaca;
    color: #dc2626;
}

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
