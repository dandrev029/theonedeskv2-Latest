<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <form @submit.prevent="saveCannedReply">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 px-4">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="py-0.5 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $t('Edit canned reply') }}</h1>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4 space-x-2">
                         <router-link
                            class="btn btn-white dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 shadow-sm rounded-md"
                            to="/dashboard/canned-replies"
                        >
                            {{ $t('Back to List') }}
                        </router-link>
                        <button
                            v-if="cannedReply.author"
                            class="btn btn-red shadow-sm rounded-md"
                            type="button"
                            @click="deleteCannedReplyModal = true"
                        >
                            {{ $t('Delete') }}
                        </button>
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
                                    :readonly="!cannedReply.author"
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
                                    :readonly="!cannedReply.author"
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
                                :readonly="!cannedReply.author"
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
                                v-if="cannedReply.author"
                                class="btn btn-green shadow-sm rounded-md"
                                type="submit"
                                :disabled="saving"
                            >
                                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ saving ? $t('Saving...') : $t('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div v-show="deleteCannedReplyModal && cannedReply.author" class="fixed z-20 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <transition
                    duration="300"
                    enter-active-class="ease-out duration-300"
                    enter-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="deleteCannedReplyModal" class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                </transition>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="deleteCannedReplyModal"
                        aria-labelledby="modal-headline"
                        aria-modal="true"
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                        role="dialog"
                    >
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg-vue class="h-6 w-6 pb-1 text-red-600" icon="font-awesome/exclamation-triangle-light"></svg-vue>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 id="modal-headline" class="text-lg leading-6 font-medium text-gray-900">
                                        {{ $t('Delete canned reply') }}
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm leading-5 text-gray-500">
                                            {{ $t('Are you sure you want to delete the canned reply?') }}
                                            {{ $t('All data will be permanently removed') }}.
                                            {{ $t('All related data will be deleted') }}.
                                            {{ $t('This action cannot be undone') }}.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                class="btn btn-red mr-2 sm:mr-0"
                                type="button"
                                @click="deleteCannedReply"
                            >
                                {{ $t('Delete canned reply') }}
                            </button>
                            <button
                                class="btn btn-white mr-0 sm:mr-2"
                                type="button"
                                @click="deleteCannedReplyModal = false"
                            >
                                {{ $t('Cancel') }}
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "edit",
    metaInfo() {
        return {
            title: this.$i18n.t('Edit canned reply')
        }
    },
    data() {
        return {
            loading: false,
            deleteCannedReplyModal: false,
            cannedReply: {
                name: null,
                shared: true,
                body: '',
                author: false,
            },
            formErrors: {},
            saving: false,
        }
    },
    mounted() {
        this.getCannedReply();
    },
    methods: {
        saveCannedReply() {
            const self = this;
            self.loading = true;
            axios.put('api/dashboard/canned-replies/' + self.$route.params.id, self.cannedReply).then(function () {
                self.loading = false;
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: self.$i18n.t('Data updated correctly').toString(),
                    type: 'success'
                });
            }).catch(function () {
                self.loading = false;
            });
        },
        getCannedReply() {
            const self = this;
            self.loading = true;
            axios.get('api/dashboard/canned-replies/' + self.$route.params.id).then(function (response) {
                self.cannedReply = response.data;
                self.loading = false;
            }).catch(function () {
                self.loading = false;
            });
        },
        deleteCannedReply() {
            const self = this;
            axios.delete('api/dashboard/canned-replies/' + self.$route.params.id).then(function () {
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: self.$i18n.t('Data deleted successfully').toString(),
                    type: 'success'
                });
                self.$router.push('/dashboard/canned-replies');
            }).catch(function () {
                self.deleteCannedReplyModal = false;
            });
        },
    }
}
</script>
