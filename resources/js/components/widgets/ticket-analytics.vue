<template>
    <div class="grid grid-cols-1 gap-6">
        <div class="flex flex-col bg-white rounded-lg shadow-sm border border-secondary-200">
            <div class="p-4 border-b border-secondary-200">
                <div class="font-semibold text-secondary-800 flex items-center">
                    <svg-vue class="h-5 w-5 text-primary-600 mr-2" icon="font-awesome.chart-bar-regular"></svg-vue>
                    {{ $t('Ticket Response & Resolution Analytics') }}
                </div>
            </div>
            <div class="p-4">
                <loading :status="loading"/>
                <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-white overflow-hidden rounded-lg border border-secondary-200 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-primary-100 rounded-full p-3 mr-4">
                                <svg-vue class="h-6 w-6 text-primary-600" icon="font-awesome.clock-regular"></svg-vue>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-secondary-500 truncate">
                                    {{ $t('Avg. First Response Time') }}
                                </dt>
                                <dd class="mt-1 text-2xl font-semibold text-secondary-900">
                                    {{ currentMonthStats.avg_response_time }} {{ $t('hours') }}
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden rounded-lg border border-secondary-200 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                                <svg-vue class="h-6 w-6 text-green-600" icon="font-awesome.check-circle-regular"></svg-vue>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-secondary-500 truncate">
                                    {{ $t('Avg. Resolution Time') }}
                                </dt>
                                <dd class="mt-1 text-2xl font-semibold text-secondary-900">
                                    {{ currentMonthStats.avg_resolution_time }} {{ $t('hours') }}
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden rounded-lg border border-secondary-200 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-full p-3 mr-4">
                                <svg-vue class="h-6 w-6 text-blue-600" icon="font-awesome.percentage-regular"></svg-vue>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-secondary-500 truncate">
                                    {{ $t('First Response Rate') }}
                                </dt>
                                <dd class="mt-1 text-2xl font-semibold text-secondary-900">
                                    {{ currentMonthStats.first_response_rate }}%
                                </dd>
                                <p class="text-xs text-secondary-500">
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
