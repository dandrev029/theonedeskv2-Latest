<template>
    <form @submit.prevent="submit">
        <div class="mb-4 relative rounded-md shadow-sm">
            <label class="block text-sm font-medium leading-5 text-secondary-700" for="email">{{ $t('Email') }}</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="h-5 w-5 text-primary-500" fill="currentColor"><path d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm0 48v40.805c-22.422 18.259-58.168 46.651-134.586 106.49-16.841 13.247-50.201 45.072-73.414 44.701-23.208.375-56.579-31.459-73.414-44.701C106.738 199.462 70.993 171.067 48 152.805V112h416zM48 400V214.398c22.914 18.251 55.409 43.862 104.961 82.626 21.546 17.064 58.715 46.818 87.039 46.617 28.327.203 65.494-29.554 87.039-46.617 50.01-39.138 82.522-64.851 104.961-82.626V400H48z"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="h-5 w-5 text-primary-500" fill="currentColor"><path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"/></svg>
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
            <button id="submit-login" class="gradient-button font-bold py-2 px-8 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" data-style="zoom-in" type="submit">
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
