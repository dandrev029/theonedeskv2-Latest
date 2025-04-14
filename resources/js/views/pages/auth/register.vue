<template>
    <form @submit.prevent="submit">
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="name">{{ $t('Name') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg-vue class="h-5 w-5 text-primary-500" icon="font-awesome.user-regular"></svg-vue>
                </div>
                <input
                    id="name"
                    v-model="user.name"
                    :placeholder="$t('Name')"
                    class="form-input block w-full pl-10 sm:text-sm sm:leading-5 border-secondary-300 focus:border-primary-500 focus:ring-primary-500"
                    required
                />
            </div>
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="email">{{ $t('Email') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg-vue class="h-5 w-5 text-primary-500" icon="font-awesome.envelope-regular"></svg-vue>
                </div>
                <input
                    id="email"
                    v-model="user.email"
                    :placeholder="$t('Email')"
                    class="form-input block w-full pl-10 sm:text-sm sm:leading-5 border-secondary-300 focus:border-primary-500 focus:ring-primary-500"
                    required
                    type="email"
                />
            </div>
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="phone_number">{{ $t('Phone Number') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg-vue class="h-5 w-5 text-primary-500" icon="font-awesome.phone-regular"></svg-vue>
                </div>
                <input
                    id="phone_number"
                    v-model="user.phone_number"
                    :placeholder="$t('Phone Number')"
                    class="form-input block w-full pl-10 sm:text-sm sm:leading-5 border-secondary-300 focus:border-primary-500 focus:ring-primary-500"
                    required
                    type="tel"
                />
            </div>
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="unit_number">{{ $t('Unit Number w/ tower #') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg-vue class="h-5 w-5 text-primary-500" icon="font-awesome.home-regular"></svg-vue>
                </div>
                <input
                    id="unit_number"
                    v-model="user.unit_number"
                    :placeholder="$t('Unit Number w/ tower #')"
                    class="form-input block w-full pl-10 sm:text-sm sm:leading-5 border-secondary-300 focus:border-primary-500 focus:ring-primary-500"
                    required
                />
            </div>
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="condo_location_id">{{ $t('Condo Location') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <input-select
                    v-model="user.condo_location_id"
                    :options="condoLocations"
                    option-label="name"
                    :searchable="true"
                    required
                />
            </div>
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="password">{{ $t('Password') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg-vue class="h-5 w-5 text-primary-500" icon="font-awesome.lock-regular"></svg-vue>
                </div>
                <input
                    id="password"
                    v-model="user.password"
                    class="form-input block w-full pl-10 sm:text-sm sm:leading-5 border-secondary-300 focus:border-primary-500 focus:ring-primary-500"
                    placeholder="******************"
                    required
                    type="password"
                />
            </div>
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="password_confirmation">{{ $t('Confirm password') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg-vue class="h-5 w-5 text-primary-500" icon="font-awesome.lock-regular"></svg-vue>
                </div>
                <input
                    id="password_confirmation"
                    v-model="user.password_confirmation"
                    class="form-input block w-full pl-10 sm:text-sm sm:leading-5 border-secondary-300 focus:border-primary-500 focus:ring-primary-500"
                    placeholder="******************"
                    required
                    type="password"
                />
            </div>
        </div>
        <div class="mb-4 text-right">
            <button id="submit-register" class="bg-primary-600 hover:bg-primary-500 text-white font-bold py-2 px-8 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" data-style="zoom-in" type="submit">
                {{ $t('Create account') }}
            </button>
        </div>
        <p class="text-secondary-700 text-sm">
            {{ $t('Do you already have an account?') }}
            <router-link class="align-baseline font-bold text-primary-600 hover:text-primary-800" to="/auth/login">
                {{ $t('Sign In') }}
            </router-link>
        </p>
    </form>
</template>

<script>
export default {
    name: "register",
    metaInfo() {
        return {
            title: this.$i18n.t('Register')
        }
    },
    data() {
        return {
            user: {
                name: null,
                email: null,
                phone_number: null,
                unit_number: null,
                condo_location_id: null,
                password: null,
                password_confirmation: null,
                captcha: null
            },
            condoLocations: []
        }
    },
    mounted() {
        this.loadCondoLocations();
    },
    methods: {
        submit() {
            const self = this;
            if (self.$store.state.settings.recaptcha_enabled) {
                self.$recaptcha('register').then(function (token) {
                    self.user.captcha = token;
                    self.register();
                });
            } else {
                self.register();
            }
        },
        loadCondoLocations() {
            const self = this;
            axios.get('api/condo-locations/select').then(function (response) {
                self.condoLocations = response.data.data;
            });
        },
        register() {
            const self = this;
            const ladda = Ladda.create(document.querySelector('#submit-register'));
            ladda.start();
            axios.post('api/auth/register', this.user).then(function (response) {
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: response.data.message,
                    type: 'success'
                });
                self.$router.push('/auth/verify-email');
            }).catch(function (error) {
                self.user.password = null;
                self.user.password_confirmation = null;
                ladda.stop();
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
