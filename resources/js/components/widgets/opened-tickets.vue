<template>
    <div class="flex flex-col bg-white rounded-lg shadow-sm border border-secondary-200">
        <div class="p-4 border-b border-secondary-200">
            <div class="font-semibold text-secondary-800 flex items-center">
                <svg-vue class="h-5 w-5 text-primary-600 mr-2" icon="font-awesome.chart-line-regular"></svg-vue>
                {{ $t('Tickets this month') }} ({{ monthName }} {{ year }})
            </div>
        </div>
        <div class="p-4">
            <loading :status="loading"/>
            <div v-if="!loading" class="flex justify-end mb-2">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded-full bg-red-500 mr-1"></span>
                        <span class="text-xs text-secondary-600">{{ $t('Opened') }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded-full bg-yellow-500 mr-1"></span>
                        <span class="text-xs text-secondary-600">{{ $t('Pending') }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded-full bg-green-500 mr-1"></span>
                        <span class="text-xs text-secondary-600">{{ $t('Solved') }}</span>
                    </div>
                </div>
            </div>
            <line-chart ref="chart" :chart-data="chartData" :height="350" :options="chartOptions"></line-chart>
        </div>
    </div>
</template>

<script>
import LineChart from "@/components/charts/line-chart";

export default {
    name: "opened-tickets",
    components: {LineChart},
    data() {
        return {
            loading: true,
            monthName: '',
            year: '',
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: this.$i18n.t('Opened'),
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderColor: '#ef4444',
                        borderWidth: 2,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#ef4444',
                        data: []
                    },
                    {
                        label: this.$i18n.t('Pending'),
                        backgroundColor: 'rgba(237, 137, 54, 0.1)',
                        borderColor: '#eab308',
                        borderWidth: 2,
                        pointBackgroundColor: '#eab308',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#eab308',
                        data: []
                    },
                    {
                        label: this.$i18n.t('Solved'),
                        backgroundColor: 'rgba(72, 187, 120, 0.1)',
                        borderColor: '#10b981',
                        borderWidth: 2,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#10b981',
                        data: []
                    }
                ],
            },
            chartOptions: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0 // Only show whole numbers
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }]
                },
                legend: {
                    display: false // We're using our custom legend
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        title: function(tooltipItem, data) {
                            const day = data.labels[tooltipItem[0].index];
                            return day + ' ' + this.monthName;
                        }.bind(this),
                        label: function(tooltipItem, data) {
                            const count = tooltipItem.yLabel;
                            const datasetLabel = data.datasets[tooltipItem.datasetIndex].label;
                            return datasetLabel + ': ' + count + ' ' + (count === 1 ? this.$t('ticket') : this.$t('tickets'));
                        }.bind(this)
                    }
                },
                animation: false,
                responsive: true,
                maintainAspectRatio: false
            }
        }
    },
    computed: {
        datasets() {
            return [
                this.chartData.datasets[0].data,
                this.chartData.datasets[1].data,
                this.chartData.datasets[2].data
            ];
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
            axios.get('api/dashboard/stats/opened-tickets').then(function (response) {
                self.chartData.labels = response.data.labels;
                self.chartData.datasets[0].data = response.data.opened_data;
                self.chartData.datasets[1].data = response.data.pending_data;
                self.chartData.datasets[2].data = response.data.solved_data;
                self.monthName = response.data.month_name;
                self.year = response.data.year;
                self.loading = false;
            });
        }
    },
}
</script>
