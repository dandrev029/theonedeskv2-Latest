<template>
    <form @submit.prevent="submit">
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="name">{{ $t('Name') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="h-5 w-5 text-primary-500 fill-current"><path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm-45.7 48C79.8 304 0 383.8 0 482.3 0 498.7 13.3 512 29.7 512h388.6c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
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
                    <svg-vue icon="font-awesome/envelope-solid" class="h-5 w-5 text-primary-500 fill-current"></svg-vue>
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
                    <svg-vue icon="font-awesome/phone-alt-solid" class="h-5 w-5 text-primary-500 fill-current"></svg-vue>
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="h-5 w-5 text-primary-500 fill-current"><path d="M280.37 148.26L96 300.11V464a16 16 0 0 0 16 16l112.06-.29a16 16 0 0 0 15.94-16V368a16 16 0 0 1 16-16h64a16 16 0 0 1 16 16v95.64a16 16 0 0 0 16 16.05L464 480a16 16 0 0 0 16-16V300L295.67 148.26a12.19 12.19 0 0 0-15.3 0zM571.6 251.47L488 182.57V44.05a12 12 0 0 0-12-12h-56a12 12 0 0 0-12 12v72.61L318.47 43a48 48 0 0 0-61 0L4.34 251.47a12 12 0 0 0-1.6 16.9l25.5 31A12 12 0 0 0 45.15 301l235.22-193.74a12.19 12.19 0 0 1 15.3 0L530.9 301a12 12 0 0 0 16.9-1.6l25.5-31a12 12 0 0 0-1.7-16.93z"/></svg>
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
            <div class="mt-1 relative rounded-md shadow-sm z-10">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg-vue icon="font-awesome/map-marker-alt-solid" class="h-5 w-5 text-primary-500 fill-current"></svg-vue>
                </div>
                <input-select
                    v-model="user.condo_location_id"
                    class="pl-10"
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="h-5 w-5 text-primary-500 fill-current"><path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="h-5 w-5 text-primary-500 fill-current"><path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"/></svg>
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
