<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('Ticket Concerns') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <router-link
                        class="btn btn-blue shadow-sm rounded-md"
                        to="/dashboard/admin/ticket-concerns/new"
                    >
                        {{ $t('Create Ticket Concern') }}
                    </router-link>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="my-6 bg-white shadow overflow-hidden sm:rounded-md">
                <loading :status="loading"/>
                <template v-if="ticketConcerns.length > 0">
                    <ul>
                        <template v-for="(ticketConcern, index) in ticketConcerns">
                            <li :key="ticketConcern.id" :class="{'border-t border-gray-200': index !== 0}">
                                <router-link
                                    :to="'/dashboard/admin/ticket-concerns/' + ticketConcern.id + '/edit'"
                                    class="block hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                >
                                    <div class="px-4 py-4 flex items-center sm:px-6">
                                        <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <div class="text-sm font-medium leading-5 text-gray-900 truncate">
                                                    {{ ticketConcern.name }}
                                                </div>
                                                <div class="mt-2 flex items-center text-sm leading-5 text-gray-500">
                                                    <span v-if="ticketConcern.status" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ $t('Active') }}
                                                    </span>
                                                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        {{ $t('Inactive') }}
                                                    </span>
                                                    <span class="ml-2">{{ $t('Created') }}: {{ formatDate(ticketConcern.created_at) }}</span>
                                                </div>
                                                <div v-if="ticketConcern.department" class="mt-2 flex items-center text-sm leading-5 text-gray-500">
                                                    <span class="flex items-center">
                                                        <span class="mr-1">{{ $t('Department') }}:</span>
                                                        <span>{{ ticketConcern.department.name }}</span>
                                                    </span>
                                                </div>
                                                <div v-if="ticketConcern.assigned_user" class="mt-2 flex items-center text-sm leading-5 text-gray-500">
                                                    <span class="flex items-center">
                                                        <span class="mr-1">{{ $t('Assigned to') }}:</span>
                                                        <img
                                                            :src="ticketConcern.assigned_user.avatar || ticketConcern.assigned_user.gravatar"
                                                            :alt="ticketConcern.assigned_user.name"
                                                            class="h-4 w-4 rounded-full mr-1"
                                                        >
                                                        <span>{{ ticketConcern.assigned_user.name }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-5 flex items-center space-x-4">
                                            <button
                                                @click.stop.prevent="deleteTicketConcern(ticketConcern)"
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
                                    <div class="w-full font-semibold text-2xl">{{ $t('No records found') }}</div>
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
    name: "ticket-concerns-list",
    metaInfo() {
        return {
            title: this.$i18n.t('Ticket Concerns')
        }
    },
    data() {
        return {
            loading: true,
            ticketConcerns: []
        }
    },
    mounted() {
        this.getTicketConcerns();
    },
    methods: {
        getTicketConcerns() {
            const self = this;
            self.loading = true;
            axios.get('api/dashboard/admin/ticket-concerns').then(function (response) {
                self.ticketConcerns = response.data.data;
                self.loading = false;
            }).catch(function () {
                self.loading = false;
            });
        },
        formatDate(date) {
            return moment(date).format('LL');
        },
        deleteTicketConcern(ticketConcern) {
            const self = this;
            if (confirm(this.$i18n.t('Are you sure you want to delete this ticket concern?'))) {
                self.loading = true;
                axios.delete('api/dashboard/admin/ticket-concerns/' + ticketConcern.id).then(function () {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: self.$i18n.t('Ticket concern deleted successfully').toString(),
                        type: 'success'
                    });
                    self.getTicketConcerns();
                }).catch(function (error) {
                    self.loading = false;
                    if (error.response && error.response.data && error.response.data.message) {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: error.response.data.message,
                            type: 'error'
                        });
                    }
                });
            }
        }
    }
}
</script>
