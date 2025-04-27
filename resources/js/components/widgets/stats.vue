<template>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.open_tickets == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-primary-100 rounded-full p-3 mr-4">
                    <svg class="h-6 w-6 text-primary-600" fill="currentColor" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path d="M128 160h320v192H128V160zm400 96c0 26.51 21.49 48 48 48v96h-48c-26.51 0-48 21.49-48 48s21.49 48 48 48h48v48c0 8.837-7.163 16-16 16H16c-8.837 0-16-7.163-16-16v-48h48c26.51 0 48-21.49 48-48s-21.49-48-48-48H0v-96c26.51 0 48-21.49 48-48s-21.49-48-48-48H0V80c0-8.837 7.163-16 16-16h544c8.837 0 16 7.163 16 16v48h-48c-26.51 0-48 21.49-48 48z"></path></svg>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Open tickets') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.open_tickets ? stats.open_tickets : 0 }}
                    </dd>
                </div>
            </div>
        </div>
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.pending_tickets == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3 mr-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8l-22.4 30.8c-3.9 5.3-11.4 6.5-16.8 2.6z"></path></svg>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Pending tickets') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.pending_tickets ? stats.pending_tickets : 0 }}
                    </dd>
                </div>
            </div>
        </div>
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.solved_tickets == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119.03 8 8 119.03 8 256s111.03 248 248 248 248-111.03 248-248S392.97 8 256 8zm0 448c-110.53 0-200-89.47-200-200S145.47 56 256 56s200 89.47 200 200-89.47 200-200 200zm141.94-255.94l-160 160c-4.69 4.69-10.88 7.03-17.03 7.03s-12.34-2.34-17.03-7.03l-80-80c-9.38-9.38-9.38-24.56 0-33.94s24.56-9.38 33.94 0l63.03 63.03 143.03-143.03c9.38-9.38 24.56-9.38 33.94 0s9.38 24.56 0 33.94z"/></svg>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Solved tickets') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.solved_tickets ? stats.solved_tickets : 0 }}
                    </dd>
                </div>
            </div>
        </div>
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.without_agent == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-full p-3 mr-4">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M633.8 458.1l-55.4-43.4c13-17.1 21.6-37.9 21.6-60.7 0-61.9-50.1-112-112-112s-112 50.1-112 112c0 11.5.6 22.8 1.8 33.8l-65.1 50.9C280.4 427.4 217.7 392 144 392c-88.4 0-160 71.6-160 160 0 17.7 14.3 32 32 32h544c11.1 0 21.4-5.7 27.4-15.1 6-9.4 5.6-21.6-.9-30.8zM448 352c53 0 96 43 96 96s-43 96-96 96-96-43-96-96 43-96 96-96zM96 480c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm288-192c0-88.4-71.6-160-160-160S64 119.6 64 208s71.6 160 160 160c69.7 0 129.9-44.5 152.1-105.9l47.6 37.2C399.7 330.5 378.4 352 352 352c-79.5 0-144-64.5-144-144S272.5 64 352 64s144 64.5 144 144c0 21.6-4.8 42-13.2 60.3l60.3 47.2c19.3-25.8 30.9-57.2 30.9-91.5C576 93.1 474.9 0 352 0S128 93.1 128 208c0 75.3 40.2 140.4 99.1 175.9l-81.8 64.1C102.7 431.6 57.7 416 0 416v32c0 35.3 28.7 64 64 64h352c18.2 0 34.8-7.6 47.2-20l80.8 63.2c9.4 7.4 22.4 6.5 30.8-1.9 8.4-8.4 9.3-21.4 1.9-30.8L384.1 288.1z"/></svg>
                </div>
                <div>
                    <dt class="text-sm font-medium truncate" :class="textTertiary">
                        {{ $t('Without assign agent') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold" :class="textPrimary">
                        {{ stats.without_agent ? stats.without_agent : 0 }}
                    </dd>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "stats",
    data() {
        return {
            stats: {
                open_tickets: null,
                pending_tickets: null,
                solved_tickets: null,
                without_agent: null,
            }
        }
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            const self = this;
            axios.get('api/dashboard/stats/count').then(function (response) {
                self.stats = response.data;
            });
        }
    },
}
</script>
