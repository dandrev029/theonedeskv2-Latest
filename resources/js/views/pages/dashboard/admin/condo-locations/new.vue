<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('New Condo Location') }}</h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
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
                <form @submit.prevent="saveCondoLocation" class="p-6">
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
    name: "condo-locations-new",
    metaInfo() {
        return {
            title: this.$i18n.t('New Condo Location')
        }
    },
    data() {
        return {
            loading: false,
            condoLocation: {
                name: '',
                status: true
            }
        }
    },
    methods: {
        saveCondoLocation() {
            const self = this;
            const ladda = Ladda.create(document.querySelector('#submit-condo-location'));
            ladda.start();
            self.loading = true;

            axios.post('api/dashboard/admin/condo-locations', this.condoLocation)
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
        }
    }
}
</script>
