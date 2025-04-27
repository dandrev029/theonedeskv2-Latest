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
            <svg viewBox="0 0 384 512" class="mr-3 w-5 h-5 text-white group-hover:text-white group-focus:text-white transition ease-in-out duration-150" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M384 480c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32V32C0 14.3 14.3 0 32 0H192V128c0 17.7 14.3 32 32 32H384V480zm-80-352V0L384 128H304zM96 352h32c8.8 0 16 7.2 16 16s-7.2 16-16 16H96c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0-64h32c8.8 0 16 7.2 16 16s-7.2 16-16 16H96c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0-64h32c8.8 0 16 7.2 16 16s-7.2 16-16 16H96c-8.8 0-16-7.2-16-16s7.2-16 16-16zm128 64h32c8.8 0 16 7.2 16 16s-7.2 16-16 16H224c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0-64h32c8.8 0 16 7.2 16 16s-7.2 16-16 16H224c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0-64h32c8.8 0 16 7.2 16 16s-7.2 16-16 16H224c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/>
            </svg>
            {{ $t('Condo Locations') }}
        </router-link>
        <router-link
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.TicketConcernController']"
            :class="mobile ? 'router-link router-link-mobile sidebar-gradient-hover' : 'router-link router-link-desktop sidebar-gradient-hover'"
            to="/dashboard/admin/ticket-concerns"
        >
            <svg viewBox="0 0 448 512" class="mr-3 w-5 h-5 text-white group-hover:text-white group-focus:text-white transition ease-in-out duration-150" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M0 80V224c0 17.7 14.3 32 32 32H176c17.7 0 32-14.3 32-32V80c0-17.7-14.3-32-32-32H32C14.3 48 0 62.3 0 80zm112 96c-17.7 0-32-14.3-32-32s14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32zm304-96V224c0 17.7-14.3 32-32 32H288c-17.7 0-32-14.3-32-32V80c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32zM176 352h32c17.7 0 32 14.3 32 32v64h64c17.7 0 32 14.3 32 32s-14.3 32-32 32H208c-17.7 0-32-14.3-32-32V384c0-17.7 14.3-32 32-32z"/>
            </svg>
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

