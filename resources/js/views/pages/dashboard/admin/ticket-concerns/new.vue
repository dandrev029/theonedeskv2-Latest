<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('New Ticket Concern') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <router-link
                        class="btn btn-white shadow-sm rounded-md"
                        to="/dashboard/admin/ticket-concerns"
                    >
                        {{ $t('Back to List') }}
                    </router-link>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="my-6 bg-white shadow overflow-hidden sm:rounded-md">
                <loading :status="loading"/>
                <form @submit.prevent="saveTicketConcern" class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium leading-5 text-gray-700" for="name">
                            {{ $t('Name') }}
                        </label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input
                                id="name"
                                v-model="ticketConcern.name"
                                class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                type="text"
                                required
                            >
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium leading-5 text-gray-700">
                            {{ $t('Status') }}
                        </label>
                        <div class="mt-2">
                            <div class="flex items-center">
                                <input
                                    id="status-active"
                                    v-model="ticketConcern.status"
                                    name="status"
                                    type="radio"
                                    :value="true"
                                    class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                                >
                                <label for="status-active" class="ml-3">
                                    <span class="block text-sm leading-5 text-gray-700">{{ $t('Active') }}</span>
                                </label>
                            </div>
                            <div class="mt-2 flex items-center">
                                <input
                                    id="status-inactive"
                                    v-model="ticketConcern.status"
                                    name="status"
                                    type="radio"
                                    :value="false"
                                    class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                                >
                                <label for="status-inactive" class="ml-3">
                                    <span class="block text-sm leading-5 text-gray-700">{{ $t('Inactive') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium leading-5 text-gray-700" for="department_id">
                            {{ $t('Department') }}
                        </label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input-select-scrollable
                                id="department_id"
                                v-model="ticketConcern.department_id"
                                :options="departments"
                                option-label="name"
                                :placeholder="$t('Select a department for this concern')"
                            />
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ $t('Select the department that this concern belongs to.') }}
                        </p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium leading-5 text-gray-700" for="assigned_to">
                            {{ $t('Assigned To') }}
                        </label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input-select-scrollable
                                id="assigned_to"
                                v-model="ticketConcern.assigned_to"
                                :options="dashboardUsers"
                                option-label="name"
                                :placeholder="$t('Select a user to handle this concern')"
                            >
                                <template v-slot:option="{ option }">
                                    <div class="flex items-center">
                                        <img
                                            :src="option.avatar || option.gravatar"
                                            :alt="option.name"
                                            class="h-6 w-6 rounded-full mr-2"
                                        >
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ option.name }}</div>
                                            <div class="text-xs text-gray-500">{{ option.email }}</div>
                                        </div>
                                    </div>
                                </template>
                            </input-select-scrollable>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ $t('Select a user with dashboard access who will handle tickets with this concern category.') }}
                        </p>
                    </div>
                    <div class="mt-8 border-t border-gray-200 pt-5">
                        <div class="flex justify-end">
                            <span class="inline-flex rounded-md shadow-sm">
                                <router-link
                                    to="/dashboard/admin/ticket-concerns"
                                    class="py-2 px-4 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out"
                                >
                                    {{ $t('Cancel') }}
                                </router-link>
                            </span>
                            <span class="ml-3 inline-flex rounded-md shadow-sm">
                                <button
                                    id="submit-ticket-concern"
                                    type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out"
                                    data-style="zoom-in"
                                >
                                    {{ $t('Save') }}
                                </button>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "ticket-concerns-new",
    metaInfo() {
        return {
            title: this.$i18n.t('New Ticket Concern')
        }
    },
    data() {
        return {
            loading: false,
            dashboardUsers: [],
            departments: [],
            ticketConcern: {
                name: '',
                status: true,
                assigned_to: null,
                department_id: null
            }
        }
    },
    mounted() {
        this.getDashboardUsers();
        this.getDepartments();
    },
    methods: {
        getDashboardUsers() {
            const self = this;
            self.loading = true;
            axios.get('api/dashboard/admin/ticket-concerns/users/dashboard')
                .then(function (response) {
                    self.dashboardUsers = response.data.data;
                    self.loading = false;
                })
                .catch(function () {
                    self.loading = false;
                });
        },
        getDepartments() {
            const self = this;
            self.loading = true;
            console.log('Fetching departments...');
            axios.get('api/dashboard/admin/ticket-concerns/departments')
                .then(function (response) {
                    console.log('Departments response:', response.data);
                    if (response.data && response.data.data) {
                        self.departments = response.data.data;
                    } else if (Array.isArray(response.data)) {
                        self.departments = response.data;
                    } else {
                        console.error('Unexpected response format for departments');
                        self.departments = [];
                    }
                    self.loading = false;
                })
                .catch(function (error) {
                    console.error('Error fetching departments:', error);
                    self.loading = false;
                });
        },
        saveTicketConcern() {
            const self = this;
            const ladda = Ladda.create(document.querySelector('#submit-ticket-concern'));
            ladda.start();
            self.loading = true;

            axios.post('api/dashboard/admin/ticket-concerns', this.ticketConcern)
                .then(function (response) {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: response.data.message,
                        type: 'success'
                    });
                    self.$router.push('/dashboard/admin/ticket-concerns');
                })
                .catch(function (error) {
                    self.loading = false;
                    if (error.response && error.response.data && error.response.data.message) {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: error.response.data.message,
                            type: 'error'
                        });
                    }
                    ladda.stop();
                });
        }
    }
}
</script>
