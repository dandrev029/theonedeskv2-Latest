<template>
    <form @submit.prevent="submit">
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-gray-700" for="name">{{ $t('Name') }}</label>
            <input
                id="name"
                v-model="user.name"
                :placeholder="$t('Name')"
                class="form-input block w-full mt-1 sm:text-sm sm:leading-5"
                required
            />
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-gray-700" for="email">{{ $t('Email') }}</label>
            <input
                id="email"
                v-model="user.email"
                :placeholder="$t('Email')"
                class="form-input block w-full mt-1 sm:text-sm sm:leading-5"
                required
                type="email"
            />
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-gray-700" for="phone_number">{{ $t('Phone Number') }}</label>
            <input
                id="phone_number"
                v-model="user.phone_number"
                :placeholder="$t('Phone Number')"
                class="form-input block w-full mt-1 sm:text-sm sm:leading-5"
                required
                type="tel"
            />
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-gray-700" for="unit_number">{{ $t('Unit Number w/ tower #') }}</label>
            <input
                id="unit_number"
                v-model="user.unit_number"
                :placeholder="$t('Unit Number w/ tower #')"
                class="form-input block w-full mt-1 sm:text-sm sm:leading-5"
                required
            />
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-gray-700" for="condo_location_id">{{ $t('Condo Location') }}</label>
            <input-select
                v-model="user.condo_location_id"
                :options="condoLocations"
                option-label="name"
                :searchable="true"
                required
            />
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-gray-700" for="password">{{ $t('Password') }}</label>
            <input
                id="password"
                v-model="user.password"
                class="form-input block w-full mt-1 sm:text-sm sm:leading-5"
                placeholder="******************"
                required
                type="password"
            />
        </div>
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-gray-700" for="password_confirmation">{{ $t('Confirm password') }}</label>
            <input
                id="password_confirmation"
                v-model="user.password_confirmation"
                class="form-input block w-full mt-1 sm:text-sm sm:leading-5"
                placeholder="******************"
                required
                type="password"
            />
        </div>
        <div class="mb-4 text-right">
            <button id="submit-register" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded focus:outline-none focus:shadow-outline" data-style="zoom-in" type="submit">
                {{ $t('Create account') }}
            </button>
        </div>
        <p class="text-gray-700 text-sm">
            {{ $t('Do you already have an account?') }}
            <router-link class="align-baseline font-bold text-blue-500 hover:text-blue-800" to="/auth/login">
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
