<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex items-center justify-between">
                <h1 class="py-0.5 text-2xl font-semibold flex items-center" :class="getDarkModeClasses({lightText: 'text-secondary-900', darkText: 'text-white'})">
                    <svg-vue class="h-6 w-6 text-primary-600 mr-2" icon="font-awesome/file-export-solid"></svg-vue>
                    {{ $t('Reports') }}
                </h1>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-6">
            <!-- Filter Section -->
            <div class="rounded-lg shadow mb-6" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800'})">
                <div class="px-6 py-4 border-b" :class="getDarkModeClasses({lightBorder: 'border-gray-200', darkBorder: 'border-gray-700'})">
                    <h3 class="text-lg font-medium" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                        {{ $t('Filter Options') }}
                    </h3>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Date Range -->
                        <div class="space-y-4">
                            <h4 class="font-medium" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                                {{ $t('Date Range') }}
                            </h4>

                            <!-- Pre-defined Periods -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                    {{ $t('Quick Select') }}
                                </label>
                                <select v-model="filters.period" @change="onPeriodChange"
                                        class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                    <option value="">{{ $t('Custom Range') }}</option>
                                    <option value="daily">{{ $t('Today') }}</option>
                                    <option value="weekly">{{ $t('This Week') }}</option>
                                    <option value="monthly">{{ $t('This Month') }}</option>
                                    <option value="quarterly">{{ $t('This Quarter') }}</option>
                                    <option value="annually">{{ $t('This Year') }}</option>
                                </select>
                            </div>

                            <!-- Custom Date Range -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                        {{ $t('Start Date') }}
                                    </label>
                                    <input type="date" v-model="filters.start_date" @change="clearPeriod"
                                           class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                           :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                        {{ $t('End Date') }}
                                    </label>
                                    <input type="date" v-model="filters.end_date" @change="clearPeriod"
                                           class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                           :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                </div>
                            </div>
                        </div>

                        <!-- Status & Priority Filters -->
                        <div class="space-y-4">
                            <h4 class="font-medium" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                                {{ $t('Status & Priority') }}
                            </h4>

                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                    {{ $t('Status') }}
                                </label>
                                <select v-model="filters.status_ids" multiple
                                        class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                    <option v-for="status in filterOptions.statuses" :key="status.id" :value="status.id">
                                        {{ status.name }}
                                    </option>
                                </select>
                                <p class="text-xs mt-1" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('Hold Ctrl/Cmd to select multiple') }}
                                </p>
                            </div>

                            <!-- Priority Filter -->
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                    {{ $t('Priority') }}
                                </label>
                                <select v-model="filters.priority_ids" multiple
                                        class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                    <option v-for="priority in filterOptions.priorities" :key="priority.id" :value="priority.id">
                                        {{ priority.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Department & Agent Filters -->
                        <div class="space-y-4">
                            <h4 class="font-medium" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                                {{ $t('Department & Agent') }}
                            </h4>

                            <!-- Department Filter -->
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                    {{ $t('Department') }}
                                </label>
                                <select v-model="filters.department_ids" multiple @change="onDepartmentChange"
                                        class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                    <option v-for="department in filterOptions.departments" :key="department.id" :value="department.id">
                                        {{ department.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Agent Filter -->
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                    {{ $t('Agent') }}
                                </label>
                                <input-select-scrollable
                                    v-model="filters.agent_ids"
                                    :options="filterOptions.agents"
                                    :multiple="true"
                                    :placeholder="$t('Select agents...')"
                                    option-value="id"
                                    option-label="name"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Filters Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                        <!-- Concern Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                {{ $t('Concern Category') }}
                            </label>
                            <select v-model="filters.concern_ids" multiple
                                    class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                <option v-for="concern in filteredConcerns" :key="concern.id" :value="concern.id">
                                    {{ concern.name }} ({{ concern.department_name }})
                                </option>
                            </select>
                        </div>

                        <!-- Condo Location Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                {{ $t('Condo Location') }}
                            </label>
                            <select v-model="filters.condo_location_ids" multiple
                                    class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                                <option v-for="location in filterOptions.condo_locations" :key="location.id" :value="location.id">
                                    {{ location.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Search Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-1" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                {{ $t('Search') }}
                            </label>
                            <input type="text" v-model="filters.search"
                                   :placeholder="$t('Search by subject, content, or user...')"
                                   class="w-full rounded-md border shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                   :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300', darkBg: 'bg-gray-700 border-gray-600 text-white'})">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t" :class="getDarkModeClasses({lightBorder: 'border-gray-200', darkBorder: 'border-gray-700'})">
                        <button @click="generateReport" :disabled="loading"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg-vue v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" icon="font-awesome.spinner-solid"></svg-vue>
                            <svg-vue v-else class="-ml-1 mr-2 h-4 w-4" icon="font-awesome.search-solid"></svg-vue>
                            {{ loading ? $t('Generating...') : $t('Generate Report') }}
                        </button>

                        <button @click="downloadReport" :disabled="loading || !reportData || reportData.data.length === 0"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg-vue v-if="downloading" class="animate-spin -ml-1 mr-2 h-4 w-4" icon="font-awesome.spinner-solid"></svg-vue>
                            <svg-vue v-else class="-ml-1 mr-2 h-4 w-4" icon="font-awesome/file-export-solid"></svg-vue>
                            {{ downloading ? $t('Downloading...') : $t('Download CSV') }}
                        </button>

                        <button @click="clearFilters"
                                class="inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50', darkBg: 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600'})">
                            <svg-vue class="-ml-1 mr-2 h-4 w-4" icon="font-awesome.times-solid"></svg-vue>
                            {{ $t('Clear Filters') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Report Results -->
            <div v-if="reportData" class="rounded-lg shadow" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800'})">
                <div class="px-6 py-4 border-b" :class="getDarkModeClasses({lightBorder: 'border-gray-200', darkBorder: 'border-gray-700'})">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                            {{ $t('Report Results') }}
                        </h3>
                        <span class="text-sm" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                            {{ $t('Total: {count} tickets', {count: reportData.total}) }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'divide-gray-200', darkBorder: 'divide-gray-700'})">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('ID') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('Subject') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('User') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('Department') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('Status') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('Priority') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ $t('Created') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'divide-gray-200', darkBorder: 'divide-gray-700'})">
                            <tr v-for="ticket in reportData.data" :key="ticket.id" class="hover:bg-gray-50" :class="getDarkModeClasses({lightHover: 'hover:bg-gray-50', darkHover: 'hover:bg-gray-700'})">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                                    #{{ ticket.id }}
                                </td>
                                <td class="px-6 py-4 text-sm" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                                    <div class="max-w-xs truncate">{{ ticket.subject }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ ticket.user ? ticket.user.name : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ ticket.department ? ticket.department.name : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="ticket.status" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-white"
                                          :style="{backgroundColor: ticket.status.color}">
                                        {{ ticket.status.name }}
                                    </span>
                                    <span v-else class="text-sm" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">N/A</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ ticket.priority ? ticket.priority.name : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                                    {{ formatDate(ticket.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="reportData.last_page > 1" class="px-6 py-4 border-t" :class="getDarkModeClasses({lightBorder: 'border-gray-200', darkBorder: 'border-gray-700'})">
                    <div class="flex items-center justify-between">
                        <div class="text-sm" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                            {{ $t('Showing {from} to {to} of {total} results', {
                                from: reportData.from,
                                to: reportData.to,
                                total: reportData.total
                            }) }}
                        </div>
                        <div class="flex space-x-2">
                            <button @click="changePage(reportData.current_page - 1)"
                                    :disabled="reportData.current_page <= 1"
                                    class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50', darkBg: 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600'})">
                                {{ $t('Previous') }}
                            </button>
                            <span class="px-3 py-1 text-sm" :class="getDarkModeClasses({lightText: 'text-gray-700', darkText: 'text-gray-300'})">
                                {{ reportData.current_page }} / {{ reportData.last_page }}
                            </span>
                            <button @click="changePage(reportData.current_page + 1)"
                                    :disabled="reportData.current_page >= reportData.last_page"
                                    class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="getDarkModeClasses({lightBg: 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50', darkBg: 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600'})">
                                {{ $t('Next') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loading" class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="mx-auto h-12 w-12 text-gray-400 fill-current">
                    <path d="M184 48l144 0c4.4 0 8 3.6 8 8l0 40L176 96l0-40c0-4.4 3.6-8 8-8zm-56 8l0 40L64 96C28.7 96 0 124.7 0 160l0 96 192 0 160 0 8.2 0c32.3-39.1 81.1-64 135.8-64c5.4 0 10.7 .2 16 .7l0-32.7c0-35.3-28.7-64-64-64l-64 0 0-40c0-30.9-25.1-56-56-56L184 0c-30.9 0-56 25.1-56 56zM320 352l-96 0c-17.7 0-32-14.3-32-32l0-32L0 288 0 416c0 35.3 28.7 64 64 64l296.2 0C335.1 449.6 320 410.5 320 368c0-5.4 .2-10.7 .7-16l-.7 0zm320 16a144 144 0 1 0 -288 0 144 144 0 1 0 288 0zM496 288c8.8 0 16 7.2 16 16l0 48 32 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-48 0c-8.8 0-16-7.2-16-16l0-64c0-8.8 7.2-16 16-16z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium" :class="getDarkModeClasses({lightText: 'text-gray-900', darkText: 'text-white'})">
                    {{ $t('No report generated') }}
                </h3>
                <p class="mt-1 text-sm" :class="getDarkModeClasses({lightText: 'text-gray-500', darkText: 'text-gray-400'})">
                    {{ $t('Set your filters and click "Generate Report" to view ticket data.') }}
                </p>
            </div>
        </div>
    </main>
</template>

<script>
import DarkModeMixin from "@/mixins/dark-mode-mixin";

export default {
    name: "Reports",
    mixins: [DarkModeMixin],
    metaInfo() {
        return {
            title: this.$i18n.t('Reports')
        }
    },
    data() {
        return {
            loading: false,
            downloading: false,
            reportData: null,
            filterOptions: {
                departments: [],
                statuses: [],
                priorities: [],
                agents: [],
                concerns: [],
                condo_locations: []
            },
            filters: {
                period: '',
                start_date: '',
                end_date: '',
                status_ids: [],
                priority_ids: [],
                department_ids: [],
                agent_ids: [],
                concern_ids: [],
                condo_location_ids: [],
                search: '',
                per_page: 25
            },
            currentPage: 1
        }
    },
    computed: {
        filteredConcerns() {
            if (this.filters.department_ids.length === 0) {
                return this.filterOptions.concerns;
            }
            return this.filterOptions.concerns.filter(concern =>
                this.filters.department_ids.includes(concern.department_id)
            );
        }
    },
    methods: {
        async loadFilterOptions() {
            try {
                console.log('Loading filter options...');
                const response = await axios.get('/api/dashboard/reports/filter-options');
                console.log('Filter options response:', response.data);
                this.filterOptions = response.data;
                this.$notify({
                    type: 'success',
                    text: this.$i18n.t('Filter options loaded successfully')
                });
            } catch (error) {
                console.error('Error loading filter options:', error);
                console.error('Error response:', error.response);
                this.$notify({
                    type: 'error',
                    text: this.$i18n.t('Error loading filter options: ') + (error.response?.data?.message || error.message)
                });
            }
        },

        async generateReport(page = 1) {
            this.loading = true;
            this.currentPage = page;

            try {
                const params = {
                    ...this.filters,
                    page: page
                };

                // Remove empty arrays and strings
                Object.keys(params).forEach(key => {
                    if (Array.isArray(params[key]) && params[key].length === 0) {
                        delete params[key];
                    } else if (typeof params[key] === 'string' && params[key].trim() === '') {
                        delete params[key];
                    }
                });

                // Convert string values to integers for ID arrays
                ['status_ids', 'priority_ids', 'department_ids', 'agent_ids', 'concern_ids', 'condo_location_ids'].forEach(key => {
                    if (params[key] && Array.isArray(params[key])) {
                        params[key] = params[key].map(id => parseInt(id, 10));
                    }
                });

                console.log('Generating report with params:', params);
                const response = await axios.get('/api/dashboard/reports/tickets', { params });
                console.log('Report response:', response.data);
                this.reportData = response.data;

                if (this.reportData.data.length === 0) {
                    this.$notify({
                        type: 'info',
                        text: this.$i18n.t('No tickets found for the selected criteria.')
                    });
                } else {
                    this.$notify({
                        type: 'success',
                        text: this.$i18n.t('Report generated successfully. {count} tickets found.', {
                            count: this.reportData.total
                        })
                    });
                }
            } catch (error) {
                console.error('Error generating report:', error);
                console.error('Error response:', error.response);
                this.$notify({
                    type: 'error',
                    text: this.$i18n.t('Error generating report: ') + (error.response?.data?.message || error.message)
                });
                this.reportData = null;
            } finally {
                this.loading = false;
            }
        },

        async downloadReport() {
            this.downloading = true;

            try {
                const params = { ...this.filters };

                // Remove empty arrays and strings
                Object.keys(params).forEach(key => {
                    if (Array.isArray(params[key]) && params[key].length === 0) {
                        delete params[key];
                    } else if (typeof params[key] === 'string' && params[key].trim() === '') {
                        delete params[key];
                    }
                });

                // Convert string values to integers for ID arrays
                ['status_ids', 'priority_ids', 'department_ids', 'agent_ids', 'concern_ids', 'condo_location_ids'].forEach(key => {
                    if (params[key] && Array.isArray(params[key])) {
                        params[key] = params[key].map(id => parseInt(id, 10));
                    }
                });

                console.log('Downloading report with params:', params);
                const response = await axios.get('/api/dashboard/reports/tickets/download', {
                    params,
                    responseType: 'blob'
                });
                console.log('Download response:', response);

                // Check if response is actually an Excel file
                if (response.data.type === 'application/json') {
                    // Handle JSON error response
                    const text = await response.data.text();
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || 'Failed to generate Excel file');
                }

                // Create download link
                const url = window.URL.createObjectURL(new Blob([response.data], {
                    type: 'text/csv'
                }));
                const link = document.createElement('a');
                link.href = url;

                // Extract filename from response headers
                let fileName = 'ticket_report.csv';
                const contentDisposition = response.headers['content-disposition'];
                if (contentDisposition) {
                    const fileNameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                    if (fileNameMatch && fileNameMatch[1]) {
                        fileName = fileNameMatch[1].replace(/['"]/g, '');
                    }
                }

                link.setAttribute('download', fileName);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);

                this.$notify({
                    type: 'success',
                    text: this.$i18n.t('Report downloaded successfully.')
                });
            } catch (error) {
                console.error('Error downloading report:', error);
                console.error('Error response:', error.response);
                this.$notify({
                    type: 'error',
                    text: this.$i18n.t('Error downloading report: ') + (error.response?.data?.message || error.message)
                });
            } finally {
                this.downloading = false;
            }
        },

        clearFilters() {
            this.filters = {
                period: '',
                start_date: '',
                end_date: '',
                status_ids: [],
                priority_ids: [],
                department_ids: [],
                agent_ids: [],
                concern_ids: [],
                condo_location_ids: [],
                search: '',
                per_page: 25
            };
            this.reportData = null;
            this.currentPage = 1;
        },

        onPeriodChange() {
            if (this.filters.period) {
                this.filters.start_date = '';
                this.filters.end_date = '';
            }
        },

        clearPeriod() {
            this.filters.period = '';
        },

        onDepartmentChange() {
            // Clear concern filter when department changes
            this.filters.concern_ids = [];
        },

        changePage(page) {
            if (page >= 1 && page <= this.reportData.last_page) {
                this.generateReport(page);
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
    },

    async mounted() {
        await this.loadFilterOptions();
    }
}
</script>
