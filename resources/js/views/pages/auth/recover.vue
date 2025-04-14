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
        <div class="mb-4 text-right">
            <button id="submit-recover" class="bg-primary-600 hover:bg-primary-500 text-white font-bold py-2 px-8 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" data-style="zoom-in" type="submit">
                {{ $t('Send account recovery email') }}
            </button>
        </div>
        <p class="text-secondary-700 text-sm">
            {{ $t('Do you already have access to your account?') }}
            <router-link
                class="align-baseline font-bold text-primary-600 hover:text-primary-800"
                to="/auth/login"
            >
                {{ $t('Sign In') }}
            </router-link>
        </p>
    </form>
</template>

<script>
export default {
    name: "recover",
    metaInfo() {
        return {
            title: this.$i18n.t('Recover account')
        }
    },
    data() {
        return {
            user: {
                email: null,
                captcha: null
            }
        }
    },
    methods: {
        submit() {
            const self = this;
            if (self.$store.state.settings.recaptcha_enabled) {
                self.$recaptcha('recover').then(function (token) {
                    self.user.captcha = token;
                    self.recover();
                });
            } else {
                self.recover();
            }
        },
        recover() {
            const self = this;
            const ladda = Ladda.create(document.querySelector('#submit-recover'));
            ladda.start();
            axios.post('api/auth/recover', this.user).then(function () {
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: self.$i18n.t('An email has been sent with a link to reset the password').toString(),
                    type: 'success'
                })
                self.$router.push('/auth/login');
            }).catch(function () {
                self.user.email = null;
            });
        }
    }
}
</script>
