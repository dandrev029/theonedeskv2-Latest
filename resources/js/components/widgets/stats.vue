<template>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="overflow-hidden shadow-sm rounded-lg border" :class="getDarkModeClasses({lightBg: 'bg-white', darkBg: 'bg-gray-800', lightBorder: 'border-secondary-200', darkBorder: 'border-gray-700'})">
            <loading :status="stats.open_tickets == null"/>
            <div class="px-4 py-5 sm:p-6 flex items-center">
                <div class="flex-shrink-0 bg-primary-100 rounded-full p-3 mr-4">
                    <svg class="h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M64 64C28.7 64 0 92.7 0 128l0 64c0 8.8 7.4 15.7 15.7 18.6C34.5 217.1 48 235 48 256s-13.5 38.9-32.3 45.4C7.4 304.3 0 311.2 0 320l0 64c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-64c0-8.8-7.4-15.7-15.7-18.6C541.5 294.9 528 277 528 256s13.5-38.9 32.3-45.4c8.3-2.9 15.7-9.8 15.7-18.6l0-64c0-35.3-28.7-64-64-64L64 64zm64 112l0 160c0 8.8 7.2 16 16 16l288 0c8.8 0 16-7.2 16-16l0-160c0-8.8-7.2-16-16-16l-288 0c-8.8 0-16 7.2-16 16zM96 160c0-17.7 14.3-32 32-32l320 0c17.7 0 32 14.3 32 32l0 192c0 17.7-14.3 32-32 32l-320 0c-17.7 0-32-14.3-32-32l0-192z"/></svg>
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
                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64l0 11c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437l0 11c-17.7 0-32 14.3-32 32s14.3 32 32 32l32 0 256 0 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-11c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1l0-11c17.7 0 32-14.3 32-32s-14.3-32-32-32L320 0 64 0 32 0zM96 75l0-11 192 0 0 11c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z"/></svg>
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
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm208.4-48a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm128 32a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"/></svg>
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
