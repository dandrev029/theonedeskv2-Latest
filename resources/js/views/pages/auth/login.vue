<template>
    <form @submit.prevent="submit">
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
                    type="text"
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
        <div class="mb-4 text-right">
            <button id="submit-login" class="bg-primary-600 hover:bg-primary-500 text-white font-bold py-2 px-8 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" data-style="zoom-in" type="submit">
                {{ $t('Sign In') }}
            </button>
        </div>
        <p v-if="$store.state.settings.register" class="text-secondary-700 text-sm">
            {{ $t('Don\'t have an account?') }}
            <router-link
                class="align-baseline font-bold text-primary-600 hover:text-primary-800"
                to="/auth/register"
            >
                {{ $t('Create account') }}
            </router-link>
        </p>
        <p class="text-secondary-700 text-sm">
            {{ $t('You can not access your account?') }}
            <router-link
                class="align-baseline font-bold text-primary-600 hover:text-primary-800"
                to="/auth/recover"
            >
                {{ $t('Recover account') }}
            </router-link>
        </p>
    </form>
</template>

<script>
import Cookies from 'js-cookie';

export default {
    name: "login",
    metaInfo() {
        return {
            title: this.$i18n.t('Sign In')
        }
    },
    data() {
        return {
            user: {
                email: null,
                password: null,
                captcha: null
            }
        }
    },
    methods: {
        submit() {
            const self = this;
            if (self.$store.state.settings.recaptcha_enabled) {
                self.$recaptcha('login').then(function (token) {
                    self.user.captcha = token;
                    self.login();
                });
            } else {
                self.login();
            }
        },
        login() {
            const self = this;
            const ladda = Ladda.create(document.querySelector('#submit-login'));
            ladda.start();
            axios.post('api/auth/login', this.user).then(function (response) {
                self.$store.commit('login', response.data);
                const intendedUrl = Cookies.get('intended_url')
                if (intendedUrl) {
                    Cookies.remove('intended_url')
                    self.$router.push(intendedUrl);
                } else {
                    if (response.data.user.role.dashboard_access) {
                        self.$router.push('/dashboard/home');
                    } else {
                        self.$router.push('/tickets/list');
                    }
                }
            }).catch(function (error) {
                self.user.password = null;
                ladda.stop();

                // Check if email is not verified
                if (error.response && error.response.status === 403 && error.response.data.email_verified === false) {
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response.data.message,
                        type: 'warning'
                    });
                    self.$router.push('/auth/verify-email');
                } else if (error.response && error.response.data && error.response.data.message) {
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
