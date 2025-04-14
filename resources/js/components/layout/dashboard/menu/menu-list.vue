<template>
    <div>
        <div class="my-3 mx-4 uppercase text-primary-200 text-xs font-semibold">
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
            class="my-3 mx-4 uppercase text-primary-200 text-xs font-semibold"
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
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController']"
            :label="$t('Condo Locations')"
            :mobile="mobile"
            icon="font-awesome.building"
            to="/dashboard/admin/condo-locations"
        ></menu-item>
        <menu-item
            v-if="$store.state.permissions && $store.state.permissions['App.Http.Controllers.Api.Dashboard.Admin.TicketConcernController']"
            :label="$t('Ticket Concerns')"
            :mobile="mobile"
            icon="font-awesome.tag"
            to="/dashboard/admin/ticket-concerns"
        ></menu-item>
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

