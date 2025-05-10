<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <!-- Page Header with Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6">
                <div class="flex items-center mb-4 sm:mb-0">
                    <h1 class="text-2xl font-semibold" :class="textPrimary">
                        {{ $t('My tickets') }}
                    </h1>
                </div>
                <div>
                    <router-link
                        class="btn btn-primary shadow-sm rounded-md inline-flex items-center"
                        to="/tickets/new"
                    >
                        {{ $t('New ticket') }}
                    </router-link>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
                <loading :status="loading"/>
                <div class="px-5 py-4 border-b" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
                        <!-- Desktop Search and Filter UI -->
                        <div class="hidden sm:block">
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Search Input -->
                                <div class="relative flex-grow max-w-md">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-400'" icon="font-awesome.search-light"></svg-vue>
                                    </div>
                                    <input
                                        id="search-desktop"
                                        v-model.lazy="filters.search"
                                        :placeholder="$t('Search tickets...')"
                                        class="form-input block w-full rounded-lg pl-10 pr-3 py-2 text-sm shadow-sm transition ease-in-out duration-150 border-0 ring-1 ring-inset focus:ring-2 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-900', darkText: 'text-white'})"
                                        @change="getTickets"
                                    >
                                </div>

                                <!-- Status Filter -->
                                <div class="relative">
                                    <select
                                        id="status-desktop"
                                        v-model="filters.status"
                                        aria-label="Filter by status"
                                        class="form-select rounded-lg py-2 pl-3 pr-10 text-sm shadow-sm border-0 ring-1 ring-inset focus:ring-2 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-900', darkText: 'text-white'})"
                                        @change="getTickets"
                                    >
                                        <option :value="null">{{ $t('All requests') }}</option>
                                        <template v-for="status in statusList">
                                            <option :value="status.id">{{ status.name }}</option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Sort Controls -->
                                <div class="flex items-center">
                                    <label class="mr-2 text-sm font-medium" :class="textSecondary">{{ $t('Sort by:') }}</label>
                                    <select
                                        id="sortBy-desktop"
                                        v-model="sort.column"
                                        aria-label="Sort by"
                                        class="form-select rounded-lg py-2 pl-3 pr-10 text-sm shadow-sm border-0 ring-1 ring-inset focus:ring-2 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-900', darkText: 'text-white'})"
                                        @change="changeSort"
                                    >
                                        <option value="subject">{{ $t('Subject') }}</option>
                                        <option value="status_id">{{ $t('Status') }}</option>
                                        <option value="created_at">{{ $t('Created at') }}</option>
                                        <option value="updated_at">{{ $t('Updated at') }}</option>
                                    </select>

                                    <button
                                        class="ml-2 p-2 rounded-lg shadow-sm border-0 ring-1 ring-inset transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-50', darkHover: 'hover:bg-gray-600'})"
                                        type="button"
                                        @click="changeSort"
                                        :title="sort.order === 'asc' ? $t('Sort Descending') : $t('Sort Ascending')"
                                    >
                                        <svg-vue
                                            v-show="sort.order === 'asc'"
                                            class="h-5 w-5"
                                            :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'"
                                            icon="font-awesome.sort-amount-down-alt-regular"
                                        ></svg-vue>
                                        <svg-vue
                                            v-show="sort.order === 'desc'"
                                            class="h-5 w-5"
                                            :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'"
                                            icon="font-awesome.sort-amount-up-alt-regular"
                                        ></svg-vue>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Search and Filter UI -->
                        <div class="sm:hidden space-y-3">
                            <!-- Search Input -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-400'" icon="font-awesome.search-light"></svg-vue>
                                </div>
                                <input
                                    id="search-mobile"
                                    v-model.lazy="filters.search"
                                    :placeholder="$t('Search tickets...')"
                                    class="form-input block w-full rounded-lg pl-10 pr-3 py-2 text-sm shadow-sm transition ease-in-out duration-150 border-0 ring-1 ring-inset focus:ring-2 focus:ring-primary-500"
                                    :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-900', darkText: 'text-white'})"
                                    @change="getTickets"
                                >
                            </div>

                            <!-- Filter and Sort Controls -->
                            <div class="flex space-x-2">
                                <!-- Status Filter -->
                                <div class="flex-1">
                                    <select
                                        id="status-mobile"
                                        v-model="filters.status"
                                        aria-label="Filter by status"
                                        class="form-select w-full rounded-lg py-2 pl-3 pr-10 text-sm shadow-sm border-0 ring-1 ring-inset focus:ring-2 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-900', darkText: 'text-white'})"
                                        @change="getTickets"
                                    >
                                        <option :value="null">{{ $t('All requests') }}</option>
                                        <template v-for="status in statusList">
                                            <option :value="status.id">{{ status.name }}</option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Sort By -->
                                <div class="flex-1">
                                    <select
                                        id="sortBy-mobile"
                                        v-model="sort.column"
                                        aria-label="Sort by"
                                        class="form-select w-full rounded-lg py-2 pl-3 pr-10 text-sm shadow-sm border-0 ring-1 ring-inset focus:ring-2 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-900', darkText: 'text-white'})"
                                        @change="changeSort"
                                    >
                                        <option value="subject">{{ $t('Subject') }}</option>
                                        <option value="status_id">{{ $t('Status') }}</option>
                                        <option value="created_at">{{ $t('Created at') }}</option>
                                        <option value="updated_at">{{ $t('Updated at') }}</option>
                                    </select>
                                </div>

                                <!-- Sort Order Button -->
                                <button
                                    class="p-2 rounded-lg shadow-sm border-0 ring-1 ring-inset transition ease-in-out duration-150"
                                    :class="getDarkModeClasses({lightBg: 'bg-white ring-gray-300', darkBg: 'bg-gray-700 ring-gray-600', lightText: 'text-gray-700', darkText: 'text-gray-300', lightHover: 'hover:bg-gray-50', darkHover: 'hover:bg-gray-600'})"
                                    type="button"
                                    @click="changeSort"
                                    :title="sort.order === 'asc' ? $t('Sort Descending') : $t('Sort Ascending')"
                                >
                                    <svg-vue
                                        v-show="sort.order === 'asc'"
                                        class="h-5 w-5"
                                        :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'"
                                        icon="font-awesome.sort-amount-down-alt-regular"
                                    ></svg-vue>
                                    <svg-vue
                                        v-show="sort.order === 'desc'"
                                        class="h-5 w-5"
                                        :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'"
                                        icon="font-awesome.sort-amount-up-alt-regular"
                                    ></svg-vue>
                                </button>
                            </div>
                        </div>
                    </div>
                    <template v-if="ticketList.length > 0">
                        <!-- Desktop Table View -->
                        <div class="hidden sm:block px-5">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'divide-gray-200', darkBorder: 'divide-gray-700'})">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="textTertiary">
                                                {{ $t('Subject') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="textTertiary">
                                                {{ $t('Created at') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="textTertiary">
                                                {{ $t('Updated at') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="textTertiary">
                                                {{ $t('Status') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800'})">
                                        <template v-for="(ticket, index) in ticketList">
                                            <router-link
                                                :key="ticket.id"
                                                :to="'/tickets/' + ticket.uuid"
                                                :class="[
                                                    index % 2 === 0 ? getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800'}) : getDarkModeClasses({lightBg: 'bg-gray-50', darkBg: 'bg-gray-750'}),
                                                    getDarkModeClasses({lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'}),
                                                    'cursor-pointer transition-colors duration-150'
                                                ]"
                                                tag="tr"
                                            >
                                                <td class="px-6 py-4 max-w-0 w-full">
                                                    <div class="flex items-center">
                                                        <div class="w-full truncate text-sm font-medium" :class="textPrimary">
                                                            {{ ticket.subject }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm" :class="textSecondary">
                                                        {{ ticket.created_at | momentFormatDate }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm" :class="textSecondary">
                                                        {{ ticket.updated_at | momentFormatDateTimeAgo }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                                          :style="{
                                                              backgroundColor: ticket.status.color + '20',
                                                              color: ticket.status.color,
                                                              borderColor: ticket.status.color + '40',
                                                          }">
                                                        <span class="w-2 h-2 mr-1.5 rounded-full" :style="{ backgroundColor: ticket.status.color }"></span>
                                                        {{ ticket.status.name }}
                                                    </span>
                                                </td>
                                            </router-link>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="sm:hidden">
                            <div class="space-y-4 px-4 py-4">
                                <div
                                    v-for="ticket in ticketList"
                                    :key="ticket.id"
                                    class="ticket-card cursor-pointer relative overflow-hidden"
                                    @click="$router.push('/tickets/' + ticket.uuid)"
                                >
                                    <!-- Status Indicator Bar -->
                                    <div
                                        class="absolute top-0 left-0 h-1 w-full"
                                        :style="{ backgroundColor: ticket.status.color }"
                                    ></div>

                                    <div class="ticket-card-header pt-5">
                                        <div class="flex justify-between items-start">
                                            <div class="w-full text-sm font-medium leading-5" :class="textPrimary">
                                                {{ ticket.subject }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ticket-card-body">
                                        <div class="flex flex-col space-y-2">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center text-xs" :class="textSecondary">
                                                    <svg-vue class="h-3.5 w-3.5 mr-1.5" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-600'" icon="font-awesome.calendar-alt-light"></svg-vue>
                                                    {{ ticket.created_at | momentFormatDate }}
                                                </div>
                                                <div class="flex items-center text-xs" :class="textSecondary">
                                                    <svg-vue class="h-3.5 w-3.5 mr-1.5" :class="$store.state.darkMode ? 'text-gray-400' : 'text-gray-600'" icon="font-awesome.clock-light"></svg-vue>
                                                    {{ ticket.updated_at | momentFormatDateTimeAgo }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ticket-card-footer">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                              :style="{
                                                  backgroundColor: ticket.status.color + '20',
                                                  color: ticket.status.color,
                                                  borderColor: ticket.status.color + '40',
                                              }">
                                            <span class="w-2 h-2 mr-1.5 rounded-full" :style="{ backgroundColor: ticket.status.color }"></span>
                                            {{ ticket.status.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <!-- Loading Skeletons -->
                    <template v-else-if="loading">
                        <!-- Desktop Loading Skeleton -->
                        <div class="hidden sm:block px-5 py-4">
                            <div class="overflow-x-auto">
                                <div class="animate-pulse">
                                    <!-- Table Header Skeleton -->
                                    <div :class="{'border-gray-200': !$store.state.darkMode, 'border-gray-700': $store.state.darkMode}" class="flex border-b pb-3">
                                        <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="w-1/2 h-5 rounded mr-4"></div>
                                        <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="w-1/6 h-5 rounded mr-4"></div>
                                        <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="w-1/6 h-5 rounded mr-4"></div>
                                        <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="w-1/6 h-5 rounded"></div>
                                    </div>

                                    <!-- Table Rows Skeleton -->
                                    <div class="space-y-4 mt-4">
                                        <div v-for="i in 5" :key="i" :class="{'border-gray-100': !$store.state.darkMode, 'border-gray-700': $store.state.darkMode}" class="flex py-4 border-b">
                                            <div class="w-1/2 pr-4">
                                                <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-5 rounded w-3/4 mb-2"></div>
                                            </div>
                                            <div class="w-1/6 pr-4">
                                                <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-5 rounded"></div>
                                            </div>
                                            <div class="w-1/6 pr-4">
                                                <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-5 rounded"></div>
                                            </div>
                                            <div class="w-1/6">
                                                <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-5 rounded-full w-20"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Loading Skeleton -->
                        <div class="sm:hidden">
                            <div class="space-y-4 px-4 py-4">
                                <div v-for="i in 5" :key="i" class="animate-pulse">
                                    <div class="ticket-card relative overflow-hidden">
                                        <!-- Status Bar Skeleton -->
                                        <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="absolute top-0 left-0 h-1 w-full"></div>

                                        <div class="ticket-card-header pt-5">
                                            <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-5 rounded w-3/4"></div>
                                        </div>

                                        <div class="ticket-card-body">
                                            <div class="flex justify-between items-center mb-2">
                                                <div class="flex items-center">
                                                    <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-4 w-4 rounded-full mr-2"></div>
                                                    <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-4 rounded w-20"></div>
                                                </div>
                                                <div class="flex items-center">
                                                    <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-4 w-4 rounded-full mr-2"></div>
                                                    <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-4 rounded w-16"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ticket-card-footer">
                                            <div :class="{'bg-gray-200': !$store.state.darkMode, 'bg-gray-700': $store.state.darkMode}" class="h-6 rounded-full w-24"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- No Records Found -->
                    <template v-else-if="!loading && ticketList.length === 0">
                        <div class="flex flex-col items-center justify-center py-16">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-full p-6 mb-6" :style="$store.state.darkMode ? {'background-color': 'rgba(55, 65, 81, 0.3)'} : {}">
                                <svg-vue class="h-24 w-24 text-gray-400 dark:text-gray-500" icon="undraw.task-list"></svg-vue>
                            </div>
                            <h3 class="text-xl font-semibold mb-2" :class="textPrimary">{{ $t('No tickets found') }}</h3>
                            <p class="text-center max-w-md mb-6" :class="textSecondary">
                                <template v-if="anyFilter">
                                    {{ $t('Try changing the filters, or rephrasing your search') }}.
                                </template>
                                <template v-else>
                                    {{ $t('You don\'t have any tickets yet. Create a new ticket to get started.') }}
                                </template>
                            </p>
                            <template v-if="!anyFilter">
                                <router-link
                                    class="btn btn-primary shadow-sm rounded-md inline-flex items-center"
                                    to="/tickets/new"
                                >
                                    <svg-vue class="h-4 w-4 mr-2 text-white" icon="font-awesome.plus-solid"></svg-vue>
                                    {{ $t('New ticket') }}
                                </router-link>
                            </template>
                        </div>
                    </template>

                    <!-- Pagination -->
                    <div class="px-5 py-4 flex items-center justify-between border-t" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-gray-200', darkBorder: 'border-gray-700'})">
                        <!-- Desktop Pagination Info -->
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm" :class="textSecondary">
                                    {{ $t('Showing') }}
                                    <span class="font-medium" :class="textPrimary">{{ (pagination.perPage * pagination.currentPage) - pagination.perPage + 1 }}</span>
                                    {{ $t('to') }}
                                    <span class="font-medium" :class="textPrimary">{{ pagination.perPage * pagination.currentPage <= pagination.total ? pagination.perPage * pagination.currentPage : pagination.total }}</span>
                                    {{ $t('of') }}
                                    <span class="font-medium" :class="textPrimary">{{ pagination.total }}</span>
                                    {{ $t('results') }}
                                </p>
                            </div>

                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <button
                                        :class="[
                                            'relative inline-flex items-center rounded-l-md px-3 py-2 text-sm font-medium ring-1 ring-inset focus:z-20 focus:outline-offset-0 transition-colors duration-150',
                                            pagination.currentPage <= 1
                                                ? getDarkModeClasses({lightBg: 'bg-gray-50', darkBg: 'bg-gray-800', lightText: 'text-gray-400', darkText: 'text-gray-500', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700'}) + ' cursor-not-allowed'
                                                : getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightText: 'text-gray-500', darkText: 'text-gray-400', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700', lightHover: 'hover:bg-gray-50', darkHover: 'hover:bg-gray-700'})
                                        ]"
                                        :disabled="pagination.currentPage <= 1"
                                        type="button"
                                        @click="changePage(pagination.currentPage - 1)"
                                    >
                                        <span class="sr-only">{{ $t('Previous') }}</span>
                                        <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.chevron-left-solid"></svg-vue>
                                    </button>

                                    <!-- Current Page Display -->
                                    <span
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset focus:outline-offset-0"
                                        :class="getDarkModeClasses({lightBg: 'bg-primary-50', darkBg: 'bg-primary-900/20', lightText: 'text-primary-600', darkText: 'text-primary-400', lightRing: 'ring-primary-500/20', darkRing: 'ring-primary-500/30'})"
                                    >
                                        {{ pagination.currentPage }}
                                    </span>

                                    <button
                                        :class="[
                                            'relative inline-flex items-center rounded-r-md px-3 py-2 text-sm font-medium ring-1 ring-inset focus:z-20 focus:outline-offset-0 transition-colors duration-150',
                                            pagination.currentPage >= pagination.totalPages
                                                ? getDarkModeClasses({lightBg: 'bg-gray-50', darkBg: 'bg-gray-800', lightText: 'text-gray-400', darkText: 'text-gray-500', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700'}) + ' cursor-not-allowed'
                                                : getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightText: 'text-gray-500', darkText: 'text-gray-400', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700', lightHover: 'hover:bg-gray-50', darkHover: 'hover:bg-gray-700'})
                                        ]"
                                        :disabled="pagination.currentPage >= pagination.totalPages"
                                        type="button"
                                        @click="changePage(pagination.currentPage + 1)"
                                    >
                                        <span class="sr-only">{{ $t('Next') }}</span>
                                        <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.chevron-right-solid"></svg-vue>
                                    </button>
                                </nav>
                            </div>
                        </div>

                        <!-- Mobile Pagination -->
                        <div class="flex sm:hidden items-center justify-between w-full">
                            <div class="pagination-info">
                                <p class="text-sm" :class="textSecondary">
                                    <span class="font-medium" :class="textPrimary">{{ pagination.currentPage }}</span>
                                    {{ $t('of') }}
                                    <span class="font-medium" :class="textPrimary">{{ pagination.totalPages }}</span>
                                </p>
                            </div>

                            <div class="flex space-x-2">
                                <button
                                    :class="[
                                        'relative inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-1 ring-inset focus:z-20 focus:outline-offset-0 transition-colors duration-150',
                                        pagination.currentPage <= 1
                                            ? getDarkModeClasses({lightBg: 'bg-gray-50', darkBg: 'bg-gray-800', lightText: 'text-gray-400', darkText: 'text-gray-500', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700'}) + ' cursor-not-allowed'
                                            : getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightText: 'text-gray-500', darkText: 'text-gray-400', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700', lightHover: 'hover:bg-gray-50', darkHover: 'hover:bg-gray-700'})
                                    ]"
                                    :disabled="pagination.currentPage <= 1"
                                    type="button"
                                    @click="changePage(pagination.currentPage - 1)"
                                >
                                    <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.chevron-left-solid"></svg-vue>
                                </button>

                                <button
                                    :class="[
                                        'relative inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-1 ring-inset focus:z-20 focus:outline-offset-0 transition-colors duration-150',
                                        pagination.currentPage >= pagination.totalPages
                                            ? getDarkModeClasses({lightBg: 'bg-gray-50', darkBg: 'bg-gray-800', lightText: 'text-gray-400', darkText: 'text-gray-500', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700'}) + ' cursor-not-allowed'
                                            : getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightText: 'text-gray-500', darkText: 'text-gray-400', lightRing: 'ring-gray-300', darkRing: 'ring-gray-700', lightHover: 'hover:bg-gray-50', darkHover: 'hover:bg-gray-700'})
                                    ]"
                                    :disabled="pagination.currentPage >= pagination.totalPages"
                                    type="button"
                                    @click="changePage(pagination.currentPage + 1)"
                                >
                                    <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-300' : 'text-gray-500'" icon="font-awesome.chevron-right-solid"></svg-vue>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "index",
    metaInfo() {
        return {
            title: this.$i18n.t('My tickets')
        }
    },
    mounted() {
        this.getStatuses();
        this.getTickets();
    },
    data() {
        return {
            loading: true,
            ticketList: [],
            statusList: [],
            filters: {
                search: '',
                status: null,
            },
            sort: {
                order: 'desc',
                column: 'updated_at',
            },
            page: 1,
            perPage: 10,
            pagination: {
                currentPage: 0,
                perPage: 0,
                total: 0,
                totalPages: 0
            },
        };
    },
    computed: {
        anyFilter() {
            return this.filters.search !== ''
                || this.filters.status !== null
        }
    },
    filters: {
        momentFormatDate: function (value) {
            return moment(value).locale(window.app.app_date_locale).format(window.app.app_date_format);
        },
        momentFormatDateTimeAgo: function (value) {
            return moment(value).locale(window.app.app_date_locale).fromNow();
        },
    },
    methods: {
        getStatuses() {
            const self = this;
            axios.get('api/tickets/statuses').then(function (response) {
                self.statusList = response.data;
            });
        },
        changePage(page) {
            const self = this;
            if ((page > 0) && (page <= self.pagination.totalPages) && (page !== self.page)) {
                self.page = page;
                self.getTickets();
            }
        },
        changeSort() {
            const self = this;
            if (self.sort.order === 'asc') {
                self.sort.order = 'desc';
            } else if (self.sort.order === 'desc') {
                self.sort.order = 'asc';
            }
            self.getTickets();
        },
        getTickets() {
            const self = this;
            self.loading = true;
            axios.get('api/tickets', {
                params: {
                    page: self.page,
                    sort: self.sort,
                    perPage: self.perPage,
                    search: self.filters.search,
                    status: self.filters.status,
                }
            }).then(function (response) {
                self.ticketList = response.data.items;
                self.pagination = response.data.pagination;
                if (self.pagination.totalPages < self.pagination.currentPage) {
                    self.page = self.pagination.totalPages;
                    self.getTickets();
                } else {
                    self.loading = false;
                }
            }).catch(function () {
                self.loading = false;
            });
        },
    }
}
</script>

<style scoped>
/* Ticket Card Styles for Mobile View */
.ticket-card {
    border-radius: 0.5rem;
    border-width: 1px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition-property: all;
    transition-duration: 200ms;
}

/* Light mode */
:root:not(.dark) .ticket-card {
    background-color: #ffffff;
    border-color: #e5e7eb;
}

/* Dark mode */
.dark .ticket-card,
:root[data-theme="dark"] .ticket-card,
.dark-mode .ticket-card {
    background-color: #1f2937;
    border-color: #374151;
}

.ticket-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-0.25rem);
}

.ticket-card-header {
    padding: 1rem;
    border-bottom-width: 1px;
    border-bottom-style: solid;
    border-bottom-color: #f3f4f6;
}

.dark-mode .ticket-card-header {
    border-bottom-color: #374151;
}

.ticket-card-body {
    padding: 1rem;
}

.ticket-card-footer {
    padding: 1rem;
    background-color: #f9fafb;
    border-top-width: 1px;
    border-top-style: solid;
    border-top-color: #f3f4f6;
    display: flex;
    align-items: center;
}

.dark-mode .ticket-card-footer {
    background-color: rgba(31, 41, 55, 0.5);
    border-top-color: #374151;
}

/* Table Styles */
table {
    border-collapse: collapse;
    table-layout: auto;
    width: 100%;
}

th {
    font-weight: 500;
    text-align: left;
}

tbody tr {
    border-bottom-width: 1px;
    border-bottom-style: solid;
    border-bottom-color: #f3f4f6;
}

tbody tr:last-child {
    border-bottom: 0;
}

.dark-mode tbody tr {
    border-bottom-color: #374151;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    border-width: 1px;
    border-style: solid;
}

/* Responsive pagination */
.pagination-info {
    font-size: 0.875rem;
}

@media (max-width: 640px) {
    .pagination-info {
        font-size: 0.75rem;
    }
}

/* Animation for hover effects */
@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-5px);
    }
    100% {
        transform: translateY(0px);
    }
}

.ticket-card:hover {
    animation: float 0.3s ease-in-out;
}
</style>
