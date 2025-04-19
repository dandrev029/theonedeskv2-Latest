<template>
    <div class="h-screen flex overflow-hidden" :class="{'bg-secondary-100': !$store.state.darkMode, 'bg-gray-900': $store.state.darkMode}">
        <sidebar :sidebarVisible="sidebarVisible" @toggleSidebar="toggleSidebar"></sidebar>
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <navbar @toggleSidebar="toggleSidebar"></navbar>
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import Navbar from "@/components/layout/dashboard/navbar";
import Sidebar from "@/components/layout/dashboard/sidebar";

export default {
    name: "dashboard",
    components: {Sidebar, Navbar},
    data() {
        return {
            sidebarVisible: false,
        }
    },
    metaInfo() {
        return {
            title: this.$i18n.t('Dashboard'),
            titleTemplate: '%s - ' + this.$store.state.settings.name
        }
    },
    methods: {
        toggleSidebar(force = null) {
            if (force !== null) {
                this.sidebarVisible = force;
            } else {
                this.sidebarVisible = !this.sidebarVisible;
            }
        }
    }
}
</script>
