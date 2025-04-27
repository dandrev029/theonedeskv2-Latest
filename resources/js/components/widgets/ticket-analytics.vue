<template>
    <div class="grid grid-cols-1 gap-6">
        <div class="flex flex-col rounded-lg shadow-sm border" :class="{'bg-white border-secondary-200': !$store.state.darkMode, 'bg-gray-800 border-gray-700': $store.state.darkMode}">
            <div class="p-4 border-b" :class="{'border-secondary-200': !$store.state.darkMode, 'border-gray-700': $store.state.darkMode}">
                <div class="font-semibold flex items-center" :class="{'text-secondary-800': !$store.state.darkMode, 'text-white': $store.state.darkMode}">
                    <svg class="h-5 w-5 text-primary-600 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M496 496H16c-8.8 0-16-7.2-16-16V16C0 7.2 7.2 0 16 0h16c8.8 0 16 7.2 16 16v448h448c8.8 0 16 7.2 16 16s-7.2 16-16 16zM112 432V240c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16v192c0 8.8 7.2 16 16 16h16c8.8 0 16-7.2 16-16zm112 0V144c0-8.8-7.2-16-16-16h-16c-8.8 0-16 7.2-16 16v288c0 8.8 7.2 16 16 16h16c8.8 0 16-7.2 16-16zm112 0V320c0-8.8-7.2-16-16-16h-16c-8.8 0-16 7.2-16 16v112c0 8.8 7.2 16 16 16h16c8.8 0 16-7.2 16-16zm112 0V176c0-8.8-7.2-16-16-16h-16c-8.8 0-16 7.2-16 16v256c0 8.8 7.2 16 16 16h16c8.8 0 16-7.2 16-16z"/></svg>
                    {{ $t('Ticket Response & Resolution Analytics') }}
                </div>
            </div>
            <div class="p-4">
                <loading :status="loading"/>
                <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="overflow-hidden rounded-lg border p-4" :class="{'bg-white border-secondary-200': !$store.state.darkMode, 'bg-gray-800 border-gray-700': $store.state.darkMode}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-primary-100 rounded-full p-3 mr-4">
                                <svg class="h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8l-22.4 30.8c-3.9 5.3-11.4 6.5-16.8 2.6z"/></svg>
                            </div>
                            <div>
                                <dt class="text-sm font-medium truncate" :class="{'text-secondary-500': !$store.state.darkMode, 'text-gray-400': $store.state.darkMode}">
                                    {{ $t('Avg. First Response Time') }}
                                </dt>
                                <dd class="mt-1 text-2xl font-semibold" :class="{'text-secondary-900': !$store.state.darkMode, 'text-white': $store.state.darkMode}">
                                    {{ currentMonthStats.avg_response_time }} {{ $t('hours') }}
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-lg border p-4" :class="{'bg-white border-secondary-200': !$store.state.darkMode, 'bg-gray-800 border-gray-700': $store.state.darkMode}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                                <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm108.3-291.1l-131.8 131.8c-4.7 4.7-12.3 4.7-17 0L171.7 243c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0l64.5 64.5 113-113c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17z"/></svg>
                            </div>
                            <div>
                                <dt class="text-sm font-medium truncate" :class="{'text-secondary-500': !$store.state.darkMode, 'text-gray-400': $store.state.darkMode}">
                                    {{ $t('Avg. Resolution Time') }}
                                </dt>
                                <dd class="mt-1 text-2xl font-semibold" :class="{'text-secondary-900': !$store.state.darkMode, 'text-white': $store.state.darkMode}">
                                    {{ currentMonthStats.avg_resolution_time }} {{ $t('hours') }}
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-lg border p-4" :class="{'bg-white border-secondary-200': !$store.state.darkMode, 'bg-gray-800 border-gray-700': $store.state.darkMode}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-full p-3 mr-4">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M113.1 129.1A111.1 111.1 0 1 0 0 240.1a111.1 111.1 0 1 0 113.1-111zm221.8 253.8a111.1 111.1 0 1 0-113.1 111 111.1 111.1 0 1 0 113.1-111zM425.8 21.8L22.2 425.8c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L471 67.1c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.2 0z"/></svg>
                            </div>
                            <div>
                                <dt class="text-sm font-medium truncate" :class="{'text-secondary-500': !$store.state.darkMode, 'text-gray-400': $store.state.darkMode}">
                                    {{ $t('First Response Rate') }}
                                </dt>
                                <dd class="mt-1 text-2xl font-semibold" :class="{'text-secondary-900': !$store.state.darkMode, 'text-white': $store.state.darkMode}">
                                    {{ currentMonthStats.first_response_rate }}%
                                </dd>
                                <p class="text-xs" :class="{'text-secondary-500': !$store.state.darkMode, 'text-gray-400': $store.state.darkMode}">
                                    {{ currentMonthStats.tickets_with_response }} / {{ currentMonthStats.total_tickets }} {{ $t('tickets') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <line-chart ref="chart" :chart-data="chartData" :height="350"></line-chart>
            </div>
        </div>
    </div>
</template>

<script>
import LineChart from "@/components/charts/line-chart";

export default {
    name: "ticket-analytics",
    components: {LineChart},
    data() {
        return {
            loading: true,
            chartData: {
                labels: [
                    this.$t('Jan'), this.$t('Feb'), this.$t('Mar'), this.$t('Apr'), this.$t('May'), this.$t('Jun'),
                    this.$t('Jul'), this.$t('Aug'), this.$t('Sept'), this.$t('Oct'), this.$t('Nov'), this.$t('Dec')
                ],
                datasets: [
                    {
                        label: this.$i18n.t('Avg. Response Time (hours)'),
                        backgroundColor: 'rgba(34, 198, 167, 0.2)',
                        borderColor: '#22c6a7',
                        borderWidth: 2,
                        pointBackgroundColor: '#22c6a7',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#22c6a7',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                    },
                    {
                        label: this.$i18n.t('Avg. Resolution Time (hours)'),
                        backgroundColor: 'rgba(66, 153, 225, 0.2)',
                        borderColor: '#4299e1',
                        borderWidth: 2,
                        pointBackgroundColor: '#4299e1',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4299e1',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                    }
                ],
            },
            currentMonthStats: {
                avg_response_time: 0,
                avg_resolution_time: 0,
                first_response_rate: 0,
                tickets_with_response: 0,
                total_tickets: 0,
                resolved_tickets: 0
            }
        }
    },
    computed: {
        datasets() {
            return [
                this.chartData.datasets[0].data,
                this.chartData.datasets[1].data
            ]
        }
    },
    watch: {
        datasets() {
            this.$refs.chart.update();
        }
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            const self = this;
            self.loading = true;
            axios.get('api/dashboard/stats/ticket-analytics').then(function (response) {
                self.chartData.datasets[0].data = response.data.response_time_data;
                self.chartData.datasets[1].data = response.data.resolution_time_data;
                self.currentMonthStats = response.data.current_month_stats;
                self.loading = false;
            });
        }
    },
}
</script>
