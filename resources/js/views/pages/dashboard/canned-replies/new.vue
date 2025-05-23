<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <form @submit.prevent="saveCannedReply">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="py-0.5 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $t('Create canned reply') }}</h1>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <router-link
                            class="btn btn-white dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 shadow-sm rounded-md"
                            to="/dashboard/canned-replies"
                        >
                            {{ $t('Back to List') }}
                        </router-link>
                    </div>
                </div>
            </div>
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mt-6 bg-white dark:bg-gray-800 shadow-xl overflow-hidden sm:rounded-lg">
                    <loading :status="loading"/>
                    <div v-if="!loading" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="name">{{ $t('Name') }} <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <input
                                    id="name"
                                    v-model="cannedReply.name"
                                    :placeholder="$t('Enter a descriptive name')"
                                    class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                                    required
                                >
                            </div>
                            <p v-if="formErrors.name" class="mt-1 text-xs text-red-500">{{ formErrors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300" for="body">{{ $t('Body') }} <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <input-wysiwyg
                                    id="body"
                                    v-model="cannedReply.body"
                                    class="dark:bg-gray-700 dark:text-gray-200"
                                />
                                <p v-if="formErrors.body" class="mt-1 text-xs text-red-500">{{ formErrors.body }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300" for="shared">{{ $t('Visibility') }}</label>
                            <input-switch
                                id="shared"
                                v-model="cannedReply.shared"
                                :disabled-label="$t('Private (only you can use this)')"
                                :enabled-label="$t('Shared (others in your context can use this)')"
                                class="mt-2"
                            ></input-switch>
                            <p v-if="formErrors.shared" class="mt-1 text-xs text-red-500">{{ formErrors.shared }}</p>
                        </div>
                    </div>
                    <div v-if="!loading" class="bg-gray-50 dark:bg-gray-750 text-right px-4 py-3 sm:px-6">
                        <div class="inline-flex space-x-3">
                            <router-link
                                class="btn btn-secondary dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 shadow-sm rounded-md"
                                to="/dashboard/canned-replies"
                            >
                                {{ $t('Cancel') }}
                            </router-link>
                            <button
                                class="btn btn-green shadow-sm rounded-md"
                                type="submit"
                                :disabled="saving"
                            >
                                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ saving ? $t('Saving...') : $t('Create canned reply') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
</template>

<script>
export default {
    name: "canned-reply-new", // Changed name for clarity
    metaInfo() {
        return {
            title: this.$i18n.t('Create canned reply')
        }
    },
    data() {
        return {
            loading: false, // No initial data loading needed for a new form
            saving: false,
            cannedReply: {
                name: '', // Initialize as empty string
                shared: true,
                body: '',
            },
            formErrors: {}
        }
    },
    methods: {
        saveCannedReply() {
            const self = this;
            self.formErrors = {}; // Clear previous errors

            // Basic Validation
            if (!self.cannedReply.name || self.cannedReply.name.trim() === '') {
                self.formErrors.name = self.$i18n.t('Name is required');
            }
            if (!self.cannedReply.body || self.cannedReply.body.trim() === '') {
                self.formErrors.body = self.$i18n.t('Body is required');
            }

            if (Object.keys(self.formErrors).length > 0) {
                self.$notify({
                    title: self.$i18n.t('Validation Error').toString(),
                    text: self.$i18n.t('Please correct the errors in the form.').toString(),
                    type: 'error'
                });
                return;
            }

            self.saving = true;
            axios.post('api/dashboard/canned-replies', self.cannedReply).then(function (response) {
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: response.data.message || self.$i18n.t('Data saved correctly').toString(),
                    type: 'success'
                });
                // Redirect to edit page of the newly created canned reply
                self.$router.push('/dashboard/canned-replies/' + response.data.cannedReply.id + '/edit');
            }).catch(function (error) {
                if (error.response && error.response.data) {
                    if (error.response.data.errors) {
                        self.formErrors = error.response.data.errors;
                         const firstErrorKey = Object.keys(error.response.data.errors)[0];
                         const firstErrorMessage = error.response.data.errors[firstErrorKey][0];
                        self.$notify({
                            title: self.$i18n.t('Validation Error').toString(),
                            text: firstErrorMessage || self.$i18n.t('Please correct the errors in the form.'),
                            type: 'error'
                        });
                    } else if (error.response.data.message) {
                         self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: error.response.data.message,
                            type: 'error'
                        });
                    } else {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: self.$i18n.t('An unexpected error occurred.'),
                            type: 'error'
                        });
                    }
                } else {
                     self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: self.$i18n.t('An network error occurred.'),
                        type: 'error'
                    });
                }
            }).finally(function () {
                self.saving = false;
            });
        },
    }
}
</script>

<style scoped>
/* Add any specific styles if needed, or rely on global/tailwind styles */
.form-input {
    /* Tailwind classes are mostly used, but ensure dark mode compatibility if not covered by global styles */
}
</style>
