<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <!-- Header Section -->
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-5">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-primary-700">{{ $t('Edit FAQ') }}</h1>
                    <p class="mt-2 text-sm text-secondary-600">{{ $t('Update the details of this FAQ entry.') }}</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-2">
                    <button
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                        type="button"
                        @click="deleteFaqModal = true"
                        :disabled="loadingInitialData"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        {{ $t('Delete FAQ') }}
                    </button>
                    <router-link
                        class="inline-flex items-center px-4 py-2 border border-secondary-300 rounded-md shadow-sm text-sm font-medium text-secondary-700 bg-white hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                        to="/dashboard/admin/faqs"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        {{ $t('Back to List') }}
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <loading :status="loadingInitialData"/>

                <!-- Global Success Message -->
                <div v-if="successMessage" class="bg-green-50 border-l-4 border-green-400 p-4 m-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ successMessage }}</p>
                        </div>
                    </div>
                </div>

                <!-- Global Error Message -->
                <div v-if="errorMessage" class="bg-red-50 border-l-4 border-red-400 p-4 m-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ errorMessage }}</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="updateFaq" class="p-8 space-y-8" v-if="!loadingInitialData && form.id">
                    <!-- Question Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-secondary-800" for="question">
                            {{ $t('Question') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="question"
                            v-model="form.question"
                            rows="3"
                            :class="['block w-full px-4 py-3 border rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent', formErrors.question ? 'border-red-300 bg-red-50 text-red-900 placeholder-red-400' : 'border-secondary-300 bg-white text-secondary-900 placeholder-secondary-500 hover:border-secondary-400']"
                            :placeholder="$t('Enter the FAQ question')"
                        ></textarea>
                        <p v-if="formErrors.question" class="text-sm text-red-600">{{ formErrors.question }}</p>
                    </div>

                    <!-- Answer Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-secondary-800" for="answer">
                            {{ $t('Answer') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="answer"
                            v-model="form.answer"
                            rows="5"
                            :class="['block w-full px-4 py-3 border rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent', formErrors.answer ? 'border-red-300 bg-red-50 text-red-900 placeholder-red-400' : 'border-secondary-300 bg-white text-secondary-900 placeholder-secondary-500 hover:border-secondary-400']"
                            :placeholder="$t('Enter the FAQ answer')"
                        ></textarea>
                        <p v-if="formErrors.answer" class="text-sm text-red-600">{{ formErrors.answer }}</p>
                    </div>

                    <!-- Category Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-secondary-800" for="category">
                            {{ $t('Category') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="category"
                            v-model="form.category"
                            :class="['block w-full px-4 py-3 border rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent', formErrors.category ? 'border-red-300 bg-red-50 text-red-900' : 'border-secondary-300 bg-white text-secondary-900 hover:border-secondary-400']"
                        >
                            <option :value="null" disabled>{{ $t('Select a category') }}</option>
                            <option value="wifi">{{ $t('WiFi Helpdesk') }}</option>
                            <option value="general">{{ $t('General Helpdesk') }}</option>
                        </select>
                        <p v-if="formErrors.category" class="text-sm text-red-600">{{ formErrors.category }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 border-t border-secondary-200 pt-5">
                        <div class="flex justify-end space-x-3">
                            <router-link
                                to="/dashboard/admin/faqs"
                                class="py-2 px-4 border border-secondary-300 rounded-md text-sm leading-5 font-medium text-secondary-700 hover:bg-secondary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-400 active:bg-secondary-200 active:text-secondary-800 transition duration-150 ease-in-out"
                            >
                                {{ $t('Cancel') }}
                            </router-link>
                            <button
                                id="submit-faq-update"
                                type="submit"
                                :disabled="saving"
                                class="inline-flex items-center justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 active:bg-primary-800 transition duration-150 ease-in-out"
                            >
                                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ saving ? $t('Updating...') : $t('Update FAQ') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteFaqModal" class="fixed z-20 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg-vue class="h-6 w-6 text-red-600" icon="font-awesome/exclamation-circle-solid"></svg-vue>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $t('Delete FAQ') }}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ $t('Are you sure you want to delete this FAQ?') }}
                                        {{ $t('This action cannot be undone.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="confirmDeleteFaq"
                            :disabled="deleting"
                        >
                            <svg v-if="deleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ deleting ? $t('Deleting...') : $t('Delete') }}
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="deleteFaqModal = false"
                            :disabled="deleting"
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
    name: "EditFaq",
    metaInfo() {
        return {
            title: this.$i18n.t('Edit FAQ')
        }
    },
    data() {
        return {
            loadingInitialData: true,
            saving: false,
            deleting: false,
            deleteFaqModal: false,
            form: {
                id: null,
                question: '',
                answer: '',
                category: null,
            },
            formErrors: {},
            successMessage: '',
            errorMessage: '',
        };
    },
    mounted() {
        this.fetchFaqDetails();
    },
    methods: {
        async fetchFaqDetails() {
            this.loadingInitialData = true;
            this.successMessage = '';
            this.errorMessage = '';
            const faqId = this.$route.params.id;

            try {
                const response = await axios.get(`/api/dashboard/admin/faqs/${faqId}`);
                const faqData = response.data.data;
                this.form.id = faqData.id;
                this.form.question = faqData.question;
                this.form.answer = faqData.answer;
                this.form.category = faqData.category;
            } catch (error) {
                console.error("Error fetching FAQ details:", error);
                this.errorMessage = error.response?.data?.message || this.$i18n.t('Could not load FAQ details.');
                this.$notify({
                    title: this.$i18n.t('Error').toString(),
                    text: this.errorMessage,
                    type: 'error'
                });
                this.$router.push('/dashboard/admin/faqs'); // Redirect if FAQ not found or error
            } finally {
                this.loadingInitialData = false;
            }
        },
        validateForm() {
            this.formErrors = {};
            let isValid = true;
            if (!this.form.question.trim()) {
                this.formErrors.question = this.$i18n.t('Question is required.');
                isValid = false;
            }
            if (!this.form.answer.trim()) {
                this.formErrors.answer = this.$i18n.t('Answer is required.');
                isValid = false;
            }
            if (!this.form.category) {
                this.formErrors.category = this.$i18n.t('Category is required.');
                isValid = false;
            }
            return isValid;
        },
        async updateFaq() {
            this.successMessage = '';
            this.errorMessage = '';
            if (!this.validateForm()) {
                 this.$notify({
                    title: this.$i18n.t('Validation Error').toString(),
                    text: this.$i18n.t('Please correct the errors in the form.'),
                    type: 'error'
                });
                return;
            }

            this.saving = true;
            let payload = {
                question: this.form.question,
                answer: this.form.answer,
                category: this.form.category,
            };

            try {
                const response = await axios.put(`/api/dashboard/admin/faqs/${this.form.id}`, payload);
                this.successMessage = response.data.message || this.$i18n.t('FAQ updated successfully!');
                this.$notify({
                    title: this.$i18n.t('Success').toString(),
                    text: this.successMessage,
                    type: 'success'
                });
                // Optionally, refresh data or redirect
                // this.fetchFaqDetails(); // to show updated data if staying on page
                this.$router.push('/dashboard/admin/faqs');
            } catch (error) {
                console.error("Error updating FAQ:", error);
                if (error.response && error.response.data) {
                    if (error.response.data.errors) {
                        const errors = error.response.data.errors;
                        for (const key in errors) {
                            if (Object.hasOwnProperty.call(errors, key)) {
                                this.formErrors[key] = errors[key][0];
                            }
                        }
                        this.errorMessage = this.$i18n.t('Please correct the validation errors.');
                    } else {
                        this.errorMessage = error.response.data.message || this.$i18n.t('An unexpected error occurred while saving.');
                    }
                } else {
                    this.errorMessage = this.$i18n.t('A network error occurred. Please try again.');
                }
                 this.$notify({
                    title: this.$i18n.t('Error').toString(),
                    text: this.errorMessage,
                    type: 'error'
                });
            } finally {
                this.saving = false;
            }
        },
        confirmDeleteFaq() {
            this.deleting = true;
            axios.delete(`/api/dashboard/admin/faqs/${this.form.id}`)
                .then(() => {
                    this.$notify({
                        title: this.$i18n.t('Success').toString(),
                        text: this.$i18n.t('FAQ deleted successfully').toString(),
                        type: 'success'
                    });
                    this.$router.push('/dashboard/admin/faqs');
                })
                .catch(error => {
                    this.errorMessage = error.response?.data?.message || this.$i18n.t('Could not delete FAQ.');
                    this.$notify({ title: this.$i18n.t('Error').toString(), text: this.errorMessage, type: 'error' });
                })
                .finally(() => {
                    this.deleting = false;
                    this.deleteFaqModal = false;
                });
        }
    }
};
</script>

<style scoped>
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>
