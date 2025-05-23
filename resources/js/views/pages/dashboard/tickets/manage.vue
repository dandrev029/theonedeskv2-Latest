<template>
    <div class="bg-white flex-1 relative">
        <loading :status="loading.form"/>
        <div class="flex relative">
            <div :style="{height: 'calc(100vh - 70px)'}" class="flex-auto min-w-0">
                <div class="w-full h:auto sm:h-14 border-b sm:px-3">
                    <div class="sm:flex sm:justify-between">
                        <div class="flex">
                            <button class="btn p-4 rounded-none" type="button" @click="$router.push('/dashboard/tickets')">
                                <svg-vue class="h-5 w-5 text-gray-700 dark:text-gray-300" icon="font-awesome/chevron-left-regular"></svg-vue>
                            </button>
                            <div v-on-clickaway="closeActionDropdown" class="block">
                                <div class="relative inline-block text-left">
                                    <button class="btn p-4 rounded-none" type="button" @click="toggleActionDropdown('agent')">
                                        <svg-vue class="h-5 w-5 text-gray-700 dark:text-gray-300" icon="font-awesome/user-tag-regular"></svg-vue>
                                    </button>
                                    <div v-show="actions.agent" class="origin-top-right absolute left-0 mt-1 w-56 rounded-md shadow-lg z-20">
                                        <div class="rounded-md bg-white shadow-xs">
                                            <div class="py-1">
                                                <template v-for="agent in agentList">
                                                    <a
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                        href="#"
                                                        role="menuitem"
                                                        @click.prevent="action('agent', agent.id)"
                                                    >
                                                        {{ agent.name }}
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative inline-block text-left">
                                    <button class="btn p-4 rounded-none" type="button" @click="toggleActionDropdown('department')">
                                        <svg-vue class="h-5 w-5 text-gray-700 dark:text-gray-300" icon="font-awesome/users-class-regular"></svg-vue>
                                    </button>
                                    <div v-show="actions.department" class="origin-top-right absolute left-0 mt-1 w-56 rounded-md shadow-lg z-20">
                                        <div class="rounded-md bg-white shadow-xs">
                                            <div class="py-1">
                                                <template v-for="department in departmentList">
                                                    <a
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                        href="#"
                                                        role="menuitem"
                                                        @click.prevent="action('department', department.id)"
                                                    >
                                                        {{ department.name }}
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative inline-block text-left">
                                    <button class="btn p-4 rounded-none" type="button" @click="toggleActionDropdown('label')">
                                        <svg-vue class="h-5 w-5 text-gray-700 dark:text-gray-300" icon="font-awesome/tags-regular"></svg-vue>
                                    </button>
                                    <div v-show="actions.label" class="origin-top-right absolute left-0 mt-1 w-56 rounded-md shadow-lg z-20">
                                        <div class="rounded-md bg-white shadow-xs">
                                            <div class="py-1">
                                                <template v-for="label in labelList">
                                                    <a
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                        href="#"
                                                        role="menuitem"
                                                        @click.prevent="action('label', label.id)"
                                                    >
                                                        {{ label.name }}
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative inline-block text-left">
                                    <button class="btn p-4 rounded-none" type="button" @click="toggleActionDropdown('priority')">
                                        <svg-vue class="h-5 w-5 text-gray-700 dark:text-gray-300" icon="font-awesome/pennant-regular"></svg-vue>
                                    </button>
                                    <div v-show="actions.priority" class="origin-top-right absolute left-0 mt-1 w-56 rounded-md shadow-lg z-20">
                                        <div class="rounded-md bg-white shadow-xs">
                                            <div class="py-1">
                                                <template v-for="priority in priorityList">
                                                    <a
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                        href="#"
                                                        role="menuitem"
                                                        @click.prevent="action('priority', priority.id)"
                                                    >
                                                        {{ priority.name }}
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn p-4 rounded-none" type="button" @click="deleteTicketModal = true">
                                <svg-vue class="h-5 w-5 text-gray-700 dark:text-gray-300" icon="font-awesome/trash-alt-regular"></svg-vue>
                            </button>
                        </div>
                        <div class="flex items-center justify-end m-3 sm:m-0">
                            <div class="text-2xl font-semibold">#{{ ticket.id }}</div>
                            <div v-if="ticket.status" class="px-3">
                                <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium"
                                     :style="{ backgroundColor: ticket.status.color + '33', color: ticket.status.color }">
                                    {{ ticket.status.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ticket-conversation">
                    <div class="block">
                        <div class="sm:flex sm:items-center py-3 max-w-full border-b">
                            <div class="px-6 sm:pl-6 sm:pr-3 sm:flex-1 sm:w-3/4">
                                <div class="text-xl truncate">{{ ticket.subject }}</div>
                                <div v-if="ticket.scheduled_visit_at" class="flex items-center text-sm text-gray-600 mt-2 mb-2">
                                    <svg-vue class="h-4 w-4 mr-1 text-gray-500 dark:text-gray-300" icon="font-awesome/calendar-alt-regular"></svg-vue>
                                    <span>{{ $t('Scheduled Visit') }}: {{ ticket.scheduled_visit_at | momentFormatDateTime }}</span>
                                </div>
                                <template v-for="(label, index) in ticket.labels">
                                    <div
                                        :style="{backgroundColor: label.color}"
                                        class="inline-flex items-center px-2 py-0.5 mr-1 rounded text-xs font-medium leading-4 text-gray-100"
                                    >
                                        {{ label.name }}
                                        <button class="flex-shrink-0 ml-1.5 inline-flex text-gray-100 focus:outline-none focus:text-gray-100 cursor-pointer" type="button" @click="removeLabel(index)">
                                            <svg-vue class="h-3 w-3" icon="font-awesome/times-solid"></svg-vue>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div class="px-6 sm:pl-3 sm:pr-6 sm:flex-1 sm:w-1/4">
                                <div class="flex items-center sm:float-right mt-3 sm:mt-0">
                                <div class="text-sm sm:pr-2">{{ ticket.created_at | momentFormatDateTimeAgo }}</div>
                                    <button class="flex items-center btn btn-white p-2 ml-3 sm:ml-0" type="button" @click="replyForm = true">
                                        <svg-vue class="h-4 w-4 mr-2 text-gray-700 dark:text-gray-300" icon="font-awesome/reply-regular"></svg-vue>
                                        {{ $t('Reply') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-show="replyForm" class="px-6 py-3 border-b">
                            <loading :status="loading.reply"/>
                            <form @submit.prevent="addReply">
                                <input-wysiwyg
                                    id="ticket_body"
                                    v-model="ticketReply.body"
                                    :cannedReplyList="cannedReplyList"
                                    :plugins="{images: true, cannedReply: true, attachment: true, shortCode: true}"
                                    @selectUploadFile="selectUploadFile"
                                >
                                    <template v-slot:top>
                                        <div :class="{'bg-gray-200': uploadingFileProgress > 0}" class="h-1 w-full">
                                            <div :style="{width: uploadingFileProgress + '%'}" class="bg-blue-500 py-0.5"></div>
                                        </div>
                                    </template>
                                    <template v-slot:bottom>
                                        <div class="flex justify-between border border-t-0">
                                            <button
                                                class="btn btn-secondary rounded-none"
                                                type="button"
                                                @click="discardReply"
                                            >
                                                {{ $t('Discard') }}
                                            </button>
                                            <div class="flex">
                                                <select
                                                    id="status"
                                                    v-model="ticketReply.status_id"
                                                    aria-label="Sort by"
                                                    class="block form-select pl-3 pr-9 py-2 border-l border-r-0 border-t-0 border-b-0 border-gray-400 rounded-none bg-white text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150"
                                                >
                                                    <template v-for="status in statusList">
                                                        <option :value="status.id" :style="{
                                                            backgroundColor: (status.id === ticketReply.status_id) ? status.color + '33' : 'transparent'
                                                        }">
                                                            {{ status.name }}
                                                        </option>
                                                    </template>
                                                </select>
                                                <button
                                                    class="btn btn-green rounded-none"
                                                    type="submit"
                                                >
                                                    {{ $t('Send reply') }}
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </input-wysiwyg>
                            </form>
                            <input ref="fileInput" hidden type="file" @change="uploadFile($event)">
                            <template v-if="ticketReply.attachments.length > 0">
                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                                    <template v-for="(attachment, index) in ticketReply.attachments">
                                        <attachment :details="attachment" v-on:remove="removeAttachment(index)"/>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <template v-if="ticket.ticketReplies.length > 0">
                            <div class="block">
                                <template v-for="(ticketReply, index) in ticket.ticketReplies">
                                    <div :class="{'border-t' : index > 0}" class="flex p-6">
                                        <img
                                            :alt="$t('Avatar')"
                                            :src="ticketReply.user.avatar !== 'gravatar' ? ticketReply.user.avatar : ticketReply.user.gravatar"
                                            class="h-12 w-12 hidden sm:inline"
                                        />
                                        <div class="sm:pl-6 pb-2 w-full">
                                            <div class="md:flex md:items-center pb-1">
                                                <div class="md:flex-1 text-lg font-semibold text-gray-800">
                                                    {{ ticketReply.user.name }}
                                                </div>
                                                <div class="md:flex-1">
                                                    <div class="md:float-right text-sm">
                                                        {{ ticketReply.created_at | momentFormatDateTime }}
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-gray-700 ticket-reply-body" v-html="ticketReply.body"/>
                                            <template v-if="ticketReply.attachments.length > 0">
                                                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                                                    <template v-for="attachment in ticketReply.attachments">
                                                        <attachment :details="attachment" :remove-button="false"/>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template v-else>
                            <div class="h-full flex border-t">
                                <div class="m-auto">
                                    <div class="grid grid-cols-1 justify-items-center h-full w-full py-24">
                                        <div class="flex justify-center items-center">
                                            <svg-vue class="h-full h-auto w-48 mb-6" icon="undraw.task-list"></svg-vue>
                                        </div>
                                        <div class="flex justify-center items-center">
                                            <div class="w-full font-semibold text-2xl">{{ $t('This ticket has no conversations') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div :style="{flex: '0 0 320px', width: '320px'}" class="hidden lg:block border-l">
                <div v-if="ticket.user" class="h-full overflow-y-auto">
                    <!-- Tenant Profile Section -->
                    <div class="ticket-details-user-gradient py-6 px-4">
                        <div class="flex items-center">
                            <div class="relative">
                                <img
                                    :alt="$t('Avatar')"
                                    :src="ticket.user.avatar !== 'gravatar' ? ticket.user.avatar : ticket.user.gravatar"
                                    class="h-16 w-16 rounded-full object-cover border-2 border-white shadow-md"
                                />
                                <span class="absolute bottom-0 right-0 h-4 w-4 rounded-full bg-green-400 border-2 border-white"></span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-white font-medium text-lg truncate">{{ ticket.user.name }}</h3>
                                <div class="flex items-center text-sm text-white opacity-90 mb-1">
                                    <svg-vue class="flex-shrink-0 mr-1.5 h-4 w-4 text-white opacity-90" icon="font-awesome/envelope-solid"></svg-vue>
                                    <span class="truncate">{{ ticket.user.email }}</span>
                                </div>
                                <div class="flex items-center text-sm text-white opacity-90">
                                    <svg-vue class="flex-shrink-0 mr-1.5 h-4 w-4 text-white" icon="font-awesome/phone-alt-solid"></svg-vue>
                                    <span class="truncate">{{ ticket.user.phone_number || $t('No phone number') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Details Section -->
                    <div class="divide-y divide-gray-200">
                        <!-- Ticket Summary -->
                        <div class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Ticket Summary') }}
                            </h4>
                            <p class="text-sm font-medium text-gray-800">{{ ticket.subject }}</p>
                        </div>

                        <!-- Status & Priority Section -->
                        <div class="px-4 py-4 bg-white">
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Status -->
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                        {{ $t('Status') }}
                                    </h4>
                                    <div v-if="ticket.status" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                        :style="{ backgroundColor: ticket.status.color + '20', color: ticket.status.color }">
                                        <span class="w-2 h-2 rounded-full mr-1.5"
                                            :style="{ backgroundColor: ticket.status.color }"></span>
                                        {{ ticket.status.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500">{{ $t('Unassigned') }}</div>
                                </div>
                                <!-- Priority -->
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                        {{ $t('Priority') }}
                                    </h4>
                                    <div class="text-sm font-medium text-gray-800">
                                        {{ ticket.priority ? ticket.priority.name : $t('Unassigned') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Condo Location -->
                        <div class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Condo Location') }}
                            </h4>
                            <div class="flex items-center">
                                <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/map-marker-alt-solid"></svg-vue>
                                <span class="text-sm text-gray-800">
                                    {{ ticket.condoLocation && ticket.condoLocation.name
                                       ? ticket.condoLocation.name
                                       : (ticket.condo_location && ticket.condo_location.name
                                          ? ticket.condo_location.name
                                          : $t('Not specified')) }}
                                </span>
                            </div>
                        </div>

                        <!-- Unit Number -->
                        <div class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Unit Number') }}
                            </h4>
                            <div class="flex items-center">
                                <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/building-regular"></svg-vue>
                                <span class="text-sm text-gray-800">
                                    {{ ticket.user && ticket.user.unit_number
                                       ? ticket.user.unit_number
                                       : $t('Not specified') }}
                                </span>
                            </div>
                        </div>

                        <!-- Scheduled Visit -->
                        <div class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Scheduled Visit') }}
                            </h4>
                            <div class="flex items-center">
                                <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/calendar-alt-regular"></svg-vue>
                                <span v-if="ticket.scheduled_visit_at" class="text-sm text-gray-800">
                                    {{ ticket.scheduled_visit_at | momentFormatDateTime }}
                                </span>
                                <span v-else class="text-sm text-gray-500">{{ $t('Not scheduled') }}</span>
                            </div>
                        </div>

                        <!-- Voucher Code -->
                        <div v-if="isWifiHelpdesk" class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Voucher Code') }} ({{ isWifiHelpdesk ? 'Visible' : 'Hidden' }})
                            </h4>
                            <div class="flex items-center">
                                <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/ticket-alt-regular"></svg-vue>
                                <span v-if="ticket.voucher_code" class="text-sm font-medium text-gray-800">
                                    {{ ticket.voucher_code }}
                                </span>
                                <span v-else class="text-sm text-gray-500">{{ $t('No voucher') }}</span>
                            </div>
                        </div>

                        <!-- Assigned Agent -->
                        <div class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Assigned Agent') }}
                            </h4>
                            <div class="flex items-center">
                                <div v-if="ticket.agent" class="flex items-center">
                                    <img
                                        :src="ticket.agent.avatar !== 'gravatar' ? ticket.agent.avatar : ticket.agent.gravatar"
                                        :alt="ticket.agent.name"
                                        class="h-6 w-6 rounded-full mr-2"
                                    />
                                    <span class="text-sm font-medium text-gray-800">{{ ticket.agent.name }}</span>
                                </div>
                                <div v-else class="flex items-center">
                                    <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/user-regular"></svg-vue>
                                    <span class="text-sm text-gray-500">{{ $t('Unassigned') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Department') }}
                            </h4>
                            <div class="flex items-center">
                                <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/users-class-regular"></svg-vue>
                                <span v-if="ticket.department" class="text-sm font-medium text-gray-800">
                                    {{ ticket.department.name }}
                                </span>
                                <span v-else class="text-sm text-gray-500">{{ $t('Unassigned') }}</span>
                            </div>
                        </div>

                        <!-- Ticket timestamps -->
                        <div class="px-4 py-4 bg-white">
                            <div class="space-y-2">
                                <!-- Created At timestamp -->
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                        {{ $t('Created') }}
                                    </h4>
                                    <div class="flex items-center">
                                        <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/clock-regular"></svg-vue>
                                        <span class="text-sm text-gray-600">{{ ticket.created_at | momentFormatDateTime }}</span>
                                    </div>
                                </div>

                                <!-- Updated At timestamp -->
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                        {{ $t('Updated') }}
                                    </h4>
                                    <div class="flex items-center">
                                        <svg-vue class="h-4 w-4 text-gray-500 dark:text-gray-300 mr-2" icon="font-awesome/history-solid"></svg-vue>
                                        <span class="text-sm text-gray-600">
                                            {{ ticket.updated_at | momentFormatDateTime }}
                                            <span class="text-xs text-gray-500 ml-1">({{ ticket.updated_at | momentFormatDateTimeAgo }})</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Labels Section (if any) -->
                        <div v-if="ticket.labels && ticket.labels.length > 0" class="px-4 py-4 bg-white">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $t('Labels') }}
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <div
                                    v-for="(label, index) in ticket.labels"
                                    :key="index"
                                    :style="{backgroundColor: label.color}"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white"
                                >
                                    {{ label.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-show="deleteTicketModal" class="fixed z-20 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <transition
                    duration="300"
                    enter-active-class="ease-out duration-300"
                    enter-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="deleteTicketModal" class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                </transition>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="deleteTicketModal"
                        aria-labelledby="modal-headline"
                        aria-modal="true"
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                        role="dialog"
                    >
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg-vue class="h-6 w-6 pb-1 text-red-600" icon="font-awesome/exclamation-triangle-light"></svg-vue>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 id="modal-headline" class="text-lg leading-6 font-medium text-gray-900">
                                        {{ $t('Delete ticket') }}
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm leading-5 text-gray-500">
                                            {{ $t('Are you sure you want to delete the ticket?') }}
                                            {{ $t('All data will be permanently removed') }}.
                                            {{ $t('All related data will be deleted') }}.
                                            {{ $t('This action cannot be undone') }}.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                class="btn btn-red mr-2 sm:mr-0"
                                type="button"
                                @click="deleteTicket"
                            >
                                {{ $t('Delete ticket') }}
                            </button>
                            <button
                                class="btn btn-white mr-0 sm:mr-2"
                                type="button"
                                @click="deleteTicketModal = false"
                            >
                                {{ $t('Cancel') }}
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
import {mixin as clickaway} from "../../../../utilities/vue-clickaway-compat";

export default {
    name: "manage",
    mixins: [clickaway],
    metaInfo() {
        return {
            title: this.$i18n.t('Manage ticket')
        }
    },
    mounted() {
        this.getTicket();
        this.getFilters();
        this.getCannedReplies();
    },
    data() {
        return {
            loading: {
                form: true,
                reply: false,
                file: false,
            },
            deleteTicketModal: false,
            replyForm: false,
            uploadingFileProgress: 0,
            ticket: {
                subject: null,
                created_at: null,
                labels: [],
                ticketReplies: [],
            },
            ticketReply: {
                status_id: null,
                body: '',
                attachments: [],
            },
            actions: {
                agent: false,
                department: false,
                label: false,
                priority: false,
            },
            cannedReplyList: [],
            agentList: [],
            departmentList: [],
            labelList: [],
            statusList: [],
            priorityList: [],
        }
    },
    filters: {
        momentFormatDateTimeAgo: function (value) {
            return moment(value).locale(window.app.app_date_locale).fromNow();
        },
        momentFormatDateTime: function (value) {
            // Parse the ISO string with the timezone information preserved
            return moment.utc(value).tz(window.app.app_timezone).locale(window.app.app_date_locale).format(window.app.app_date_format + ' h:mm A');
        },
    },
    computed: {
        isWifiHelpdesk() {
            // Hard-coded check for MIS user (WiFi Helpdesk)
            const user = this.$store.state.user;
            if (user && user.name === 'MIS') {
                return true;
            }

            // Check if user has departments directly
            if (user && user.departments && user.departments.length > 0) {
                return user.departments.some(dept =>
                    dept.name.toLowerCase().includes('wifi')
                );
            }

            // Check if user's role name contains WiFi
            if (user && user.role && user.role.name) {
                return user.role.name.toLowerCase().includes('wifi');
            }

            return false;
        }
    },
    methods: {
        getTicket() {
            const self = this;
            self.loading.form = true;
            axios.get('api/dashboard/tickets/' + self.$route.params.uuid).then(function (response) {
                self.loading.form = false;
                self.ticket = response.data;
                self.ticketReply.status_id = response.data.status_id;
            }).catch(function () {
                self.$router.push('/dashboard/tickets');
            });
        },
        getCannedReplies() {
            const self = this;
            axios.get('api/dashboard/tickets/canned-replies').then(function (response) {
                self.cannedReplyList = response.data;
            })
        },
        getFilters() {
            const self = this;
            axios.get('api/dashboard/tickets/filters').then(function (response) {
                self.agentList = response.data.agents;
                self.departmentList = response.data.departments;
                self.labelList = response.data.labels;
                self.statusList = response.data.statuses;
                self.priorityList = response.data.priorities;
            });
        },
        discardReply() {
            this.ticketReply.body = '';
            this.ticketReply.attachments = [];
            this.replyForm = false;
        },
        selectUploadFile() {
            if (!this.loading.file) {
                this.$refs.fileInput.click();
            } else {
                this.$notify({
                    title: this.$i18n.t('Error').toString(),
                    text: this.$i18n.t('A file is being uploaded').toString(),
                    type: 'warning'
                });
            }
        },
        uploadFile(e) {
            const self = this;
            const formData = new FormData();
            self.loading.file = true;
            formData.append('file', e.target.files[0]);
            axios.post(
                'api/dashboard/tickets/attachments',
                formData,
                {
                    headers: {'Content-Type': 'multipart/form-data'},
                    onUploadProgress: function (progressEvent) {
                        self.uploadingFileProgress = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                    }.bind(this)
                }
            ).then(function (response) {
                self.loading.file = false;
                self.uploadingFileProgress = 0;
                self.$refs.fileInput.value = null;
                self.ticketReply.attachments.push(response.data);
            }).catch(function () {
                self.loading.file = false;
                self.uploadingFileProgress = 0;
                self.$refs.fileInput.value = null;
            });
        },
        removeAttachment(attachment) {
            this.ticketReply.attachments.splice(attachment, 1);
        },
        removeLabel(index) {
            const self = this;
            const label = self.ticket.labels[index];
            axios.post('api/dashboard/tickets/' + self.$route.params.uuid + '/remove-label', {
                label: label.id,
            }).then(function () {
                self.ticket.labels.splice(index, 1);
            });
        },
        addReply() {
            const self = this;
            self.loading.reply = true;

            // Make sure we have a valid body
            if (!self.ticketReply.body || self.ticketReply.body.trim() === '') {
                self.$notify({
                    title: self.$i18n.t('Error').toString(),
                    text: self.$i18n.t('Reply message cannot be empty').toString(),
                    type: 'error'
                });
                self.loading.reply = false;
                return;
            }

            // Make sure we have a valid status
            if (!self.ticketReply.status_id) {
                self.$notify({
                    title: self.$i18n.t('Error').toString(),
                    text: self.$i18n.t('Please select a status').toString(),
                    type: 'error'
                });
                self.loading.reply = false;
                return;
            }

            // Add user_id explicitly to ensure it's sent
            const replyData = {
                ...self.ticketReply,
                user_id: self.$store.state.user.id // Add the user ID from the store
            };

            axios.post('api/dashboard/tickets/' + self.$route.params.uuid + '/reply', replyData)
                .then(function (response) {
                    self.$notify({
                        title: self.$i18n.t('Success').toString(),
                        text: self.$i18n.t('Data saved correctly').toString(),
                        type: 'success'
                    });
                    self.ticket = response.data.ticket;
                    self.ticketReply.status_id = response.data.ticket.status_id;
                    self.discardReply();
                    self.loading.reply = false;
                })
                .catch(function (error) {
                    self.$notify({
                        title: self.$i18n.t('Error').toString(),
                        text: error.response && error.response.data.message
                            ? error.response.data.message
                            : self.$i18n.t('An error occurred while saving data').toString(),
                        type: 'error'
                    });
                    self.loading.reply = false;
                    console.error('Error sending reply:', error);
                });
        },
        closeActionDropdown() {
            this.actions.agent = false;
            this.actions.department = false;
            this.actions.label = false;
            this.actions.priority = false;
        },
        toggleActionDropdown(action) {
            if (action === 'agent') {
                this.actions.agent = !this.actions.agent;
                this.actions.department = false;
                this.actions.label = false;
                this.actions.priority = false;
            }
            if (action === 'department') {
                this.actions.department = !this.actions.department;
                this.actions.agent = false;
                this.actions.label = false;
                this.actions.priority = false;
            }
            if (action === 'label') {
                this.actions.label = !this.actions.label;
                this.actions.agent = false;
                this.actions.department = false;
                this.actions.priority = false;
            }
            if (action === 'priority') {
                this.actions.priority = !this.actions.priority;
                this.actions.agent = false;
                this.actions.department = false;
                this.actions.label = false;
            }
        },
        action(param, value) {
            const self = this;
            axios.post('api/dashboard/tickets/' + self.$route.params.uuid + '/quick-actions', {
                action: param,
                value: value,
            }).then(function (response) {
                self.closeActionDropdown();
                if (!response.data.access) {
                    self.$router.push('/dashboard/tickets');
                } else {
                    self.ticket = response.data.ticket;
                    self.ticketReply.status_id = response.data.ticket.status_id;
                }
            }).catch(function () {
                self.closeActionDropdown();
            });
        },
        deleteTicket() {
            const self = this;
            axios.delete('api/dashboard/tickets/' + self.$route.params.uuid).then(function () {
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: self.$i18n.t('Data deleted successfully').toString(),
                    type: 'success'
                });
                self.$router.push('/dashboard/tickets');
            }).catch(function () {
                self.deleteTicketModal = false;
            });
        }
    }
}
</script>

<style>
/*
  Force SVG path elements to inherit their fill color from the parent's text color.
  This is to ensure Tailwind text color utilities (e.g., .text-white, .text-gray-500)
  correctly color the SVG icons. The !important flag is used to override
  any default or conflicting fill attributes on the path elements.
*/
[class*="text-"] svg path,
svg[class*="text-"] path {
  fill: currentColor !important;
}
</style>
