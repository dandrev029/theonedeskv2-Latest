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
                            beginAtZero: true
                        }
                    }],
                },
                legend: {
                    display: false
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
