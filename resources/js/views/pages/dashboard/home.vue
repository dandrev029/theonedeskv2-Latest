<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h1 class="py-0.5 text-2xl font-semibold flex items-center" :class="{'text-secondary-900': !$store.state.darkMode, 'text-white': $store.state.darkMode}">
                <svg-vue class="h-6 w-6 text-primary-600 mr-2" icon="font-awesome/tachometer-alt-regular"></svg-vue>
                {{ $t('Dashboard') }}
            </h1>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <template v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.StatsController']">
                <div class="py-4">
                    <stats/>
                </div>
                <div class="py-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <opened-tickets/>
                    <ticket-analytics/>
                </div>
            </template>
        </div>
    </main>
</template>

<script>
import Stats from "@/components/widgets/stats";
import OpenedTickets from "@/components/widgets/opened-tickets";
import TicketAnalytics from "@/components/widgets/ticket-analytics";

export default {
    name: "home",
    metaInfo() {
        return {
            title: this.$i18n.t('Dashboard')
        }
    },
    components: {TicketAnalytics, OpenedTickets, Stats}
}
</script>
