<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('FAQs') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <router-link
                        class="btn btn-blue shadow-sm rounded-md"
                        to="/dashboard/admin/faqs/new"
                    >
                        {{ $t('Create FAQ') }}
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5 mt-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-grow">
                    <label for="search" class="sr-only">{{ $t('Search') }}</label>
                    <input
                        id="search"
                        v-model="filters.search"
                        @keyup.enter="getFaqs"
                        type="search"
                        name="search"
                        class="form-input block w-full sm:text-sm sm:leading-5"
                        :placeholder="$t('Search by question or answer...')"
                    />
                </div>
                <div class="flex-shrink-0">
                    <label for="category" class="sr-only">{{ $t('Category') }}</label>
                    <select
                        id="category"
                        v-model="filters.category"
                        @change="getFaqs"
                        class="form-select block w-full md:w-auto sm:text-sm sm:leading-5"
                    >
                        <option value="">{{ $t('All Categories') }}</option>
                        <option value="wifi">{{ $t('WiFi Helpdesk') }}</option>
                        <option value="general">{{ $t('General Helpdesk') }}</option>
                    </select>
                </div>
                 <div class="flex-shrink-0">
                    <button @click="getFaqs" class="btn btn-white">{{ $t('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="my-6 bg-white shadow overflow-hidden sm:rounded-md">
                <loading :status="loading"/>
                <template v-if="faqs.length > 0">
                    <ul>
                        <template v-for="(faq, index) in faqs">
                            <li :key="faq.id" :class="{'border-t border-gray-200': index !== 0}">
                                <router-link
                                    :to="'/dashboard/admin/faqs/' + faq.id + '/edit'"
                                    class="block hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                >
                                    <div class="px-4 py-4 flex items-center sm:px-6">
                                        <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <div class="text-sm font-medium leading-5 text-gray-900 truncate">
                                                    {{ faq.question }}
                                                </div>
                                                <div class="mt-1 text-sm leading-5 text-gray-600">
                                                    <p class="truncate">{{ faq.answer }}</p>
                                                </div>
                                                <div class="mt-2 flex items-center text-sm leading-5 text-gray-500">
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                        :class="{
                                                            'bg-blue-100 text-blue-800': faq.category === 'wifi',
                                                            'bg-indigo-100 text-indigo-800': faq.category === 'general'
                                                        }"
                                                    >
                                                        {{ faq.category === 'wifi' ? $t('WiFi Helpdesk') : $t('General Helpdesk') }}
                                                    </span>
                                                    <span class="ml-2">{{ $t('Created') }}: {{ formatDate(faq.created_at) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-5 flex items-center space-x-4">
                                            <button
                                                @click.stop.prevent="deleteFaq(faq)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 focus:outline-none"
                                                :title="$t('Delete')"
                                            >
                                                <svg-vue class="h-5 w-5" icon="font-awesome/trash-alt-regular"></svg-vue>
                                            </button>
                                            <svg-vue class="h-5 w-5" :class="$store.state.darkMode ? 'text-gray-500' : 'text-gray-400'" icon="font-awesome/angle-right-regular"></svg-vue>
                                        </div>
                                    </div>
                                </router-link>
                            </li>
                        </template>
                    </ul>
                </template>
                <template v-else-if="!loading">
                    <div class="h-full flex">
                        <div class="m-auto">
                            <div class="grid grid-cols-1 justify-items-center h-full w-full px-4 py-10">
                                <div class="flex justify-center items-center">
                                    <svg-vue class="h-full h-auto w-64 mb-12" icon="undraw.browsing"></svg-vue>
                                </div>
                                <div class="flex justify-center items-center">
                                    <div class="w-full font-semibold text-2xl">{{ $t('No FAQs found') }}</div>
                                </div>
                                 <div v-if="filters.search || filters.category" class="mt-4">
                                    <button @click="clearFilters" class="btn btn-sm btn-white">{{ $t('Clear Filters') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "faqs-list",
    metaInfo() {
        return {
            title: this.$i18n.t('FAQs')
        }
    },
    data() {
        return {
            loading: true,
            faqs: [],
            filters: {
                search: '',
                category: '',
            }
        }
    },
    mounted() {
        this.getFaqs();
    },
    methods: {
        getFaqs() {
            const self = this;
            self.loading = true;
            axios.get('api/dashboard/admin/faqs', { params: self.filters }).then(function (response) {
                self.faqs = response.data.data;
                self.loading = false;
            }).catch(function () {
                self.loading = false;
            });
        },
        formatDate(date) {
            return moment(date).format('LL');
        },
        deleteFaq(faq) {
            const self = this;
            if (confirm(this.$i18n.t('Are you sure you want to delete this FAQ?'))) {
                self.loading = true;
                axios.delete('api/dashboard/admin/faqs/' + faq.id).then(function () {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: self.$i18n.t('FAQ deleted successfully').toString(),
                        type: 'success'
                    });
                    self.getFaqs();
                }).catch(function (error) {
                    self.loading = false;
                    if (error.response && error.response.data && error.response.data.message) {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: error.response.data.message,
                            type: 'error'
                        });
                    } else {
                         self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: self.$i18n.t('An unexpected error occurred').toString(),
                            type: 'error'
                        });
                    }
                });
            }
        },
        clearFilters() {
            this.filters.search = '';
            this.filters.category = '';
            this.getFaqs();
        }
    }
}
</script>
