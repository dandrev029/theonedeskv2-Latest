<template>
    <div>
        <div class="my-3 mx-4 uppercase text-white text-xs font-semibold">
            {{ $t('General') }}
        </div>
        <menu-item
            :label="$t('Dashboard')"
            :mobile="mobile"
            icon="font-awesome.tachometer-alt-regular"
            to="/dashboard/home"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.TicketController']"
            :label="$t('Tickets')"
            :mobile="mobile"
            icon="font-awesome.inbox-regular"
            to="/dashboard/tickets"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.CannedReplyController']"
            :label="$t('Canned replies')"
            :mobile="mobile"
            icon="font-awesome.comments-alt-regular"
            to="/dashboard/canned-replies"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.ReportController']"
            :label="$t('Reports')"
            :mobile="mobile"
            icon="font-awesome/file-export-solid"
            to="/dashboard/reports"
        ></menu-item>
        <div
            v-if="$store.state.permissions && (
                $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.UserController'] ||
                $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.UserRoleController'] ||
                $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.SettingController'] ||
                $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.LanguageController'] ||
                $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController'] ||
                $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.TicketConcernController']
            )"
            class="my-3 mx-4 uppercase text-white text-xs font-semibold"
        >
            {{ $t('Administration') }}
        </div>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.DepartmentController']"
            :label="$t('Departments')"
            :mobile="mobile"
            icon="font-awesome.users-class-regular"
            to="/dashboard/admin/departments"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.LabelController']"
            :label="$t('Labels')"
            :mobile="mobile"
            icon="font-awesome.tags-regular"
            to="/dashboard/admin/labels"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.StatusController']"
            :label="$t('Statuses')"
            :mobile="mobile"
            icon="font-awesome.tasks-regular"
            to="/dashboard/admin/statuses"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.PriorityController']"
            :label="$t('Priorities')"
            :mobile="mobile"
            icon="font-awesome.pennant-regular"
            to="/dashboard/admin/priorities"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.UserController']"
            :label="$t('Users')"
            :mobile="mobile"
            icon="font-awesome.users-regular"
            to="/dashboard/admin/users"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.UserRoleController']"
            :label="$t('User roles')"
            :mobile="mobile"
            icon="font-awesome.id-card-regular"
            to="/dashboard/admin/user-roles"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.SettingController']"
            :label="$t('Settings')"
            :mobile="mobile"
            icon="font-awesome.cog-regular"
            to="/dashboard/admin/settings"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.LanguageController']"
            :label="$t('Translations')"
            :mobile="mobile"
            icon="font-awesome.language-regular"
            to="/dashboard/admin/languages"
        ></menu-item>
        <router-link
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController']"
            :class="mobile ? 'router-link router-link-mobile sidebar-gradient-hover' : 'router-link router-link-desktop sidebar-gradient-hover'"
            to="/dashboard/admin/condo-locations"
        >
            <svg class="mr-3 w-5 h-5 text-white group-hover:text-white group-focus:text-white transition ease-in-out duration-150" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M480 48c0-26.5-21.5-48-48-48L336 0c-26.5 0-48 21.5-48 48l0 48-64 0 0-72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 72-64 0 0-72c0-13.3-10.7-24-24-24S64 10.7 64 24l0 72L48 96C21.5 96 0 117.5 0 144l0 96L0 464c0 26.5 21.5 48 48 48l256 0 32 0 96 0 160 0c26.5 0 48-21.5 48-48l0-224c0-26.5-21.5-48-48-48l-112 0 0-144zm96 320l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16zM240 416l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16zM128 400c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32zM560 256c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0zM256 176l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16zM112 160c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0zM256 304c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32zM112 320l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16zm304-48l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16zM400 64c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0zm16 112l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16z"/></svg>
            {{ $t('Condo Locations') }}
        </router-link>
        <router-link
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.TicketConcernController']"
            :class="mobile ? 'router-link router-link-mobile sidebar-gradient-hover' : 'router-link router-link-desktop sidebar-gradient-hover'"
            to="/dashboard/admin/ticket-concerns"
        >
            <svg class="mr-3 w-5 h-5 text-white group-hover:text-white group-focus:text-white transition ease-in-out duration-150" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M208 352c114.9 0 208-78.8 208-176S322.9 0 208 0S0 78.8 0 176c0 38.6 14.7 74.3 39.6 103.4c-3.5 9.4-8.7 17.7-14.2 24.7c-4.8 6.2-9.7 11-13.3 14.3c-1.8 1.6-3.3 2.9-4.3 3.7c-.5 .4-.9 .7-1.1 .8l-.2 .2s0 0 0 0s0 0 0 0C1 327.2-1.4 334.4 .8 340.9S9.1 352 16 352c21.8 0 43.8-5.6 62.1-12.5c9.2-3.5 17.8-7.4 25.2-11.4C134.1 343.3 169.8 352 208 352zM448 176c0 112.3-99.1 196.9-216.5 207C255.8 457.4 336.4 512 432 512c38.2 0 73.9-8.7 104.7-23.9c7.5 4 16 7.9 25.2 11.4c18.3 6.9 40.3 12.5 62.1 12.5c6.9 0 13.1-4.5 15.2-11.1c2.1-6.6-.2-13.8-5.8-17.9c0 0 0 0 0 0s0 0 0 0l-.2-.2c-.2-.2-.6-.4-1.1-.8c-1-.8-2.5-2-4.3-3.7c-3.6-3.3-8.5-8.1-13.3-14.3c-5.5-7-10.7-15.4-14.2-24.7c24.9-29 39.6-64.7 39.6-103.4c0-92.8-84.9-168.9-192.6-175.5c.4 5.1 .6 10.3 .6 15.5z"/></svg>
            {{ $t('Ticket Concerns') }}
        </router-link>
    </div>
</template>

<script>
import MenuItem from "@/components/layout/dashboard/menu/menu-item";

export default {
    name: "menu-list",
    components: {MenuItem},
    props: {
        mobile: {
            type: Boolean,
            required: false
        }
    }
}
</script>
