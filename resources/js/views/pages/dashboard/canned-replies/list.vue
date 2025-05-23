<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('Canned replies') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <router-link
                        class="btn btn-blue shadow-sm rounded-md"
                        to="/dashboard/canned-replies/new"
                    >
                        {{ $t('Create canned reply') }}
                    </router-link>
                </div>
            </div>
        </div>
        <!-- Search and Filter Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Search Input -->
                    <div class="col-span-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Search') }}</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg-vue class="h-5 w-5 text-gray-400 dark:text-gray-500" icon="font-awesome.search-solid"></svg-vue>
                            </div>
                            <input
                                id="search"
                                v-model.lazy="filters.search"
                                type="text"
                                class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md"
                                :placeholder="$t('Search by name or body...')"
                                @change="getCannedReplies"
                            />
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="col-span-1">
                        <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Sort by') }}</label>
                        <div class="relative inline-flex w-full shadow-sm rounded-md">
                             <button
                                type="button"
                                @click="toggleSortOrder"
                                class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                            >
                                <svg-vue
                                    v-show="sort.order === 'asc'"
                                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                    icon="font-awesome.sort-amount-down-alt-regular"
                                ></svg-vue>
                                <svg-vue
                                    v-show="sort.order === 'desc'"
                                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                    icon="font-awesome.sort-amount-up-alt-regular"
                                ></svg-vue>
                            </button>
                            <select
                                id="sort"
                                v-model="sort.column"
                                class="-ml-px block w-full pl-3 pr-9 py-2 rounded-l-none rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                @change="applySort"
                            >
                                <option value="name">{{ $t('Name') }}</option>
                                <option value="shared">{{ $t('Shared Status') }}</option>
                                <option value="user_id">{{ $t('Creator') }}</option>
                                <option value="created_at">{{ $t('Created Date') }}</option>
                                <option value="updated_at">{{ $t('Last Updated') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <loading :status="loading"/>
            <div v-if="!loading && cannedReplyList.length > 0" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <!-- Canned Replies Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 p-5">
                    <div
                        v-for="cannedReply in cannedReplyList"
                        :key="cannedReply.id"
                        class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 flex flex-col"
                    >
                        <div class="p-5 flex-grow">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate" :title="cannedReply.name">
                                    {{ cannedReply.name }}
                                </h3>
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        cannedReply.shared
                                            ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'
                                    ]"
                                >
                                    {{ cannedReply.shared ? $t('Shared') : $t('Private') }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-3" :title="cannedReply.body">
                                {{ cannedReply.body }}
                            </p>

                            <div v-if="cannedReply.user" class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-auto pt-2 border-t border-gray-100 dark:border-gray-600">
                                <img :src="cannedReply.user.avatar !== 'gravatar' ? cannedReply.user.avatar : cannedReply.user.gravatar" :alt="cannedReply.user.name" class="h-6 w-6 rounded-full mr-2">
                                <span>{{ $t('By') }} {{ cannedReply.user.name }} &bull; {{ formatDate(cannedReply.created_at) }}</span>
                            </div>
                             <div v-else class="text-xs text-gray-500 dark:text-gray-400 mt-auto pt-2 border-t border-gray-100 dark:border-gray-600">
                                 <span>{{ $t('Created') }}: {{ formatDate(cannedReply.created_at) }}</span>
                             </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-750 px-5 py-3 flex justify-end space-x-3 border-t border-gray-100 dark:border-gray-600">
                            <router-link
                                :to="'/dashboard/canned-replies/' + cannedReply.id + '/edit'"
                                class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                                :title="$t('Edit')"
                            >
                                <svg-vue class="h-5 w-5" icon="font-awesome.edit-regular"></svg-vue>
                            </router-link>
                            <button
                                @click.prevent="confirmDelete(cannedReply)"
                                class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                :title="$t('Delete')"
                            >
                                <svg-vue class="h-5 w-5" icon="font-awesome.trash-alt-regular"></svg-vue>
                            </button>
                        </div>
                    </div>
                </div>
                 <!-- Pagination -->
                <nav v-if="pagination.totalPages > 1" class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <div class="hidden sm:block">
                        <p class="text-sm leading-5 text-gray-700 dark:text-gray-300">
                            {{ $t('Showing') }}
                            <span class="font-medium">{{ (pagination.perPage * pagination.currentPage) - pagination.perPage + 1 }}</span>
                            {{ $t('to') }}
                            <span class="font-medium">{{ pagination.perPage * pagination.currentPage <= pagination.total ? pagination.perPage * pagination.currentPage : pagination.total }}</span>
                            {{ $t('of') }}
                            <span class="font-medium">{{ pagination.total }}</span>
                            {{ $t('results') }}
                        </p>
                    </div>
                    <div class="flex-1 flex justify-between sm:justify-end">
                        <button
                            :class="pagination.currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''"
                            :disabled="pagination.currentPage <= 1"
                            class="pagination-link dark:text-gray-300 dark:hover:bg-gray-700"
                            type="button"
                            @click="changePage(pagination.currentPage - 1)"
                        >
                            {{ $t('Previous') }}
                        </button>
                        <button
                            :class="pagination.currentPage >= pagination.totalPages ? 'opacity-50 cursor-not-allowed' : ''"
                            :disabled="pagination.currentPage >= pagination.totalPages"
                            class="ml-3 pagination-link dark:text-gray-300 dark:hover:bg-gray-700"
                            type="button"
                            @click="changePage(pagination.currentPage + 1)"
                        >
                            {{ $t('Next') }}
                        </button>
                    </div>
                </nav>
            </div>
            <!-- Empty State -->
            <div v-else-if="!loading && cannedReplyList.length === 0" class="text-center py-12">
                <svg-vue class="mx-auto h-24 w-24 text-gray-300 dark:text-gray-500" icon="undraw.empty_street"></svg-vue>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">{{ $t('No canned replies found') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('Get started by creating a new canned reply.') }}</p>
                <div class="mt-6">
                    <router-link
                        to="/dashboard/canned-replies/new"
                        class="gradient-button inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        <svg-vue class="-ml-1 mr-2 h-5 w-5" icon="font-awesome.plus-solid"></svg-vue>
                        {{ $t('Create canned reply') }}
                    </router-link>
                </div>
            </div>
        </div>
         <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed z-20 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" aria-hidden="true" @click="showDeleteModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-700 sm:mx-0 sm:h-10 sm:w-10">
                                <svg-vue class="h-6 w-6 text-red-600 dark:text-red-300" icon="font-awesome.exclamation-triangle-solid"></svg-vue>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    {{ $t('Delete Canned Reply') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        {{ $t('Are you sure you want to delete this canned reply? This action cannot be undone.') }}
                                    </p>
                                    <p v-if="cannedReplyToDelete" class="mt-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        {{ cannedReplyToDelete.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-750 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 dark:hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="deleting"
                            @click="deleteCannedReply"
                        >
                            <svg-vue v-if="deleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" icon="font-awesome.spinner-solid"></svg-vue>
                            {{ deleting ? $t('Deleting...') : $t('Delete') }}
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="showDeleteModal = false"
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
import moment from 'moment'; // Import moment for date formatting

export default {
    name: "canned-replies-list",
    metaInfo() {
        return {
            title: this.$i18n.t('Canned replies')
        }
    },
    data() {
        return {
            page: 1,
            filters: {
                search: null,
            },
            sort: {
                order: 'asc',
                column: 'created_at',
            },
            perPage: 10,
            loading: true,
            pagination: {
                currentPage: 0,
                perPage: 0,
                total: 0,
                totalPages: 0
            },
            cannedReplyList: [],
            showDeleteModal: false,
            cannedReplyToDelete: null,
            deleting: false,
        }
    },
    mounted() {
        this.getCannedReplies();
    },
    methods: {
        formatDate(date) {
            return moment(date).format('MMM D, YYYY');
        },
        changePage(page) {
            const self = this;
            if ((page > 0) && (page <= self.pagination.totalPages) && (page !== self.page)) {
                self.page = page;
                self.getCannedReplies();
            }
        },
        toggleSortOrder() {
            this.sort.order = this.sort.order === 'asc' ? 'desc' : 'asc';
            this.getCannedReplies();
        },
        applySort() {
            // Called when sort.column changes
            this.getCannedReplies();
        },
        confirmDelete(cannedReply) {
            this.cannedReplyToDelete = cannedReply;
            this.showDeleteModal = true;
        },
        deleteCannedReply() {
            if (!this.cannedReplyToDelete) return;
            const self = this;
            self.deleting = true;
            axios.delete(`api/dashboard/canned-replies/${self.cannedReplyToDelete.id}`)
                .then(function () {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: self.$i18n.t('Canned reply deleted successfully').toString(),
                        type: 'success'
                    });
                    self.showDeleteModal = false;
                    self.cannedReplyToDelete = null;
                    self.getCannedReplies(); // Refresh list
                })
                .catch(function (error) {
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response?.data?.message || self.$i18n.t('Could not delete canned reply'),
                        type: 'error'
                    });
                })
                .finally(function () {
                    self.deleting = false;
                });
        },
        getCannedReplies() {
            const self = this;
            self.loading = true;
            axios.get('api/dashboard/canned-replies', {
                params: {
                    page: self.page,
                    sort: self.sort,
                    perPage: self.perPage,
                    search: self.filters.search
                }
            }).then(function (response) {
                self.cannedReplyList = response.data.items;
                self.pagination = response.data.pagination;
                if (self.pagination.totalPages < self.pagination.currentPage) {
                    self.page = self.pagination.totalPages;
                    self.getCannedReplies();
                } else {
                    self.loading = false;
                }
            }).catch(function () {
                self.loading = false;
            });
        }
    }
}
</script>
