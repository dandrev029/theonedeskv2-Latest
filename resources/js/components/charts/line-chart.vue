<script>
import {Line, mixins} from 'vue-chartjs'

const {reactiveProp} = mixins;

export default {
    name: 'line-chart',
    extends: Line,
    mixins: [reactiveProp],
    props: {
        chartData: {
            type: Object,
            required: true,
        },
        options: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            defaultOptions: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: this.$store.state.darkMode ? '#e5e7eb' : '#4b5563'
                        },
                        gridLines: {
                            color: this.$store.state.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            fontColor: this.$store.state.darkMode ? '#e5e7eb' : '#4b5563'
                        },
                        gridLines: {
                            color: this.$store.state.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    }]
                },
                legend: {
                    display: false,
                    labels: {
                        fontColor: this.$store.state.darkMode ? '#e5e7eb' : '#4b5563'
                    }
                },
                tooltips: {
                    backgroundColor: this.$store.state.darkMode ? '#374151' : '#fff',
                    titleFontColor: this.$store.state.darkMode ? '#f9fafb' : '#111827',
                    bodyFontColor: this.$store.state.darkMode ? '#e5e7eb' : '#4b5563',
                    borderColor: this.$store.state.darkMode ? '#4b5563' : '#e5e7eb',
                    borderWidth: 1
                },
                animation: false,
                responsive: true,
                maintainAspectRatio: false
            }
        }
    },
    mounted() {
        // Use custom options if provided, otherwise use default options
        const chartOptions = this.options || this.defaultOptions;
        this.renderChart(this.chartData, chartOptions);
    },
    methods: {
        update() {
            this.$data._chart.update();
        }
    }
}
</script>
