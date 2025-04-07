<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('Edit Condo Location') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button
                        class="btn btn-red shadow-sm rounded-md mr-2"
                        type="button"
                        @click="deleteCondoLocationModal = true"
                    >
                        {{ $t('Delete Condo Location') }}
                    </button>
                    <router-link
                        class="btn btn-white shadow-sm rounded-md"
                        to="/dashboard/admin/condo-locations"
                    >
                        {{ $t('Back to List') }}
                    </router-link>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="my-6 bg-white shadow overflow-hidden sm:rounded-md">
                <loading :status="loading"/>
                <form @submit.prevent="updateCondoLocation" class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium leading-5 text-gray-700" for="name">
                            {{ $t('Name') }}
                        </label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input
                                id="name"
                                v-model="condoLocation.name"
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
                                    v-model="condoLocation.status"
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
                                    v-model="condoLocation.status"
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
                    <div class="mt-8 border-t border-gray-200 pt-5">
                        <div class="flex justify-end">
                            <span class="inline-flex rounded-md shadow-sm">
                                <router-link
                                    to="/dashboard/admin/condo-locations"
                                    class="py-2 px-4 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out"
                                >
                                    {{ $t('Cancel') }}
                                </router-link>
                            </span>
                            <span class="ml-3 inline-flex rounded-md shadow-sm">
                                <button
                                    id="submit-condo-location"
                                    type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out"
                                    data-style="zoom-in"
                                >
                                    {{ $t('Update') }}
                                </button>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteCondoLocationModal" class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg-vue class="h-6 w-6 text-red-600" icon="font-awesome.exclamation-circle-solid"></svg-vue>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $t('Delete Condo Location') }}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ $t('Are you sure you want to delete this condo location?') }}
                                        {{ $t('All data will be permanently removed') }}.
                                        {{ $t('This action cannot be undone') }}.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="deleteCondoLocation"
                        >
                            {{ $t('Delete') }}
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="deleteCondoLocationModal = false"
                        >
                            {{ $t('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "condo-locations-edit",
    metaInfo() {
        return {
            title: this.$i18n.t('Edit Condo Location')
        }
    },
    data() {
        return {
            loading: true,
            deleteCondoLocationModal: false,
            condoLocation: {
                id: null,
                name: '',
                status: true
            }
        }
    },
    mounted() {
        this.getCondoLocation();
    },
    methods: {
        getCondoLocation() {
            const self = this;
            self.loading = true;
            axios.get('api/dashboard/admin/condo-locations/' + this.$route.params.id)
                .then(function (response) {
                    self.condoLocation = response.data.data;
                    self.loading = false;
                })
                .catch(function () {
                    self.loading = false;
                    self.$router.push('/dashboard/admin/condo-locations');
                });
        },
        updateCondoLocation() {
            const self = this;
            const ladda = Ladda.create(document.querySelector('#submit-condo-location'));
            ladda.start();
            self.loading = true;

            axios.put('api/dashboard/admin/condo-locations/' + this.condoLocation.id, this.condoLocation)
                .then(function (response) {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: response.data.message,
                        type: 'success'
                    });
                    self.$router.push('/dashboard/admin/condo-locations');
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
        },
        deleteCondoLocation() {
            const self = this;
            self.loading = true;
            axios.delete('api/dashboard/admin/condo-locations/' + this.condoLocation.id)
                .then(function () {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: self.$i18n.t('Condo location deleted successfully').toString(),
                        type: 'success'
                    });
                    self.$router.push('/dashboard/admin/condo-locations');
                })
                .catch(function (error) {
                    self.loading = false;
                    self.deleteCondoLocationModal = false;
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
</script>
