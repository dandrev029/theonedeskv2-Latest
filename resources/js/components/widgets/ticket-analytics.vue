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
                                <svg class="h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M224 352L196.8 52.3C194.2 24.2 216.3 0 244.6 0L534.1 0c21.1 0 36.4 20.1 30.9 40.4L558.5 64 400 64c-8.8 0-16 7.2-16 16s7.2 16 16 16l149.8 0-17.5 64L400 160c-8.8 0-16 7.2-16 16s7.2 16 16 16l123.6 0-17.5 64L400 256c-8.8 0-16 7.2-16 16s7.2 16 16 16l97.5 0L480 352l-256 0zm-16 32l288 0c26.5 0 48 21.5 48 48l0 32c0 26.5-21.5 48-48 48l-288 0c-26.5 0-48-21.5-48-48l0-32c0-26.5 21.5-48 48-48zm144 96a32 32 0 1 0 0-64 32 32 0 1 0 0 64zM147.5 30.7c10.8 6.7 15.3 21 10.6 33.4l-22 57.8c-4.2 10.9-14.5 17.6-25.3 16.4l-33.3-3.6c-13.6 42.2-13.6 88.4 0 130.7l33.3-3.6c10.9-1.2 21.2 5.5 25.3 16.4l22 57.8c4.7 12.4 .2 26.7-10.6 33.4l-44 27.2c-9.7 6-21.9 4.2-29.8-4.3C-24.6 286-24.6 114 73.7 7.8C81.6-.7 93.8-2.5 103.5 3.5l44 27.2z"/></svg>
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
                                <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288l111.5 0L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7l-111.5 0L349.4 44.6z"/></svg>
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
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"/></svg>
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
