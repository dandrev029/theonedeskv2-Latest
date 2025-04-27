<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h1 class="py-0.5 text-2xl font-semibold flex items-center" :class="{'text-secondary-900': !$store.state.darkMode, 'text-white': $store.state.darkMode}">
                <svg-vue class="h-6 w-6 text-primary-600 mr-2" icon="font-awesome.ticket-alt-regular"></svg-vue>
                {{ $t('My tickets') }}
            </h1>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex justify-between items-center py-4">
                <div></div>
                <div>
                    <router-link
                        class="btn btn-primary shadow-sm rounded-md"
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
                <div class="px-4 py-3 border-b" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
                        <!-- Desktop Search and Filter UI -->
                        <div class="hidden sm:block">
                            <label class="sr-only" for="search-desktop">{{ $t('Search') }}</label>
                            <div class="flex rounded-md shadow-sm">
                                <div class="relative flex-grow focus-within:z-10">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg-vue class="h-5 w-5" :class="textTertiary" icon="font-awesome.search-regular"></svg-vue>
                                    </div>
                                    <input
                                        id="search-desktop"
                                        v-model.lazy="filters.search"
                                        :placeholder="$t('Search')"
                                        class="form-input block w-full rounded-none rounded-l-md pl-10 text-sm transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600'})"
                                        @change="getTickets"
                                    >
                                </div>
                                <div class="relative inline-flex rounded-none">
                                    <select
                                        id="status-desktop"
                                        v-model="filters.status"
                                        aria-label="Filter by status"
                                        class="-mx-px block form-select w-full pl-3 pr-9 py-2 rounded-none border border-r-0 text-sm leading-5 font-medium transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})"
                                        @change="getTickets"
                                    >
                                        <option :value="null">{{ $t('All requests') }}</option>
                                        <template v-for="status in statusList">
                                            <option :value="status.id">{{ status.name }}</option>
                                        </template>
                                    </select>
                                </div>
                                <div class="relative inline-flex rounded-r-md">
                                    <button
                                        class="relative -ml-px inline-flex items-center px-4 py-2 border text-sm leading-5 font-medium transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})"
                                        type="button"
                                        @click="changeSort"
                                    >
                                        <svg-vue
                                            v-show="sort.order === 'asc'"
                                            class="h-5 w-5"
                                            :class="textTertiary"
                                            icon="font-awesome.sort-amount-down-alt-regular"
                                        ></svg-vue>
                                        <svg-vue
                                            v-show="sort.order === 'desc'"
                                            class="h-5 w-5"
                                            :class="textTertiary"
                                            icon="font-awesome.sort-amount-up-alt-regular"
                                        ></svg-vue>
                                        <span class="ml-2">{{ $t('Sort') }}</span>
                                    </button>
                                    <select
                                        id="sortBy-desktop"
                                        v-model="sort.column"
                                        aria-label="Sort by"
                                        class="block form-select w-full pl-3 pr-9 py-2 rounded-l-none rounded-r-md border text-sm leading-5 font-medium transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})"
                                        @change="changeSort"
                                    >
                                        <option value="subject">{{ $t('Subject') }}</option>
                                        <option value="status_id">{{ $t('Status') }}</option>
                                        <option value="created_at">{{ $t('Created at') }}</option>
                                        <option value="updated_at">{{ $t('Updated at') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Search and Filter UI -->
                        <div class="sm:hidden">
                            <div class="mb-3">
                                <label class="sr-only" for="search-mobile">{{ $t('Search') }}</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg-vue class="h-5 w-5" :class="textTertiary" icon="font-awesome.search-regular"></svg-vue>
                                    </div>
                                    <input
                                        id="search-mobile"
                                        v-model.lazy="filters.search"
                                        :placeholder="$t('Search')"
                                        class="form-input block w-full rounded-md pl-10 text-sm transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600'})"
                                        @change="getTickets"
                                    >
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <div class="flex-1">
                                    <select
                                        id="status-mobile"
                                        v-model="filters.status"
                                        aria-label="Filter by status"
                                        class="block form-select w-full pl-3 pr-9 py-2 rounded-md border text-sm leading-5 font-medium transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})"
                                        @change="getTickets"
                                    >
                                        <option :value="null">{{ $t('All requests') }}</option>
                                        <template v-for="status in statusList">
                                            <option :value="status.id">{{ status.name }}</option>
                                        </template>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <select
                                        id="sortBy-mobile"
                                        v-model="sort.column"
                                        aria-label="Sort by"
                                        class="block form-select w-full pl-3 pr-9 py-2 rounded-md border text-sm leading-5 font-medium transition ease-in-out duration-150"
                                        :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})"
                                        @change="changeSort"
                                    >
                                        <option value="subject">{{ $t('Subject') }}</option>
                                        <option value="status_id">{{ $t('Status') }}</option>
                                        <option value="created_at">{{ $t('Created at') }}</option>
                                        <option value="updated_at">{{ $t('Updated at') }}</option>
                                    </select>
                                </div>
                                <button
                                    class="inline-flex items-center px-3 py-2 border rounded-md text-sm leading-5 font-medium transition ease-in-out duration-150"
                                    :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})"
                                    type="button"
                                    @click="changeSort"
                                >
                                    <svg-vue
                                        v-show="sort.order === 'asc'"
                                        class="h-5 w-5"
                                        :class="textTertiary"
                                        icon="font-awesome.sort-amount-down-alt-regular"
                                    ></svg-vue>
                                    <svg-vue
                                        v-show="sort.order === 'desc'"
                                        class="h-5 w-5"
                                        :class="textTertiary"
                                        icon="font-awesome.sort-amount-up-alt-regular"
                                    ></svg-vue>
                                </button>
                            </div>
                        </div>
                    </div>
                    <template v-if="ticketList.length > 0">
                        <!-- Desktop Table View -->
                        <div class="hidden sm:block -my-2 sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'divide-secondary-200', darkBorder: 'divide-gray-700'})">
                                    <thead>
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs leading-4 font-medium uppercase tracking-wider whitespace-no-wrap overflow-x-auto" :class="textTertiary">
                                            {{ $t('Subject') }}
                                        </th>
                                        <th class="px-6 py-2 text-left text-xs leading-4 font-medium uppercase tracking-wider whitespace-no-wrap overflow-x-auto" :class="textTertiary">
                                            {{ $t('Created at') }}
                                        </th>
                                        <th class="px-6 py-2 text-left text-xs leading-4 font-medium uppercase tracking-wider whitespace-no-wrap overflow-x-auto" :class="textTertiary">
                                            {{ $t('Updated at') }}
                                        </th>
                                        <th class="px-6 py-2 text-left text-xs leading-4 font-medium uppercase tracking-wider whitespace-no-wrap overflow-x-auto" :class="textTertiary">
                                            {{ $t('Status') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'divide-secondary-200', darkBorder: 'divide-gray-700'})">
                                    <template v-for="ticket in ticketList">
                                        <router-link
                                            :to="'/tickets/' + ticket.uuid"
                                            :class="getDarkModeClasses({lightHover: 'hover:bg-gray-100', darkHover: 'hover:bg-gray-700'})"
                                            class="cursor-pointer"
                                            tag="tr"
                                        >
                                            <td class="px-6 py-4 max-w-0 w-full whitespace-no-wrap">
                                                <div class="w-full truncate text-sm leading-5" :class="textPrimary">
                                                    {{ ticket.subject }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap leading-5">
                                                <div class="text-sm" :class="textSecondary">
                                                    {{ ticket.created_at | momentFormatDate }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap leading-5">
                                                <div class="text-sm" :class="textSecondary">
                                                    {{ ticket.updated_at | momentFormatDateTimeAgo }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap leading-5">
                                                <div class="flex items-center">
                                                    <div
                                                        class="w-3 h-3 mr-2 rounded-full"
                                                        :style="{ backgroundColor: ticket.status.color }"
                                                    ></div>
                                                    <div class="text-sm leading-5" :class="textSecondary">
                                                        {{ ticket.status.name }}
                                                    </div>
                                                </div>
                                            </td>
                                        </router-link>
                                    </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="sm:hidden">
                            <div class="space-y-4 px-2 py-3">
                                <div
                                    v-for="ticket in ticketList"
                                    :key="ticket.id"
                                    class="ticket-card cursor-pointer"
                                    @click="$router.push('/tickets/' + ticket.uuid)"
                                >
                                    <div class="ticket-card-header">
                                        <div class="flex justify-between items-start">
                                            <div class="w-full truncate text-sm font-medium leading-5" :class="textPrimary">
                                                {{ ticket.subject }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ticket-card-body">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="text-xs" :class="textSecondary">
                                                {{ ticket.created_at | momentFormatDate }}
                                            </div>
                                            <div class="text-xs" :class="textSecondary">
                                                {{ ticket.updated_at | momentFormatDateTimeAgo }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ticket-card-footer">
                                        <div class="flex items-center">
                                            <div
                                                class="w-3 h-3 mr-2 rounded-full"
                                                :style="{ backgroundColor: ticket.status.color }"
                                            ></div>
                                            <div class="text-sm leading-5" :class="textSecondary">
                                                {{ ticket.status.name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <!-- Loading Skeletons -->
                    <template v-else-if="loading">
                        <!-- Desktop Loading Skeleton -->
                        <div class="hidden sm:block -my-2 sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="animate-pulse">
                                    <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div>
                                    <div class="space-y-3">
                                        <div v-for="i in 5" :key="i" class="h-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Loading Skeleton -->
                        <div class="sm:hidden">
                            <div class="space-y-4 px-2 py-3">
                                <div v-for="i in 5" :key="i" class="animate-pulse">
                                    <div class="ticket-card">
                                        <div class="ticket-card-header">
                                            <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                        </div>
                                        <div class="ticket-card-body">
                                            <div class="flex justify-between items-center mb-2">
                                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                                            </div>
                                        </div>
                                        <div class="ticket-card-footer">
                                            <div class="flex items-center">
                                                <div class="h-3 w-3 bg-gray-200 dark:bg-gray-700 rounded-full mr-2"></div>
                                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- No Records Found -->
                    <template v-else-if="!loading && ticketList.length === 0">
                        <div class="h-full flex">
                            <div class="m-auto">
                                <div class="grid grid-cols-1 justify-items-center h-full w-full py-24">
                                    <div class="flex justify-center items-center">
                                        <svg-vue class="h-full h-auto w-48 mb-6" icon="undraw.task-list"></svg-vue>
                                    </div>
                                    <div class="flex justify-center items-center">
                                        <div class="w-full font-semibold text-2xl" :class="textPrimary">{{ $t('No records found') }}</div>
                                    </div>
                                    <template v-if="anyFilter">
                                        <div class="flex justify-center items-center">
                                            <div :class="textSecondary">{{ $t('Try changing the filters, or rephrasing your search') }}.</div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    <nav class="px-4 py-3 flex items-center justify-between border-t sm:px-6" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
                        <div class="hidden sm:block">
                            <p class="text-sm leading-5" :class="textSecondary">
                                {{ $t('Showing') }}
                                <span class="font-medium" :class="textPrimary">{{ (pagination.perPage * pagination.currentPage) - pagination.perPage + 1 }}</span>
                                {{ $t('to') }}
                                <span class="font-medium" :class="textPrimary">{{ pagination.perPage * pagination.currentPage <= pagination.total ? pagination.perPage * pagination.currentPage : pagination.total }}</span>
                                {{ $t('of') }}
                                <span class="font-medium" :class="textPrimary">{{ pagination.total }}</span>
                                {{ $t('results') }}
                            </p>
                        </div>
                        <!-- Mobile Pagination Info -->
                        <div class="sm:hidden pagination-info">
                            <p class="text-xs leading-5" :class="textSecondary">
                                <span class="font-medium" :class="textPrimary">{{ pagination.currentPage }}</span>
                                {{ $t('of') }}
                                <span class="font-medium" :class="textPrimary">{{ pagination.totalPages }}</span>
                            </p>
                        </div>
                        <div class="flex-1 flex justify-between sm:justify-end">
                            <button
                                :class="[pagination.currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : '', getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})]"
                                :disabled="pagination.currentPage <= 1"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md"
                                type="button"
                                @click="changePage(pagination.currentPage - 1)"
                            >
                                {{ $t('Previous') }}
                            </button>
                            <button
                                :class="[pagination.currentPage >= pagination.totalPages ? 'opacity-50 cursor-not-allowed' : '', 'ml-3', getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-700', lightBorder: 'border-secondary-300', darkBorder: 'border-gray-600', lightText: 'text-secondary-700', darkText: 'text-gray-300'})]"
                                :disabled="pagination.currentPage >= pagination.totalPages"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md"
                                type="button"
                                @click="changePage(pagination.currentPage + 1)"
                            >
                                {{ $t('Next') }}
                            </button>
                        </div>
                    </nav>
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
    @apply bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden transition-all duration-200;
}

.dark-mode .ticket-card {
    @apply bg-gray-800 border-gray-700;
}

.ticket-card:hover {
    @apply shadow-md;
}

.ticket-card-header {
    @apply p-4 border-b border-gray-100;
}

.dark-mode .ticket-card-header {
    @apply border-gray-700;
}

.ticket-card-body {
    @apply p-4;
}

.ticket-card-footer {
    @apply p-4 bg-gray-50 border-t border-gray-100;
}

.dark-mode .ticket-card-footer {
    @apply bg-gray-700 border-gray-700;
}

/* Responsive pagination for mobile */
@media (max-width: 640px) {
    .pagination-info {
        @apply text-xs;
    }
}
</style>
