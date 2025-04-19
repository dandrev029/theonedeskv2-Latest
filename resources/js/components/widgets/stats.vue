<template>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.open_tickets == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-primary-100 rounded-full p-3 mr-4">
                    <svg-vue class="h-6 w-6 text-primary-600" icon="font-awesome.ticket-alt-regular"></svg-vue>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Open tickets') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.open_tickets ? stats.open_tickets : 0 }}
                    </dd>
                </div>
            </div>
        </div>
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.pending_tickets == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3 mr-4">
                    <svg-vue class="h-6 w-6 text-yellow-600" icon="font-awesome.clock-regular"></svg-vue>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Pending tickets') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.pending_tickets ? stats.pending_tickets : 0 }}
                    </dd>
                </div>
            </div>
        </div>
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.solved_tickets == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                    <svg-vue class="h-6 w-6 text-green-600" icon="font-awesome.check-circle-regular"></svg-vue>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Solved tickets') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.solved_tickets ? stats.solved_tickets : 0 }}
                    </dd>
                </div>
            </div>
        </div>
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.without_agent == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-full p-3 mr-4">
                    <svg-vue class="h-6 w-6 text-red-600" icon="font-awesome.user-slash-regular"></svg-vue>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Without assign agent') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.without_agent ? stats.without_agent : 0 }}
                    </dd>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "stats",
    data() {
        return {
            stats: {
                open_tickets: null,
                pending_tickets: null,
                solved_tickets: null,
                without_agent: null,
            }
        }
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            const self = this;
            axios.get('api/dashboard/stats/count').then(function (response) {
                self.stats = response.data;
            });
        }
    },
}
</script>
