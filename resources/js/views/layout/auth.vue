<template>
    <div
        :style="{backgroundImage: 'url(' + $store.state.settings.background + ')'}"
        class="bg-center bg-no-repeat bg-cover items-center"
    >
        <div class="container mx-auto">
            <div class="h-full min-h-screen flex">
                <div class="w-full m-auto">
                    <div class="flex justify-center">
                        <div class="w-full max-w-md shadow-sm rounded-lg border p-10 my-10" :class="{'bg-white border-secondary-200': !$store.state.darkMode, 'bg-gray-800 border-gray-700': $store.state.darkMode}">
                            <div class="flex justify-between items-center mb-8">
                                <router-link class="flex items-center" to="/">
                                    <img :src="$store.state.settings.icon" alt="App icon" class="w-12 h-12 mr-5"/>
                                    <div class="inline-block text-2xl font-semibold uppercase" :class="$store.state.darkMode ? 'text-primary-400' : 'text-primary-800'">{{ $store.state.settings.name }}</div>
                                </router-link>
                                <dark-mode-toggle />
                            </div>
                            <router-view/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DarkModeToggle from "@/components/elements/dark-mode-toggle";

export default {
    name: "auth",
    components: { DarkModeToggle },
    metaInfo() {
        return {
            titleTemplate: '%s - ' + this.$store.state.settings.name
        }
    },
}
</script>
