<template>
    <div class="min-h-screen flex items-center justify-center bg-secondary-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="flex justify-center">
                    <div v-if="status === 'success'" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <svg-vue class="h-6 w-6 text-green-600" icon="font-awesome/check-circle-solid"></svg-vue>
                    </div>
                    <div v-else-if="status === 'already_verified'" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary-100">
                        <svg-vue class="h-6 w-6 text-primary-600" icon="font-awesome/info-circle-solid"></svg-vue>
                    </div>
                    <div v-else class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg-vue class="h-6 w-6 text-red-600" icon="font-awesome/exclamation-circle-solid"></svg-vue>
                    </div>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-secondary-900">
                    {{ getTitle() }}
                </h2>
                <p class="mt-2 text-center text-sm text-secondary-600">
                    {{ getMessage() }}
                </p>
            </div>
            <div class="flex justify-center">
                <router-link
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 max-w-xs"
                    to="/auth/login"
                >
                    {{ $t('Go to Login') }}
                </router-link>
            </div>
            <div v-if="status === 'error'" class="flex justify-center mt-4">
                <router-link
                    class="text-sm font-medium text-primary-600 hover:text-primary-500"
                    to="/auth/verify-email"
                >
                    {{ $t('Request a new verification link') }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "email-verified",
    metaInfo() {
        return {
            title: this.$i18n.t('Email Verification')
        }
    },
    data() {
        return {
            status: 'success'
        }
    },
    mounted() {
        // Get status from URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'already_verified') {
            this.status = 'already_verified';
        } else if (status === 'success') {
            this.status = 'success';
        } else {
            this.status = 'error';
        }
    },
    methods: {
        getTitle() {
            switch (this.status) {
                case 'success':
                    return this.$t('Email Verified!');
                case 'already_verified':
                    return this.$t('Email Already Verified');
                default:
                    return this.$t('Verification Failed');
            }
        },
        getMessage() {
            switch (this.status) {
                case 'success':
                    return this.$t('Your email has been successfully verified. You can now log in to your account.');
                case 'already_verified':
                    return this.$t('Your email was already verified. You can log in to your account.');
                default:
                    return this.$t('There was a problem verifying your email. Please request a new verification link.');
            }
        }
    }
}
</script>
