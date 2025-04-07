<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="flex justify-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                        <svg-vue class="h-6 w-6 text-blue-600" icon="font-awesome.envelope-regular"></svg-vue>
                    </div>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    {{ $t('Verify Your Email Address') }}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    {{ $t('Thanks for signing up! Before getting started, please verify your email address.') }}
                </p>
            </div>

            <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg-vue class="h-5 w-5 text-red-400" icon="font-awesome.exclamation-circle-solid"></svg-vue>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">{{ errorMessage }}</h3>
                    </div>
                </div>
            </div>

            <div class="rounded-md bg-blue-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg-vue class="h-5 w-5 text-blue-400" icon="font-awesome.info-circle-solid"></svg-vue>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            {{ $t('If you did not receive the email, click the button below to request another.') }}
                        </p>
                        <p class="mt-2 text-xs text-blue-700">
                            {{ $t('Note: Only new accounts created after July 1, 2023 require email verification. If you had an account before this date, you can log in without verification.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col space-y-4">
                <button
                    id="resend-verification"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    data-style="zoom-in"
                    @click="resendVerification"
                >
                    {{ $t('Resend Verification Email') }}
                </button>

                <router-link
                    class="group relative w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    to="/auth/login"
                >
                    {{ $t('Back to Login') }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "verify-email",
    metaInfo() {
        return {
            title: this.$i18n.t('Verify Email')
        }
    },
    data() {
        return {
            errorMessage: ''
        }
    },
    mounted() {
        // Check for error parameters in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');

        if (error === 'invalid_link') {
            this.errorMessage = this.$t('The verification link is invalid or has expired.');
        } else if (error === 'user_not_found') {
            this.errorMessage = this.$t('We could not find a user with that email address.');
        } else if (error === 'invalid_token') {
            this.errorMessage = this.$t('The verification token is invalid.');
        }
    },
    methods: {
        resendVerification() {
            const self = this;
            const ladda = Ladda.create(document.querySelector('#resend-verification'));
            ladda.start();

            axios.post('api/auth/email/resend')
                .then(function (response) {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: response.data.message,
                        type: 'success'
                    });
                })
                .catch(function (error) {
                    if (error.response && error.response.data && error.response.data.message) {
                        self.$notify({
                            title: self.$i18n.t('Error').toString(),
                            text: error.response.data.message,
                            type: 'error'
                        });
                    }
                })
                .finally(function () {
                    ladda.stop();
                });
        }
    }
}
</script>
