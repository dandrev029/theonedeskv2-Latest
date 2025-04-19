<template>
    <main class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0">
        <form @submit.prevent="saveTicket">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="py-0.5 text-2xl font-semibold text-gray-900">{{ $t('Create ticket') }}</h1>
                    </div>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mt-6 shadow sm:rounded-lg">
                    <loading :status="loading.form"/>
                    <div class="bg-white md:grid md:grid-cols-3 md:gap-6 px-4 py-5 sm:p-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $t('Ticket details') }}</h3>
                            <p class="mt-1 text-sm leading-5 text-gray-500">
                                {{ $t('Ticket details and classification') }}.
                            </p>
                        </div>
                        <div class="mt-5 md:mt-0 md:col-span-2">
                            <div class="grid grid-cols-3 gap-6">
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="customer">{{ $t('Customer') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input-select
                                            id="customer"
                                            v-model="ticket.user_id"
                                            :options="userList"
                                            option-label="name"
                                            required
                                            searchable
                                        >
                                            <template v-slot:selectedOption="props">
                                                <template v-if="props && !props.anySelected">
                                                    <span class="block truncate">{{ $t('Select an option') }}</span>
                                                </template>
                                                <template v-else>
                                                    <div class="flex-shrink-0 inline-block">
                                                        <img :src="props.option.avatar !== 'gravatar' ? props.option.avatar : props.option.gravatar" alt="Icon" class="w-5 h-auto rounded-full">
                                                    </div>
                                                    <span class="block truncate">{{ props.option.name }}</span>
                                                </template>
                                            </template>
                                            <template v-slot:selectOption="props">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0 inline-block">
                                                        <img :src="props.option.avatar !== 'gravatar' ? props.option.avatar : props.option.gravatar" alt="Icon" class="w-5 h-auto rounded-full">
                                                    </div>
                                                    <div :class="props.option.id === ticket.user_id ? 'font-semibold' : 'font-normal'" class="font-normal block truncate">
                                                        {{ props.option.name }}
                                                    </div>
                                                </div>
                                            </template>
                                        </input-select>
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="subject">{{ $t('Subject') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input
                                            id="subject"
                                            v-model="ticket.subject"
                                            :placeholder="$t('Subject')"
                                            class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                            required
                                        >
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="department">{{ $t('Department') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input-select
                                            id="department"
                                            v-model="ticket.department_id"
                                            :options="departmentList"
                                            option-label="name"
                                            required
                                        />
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="concern">{{ $t('Concern') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input-select
                                            id="concern"
                                            v-model="ticket.concern_id"
                                            :options="filteredConcerns"
                                            option-label="name"
                                            :disabled="!ticket.department_id"
                                        />
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="voucher_code">{{ $t('Voucher Code') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input
                                            id="voucher_code"
                                            v-model="ticket.voucher_code"
                                            :placeholder="$t('Voucher Code')"
                                            class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                        >
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="scheduled_visit_at">{{ $t('Schedule a Visit') }}</label>
                                    <div class="mt-1 flex">
                                        <div class="relative rounded-md shadow-sm flex-grow">
                                            <input
                                                id="scheduled_visit_at"
                                                v-model="ticket.scheduled_visit_at"
                                                type="datetime-local"
                                                class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                :placeholder="$t('Select date and time for visit')"
                                            >
                                        </div>
                                        <button
                                            type="button"
                                            @click="setVisitTimeToNow"
                                            class="ml-2 inline-flex items-center px-3 py-2 border border-primary-300 text-sm leading-4 font-medium rounded-md text-primary-700 bg-white hover:text-primary-500 focus:outline-none focus:border-primary-300 focus:shadow-outline-primary active:text-primary-800 active:bg-primary-50 transition ease-in-out duration-150"
                                        >
                                            {{ $t('NOW') }}
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ $t('Select preferred date and time for tenant visit') }}</p>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="status">{{ $t('Status') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input-select
                                            id="status"
                                            v-model="ticket.status_id"
                                            :options="statusList"
                                            option-label="name"
                                            required
                                        />
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="priority">{{ $t('Priority') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input-select
                                            id="priority"
                                            v-model="ticket.priority_id"
                                            :options="priorityList"
                                            option-label="name"
                                            required
                                        />
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium leading-5 text-gray-700" for="ticket_body">{{ $t('Ticket body') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input-wysiwyg
                                            id="ticket_body"
                                            v-model="ticket.body"
                                            :cannedReplyList="cannedReplyList"
                                            :plugins="{images: true, cannedReply: true, attachment: true, shortCode: true}"
                                            @selectUploadFile="selectUploadFile"
                                        >
                                            <template v-slot:top>
                                                <div :class="{'bg-gray-200': uploadingFileProgress > 0}" class="h-1 w-full">
                                                    <div :style="{width: uploadingFileProgress + '%'}" class="bg-blue-500 py-0.5"></div>
                                                </div>
                                            </template>
                                        </input-wysiwyg>
                                    </div>
                                </div>
                                <div v-if="ticket.attachments.length > 0" class="col-span-3">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-2">
                                        <template v-for="(attachment, index) in ticket.attachments">
                                            <attachment :details="attachment" v-on:remove="removeAttachment(index)"/>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-100 text-right px-4 py-3 sm:px-6">
                        <div class="inline-flex">
                            <router-link
                                class="btn btn-secondary shadow-sm rounded-md mr-4"
                                to="/dashboard/tickets"
                            >
                                {{ $t('Cancel') }}
                            </router-link>
                            <button
                                class="btn btn-green shadow-sm rounded-md"
                                type="submit"
                            >
                                {{ $t('Create ticket') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <input ref="fileInput" hidden type="file" @change="uploadFile($event)">
    </main>
</template>

<script>
export default {
    name: "new",
    metaInfo() {
        return {
            title: this.$i18n.t('Create ticket')
        }
    },
    data() {
        return {
            loading: {
                form: true,
                file: false,
            },
            uploadingFileProgress: 0,
            ticket: {
                user_id: null,
                subject: null,
                concern_id: null,
                voucher_code: null,
                department_id: null,
                status_id: 1,
                priority_id: 1,
                body: '',
                attachments: [],
                scheduled_visit_at: null,
            },
            departmentList: [],
            userList: [],
            statusList: [],
            priorityList: [],
            concernList: [],
            departmentConcerns: {},
            cannedReplyList: [],
        }
    },
    mounted() {
        this.getFilters();
        this.getCannedReplies();
    },
    computed: {
        filteredConcerns() {
            if (!this.ticket.department_id) {
                return [];
            }
            return this.departmentConcerns[this.ticket.department_id] || [];
        }
    },
    watch: {
        'ticket.department_id': function(newVal, oldVal) {
            if (newVal !== oldVal) {
                this.ticket.concern_id = null;
                if (newVal) {
                    this.getConcernsByDepartment(newVal);
                }
            }
        },
        'ticket.user_id': function(newVal, oldVal) {
            if (newVal !== oldVal && newVal) {
                // When user is selected, get departments for that user's condo location
                this.getDepartmentsByUserCondoLocation(newVal);

                // Reset department and concern selections
                this.ticket.department_id = null;
                this.ticket.concern_id = null;
            }
        }
    },
    methods: {
        setVisitTimeToNow() {
            // Get current date and time in local timezone
            const now = moment();

            // Format as YYYY-MM-DDThh:mm which is the format expected by datetime-local input
            this.ticket.scheduled_visit_at = now.format('YYYY-MM-DD[T]HH:mm');

            // Show a notification to confirm
            this.$notify({
                title: this.$i18n.t('Visit Time Set').toString(),
                text: this.$i18n.t('Visit time set to now').toString(),
                type: 'success'
            });
        },
        getFilters() {
            const self = this;
            self.loading.form = true;
            axios.get('api/dashboard/tickets/filters').then(function (response) {
                self.userList = response.data.customers;
                // We'll load departments based on the selected user's condo location
                self.statusList = response.data.statuses;
                self.priorityList = response.data.priorities;
                self.loading.form = false;
            }).catch(function () {
                self.loading.form = false;
            })
        },
        getDepartmentsByUserCondoLocation(userId) {
            const self = this;
            if (!userId) return;

            self.loading.form = true;
            axios.get('api/dashboard/tickets/departments-by-condo-location', {
                params: {
                    user_id: userId
                }
            }).then(function (response) {
                self.departmentList = response.data;
                self.loading.form = false;
            }).catch(function (error) {
                self.loading.form = false;
                self.$notify({
                    title: self.$i18n.t('Error').toString(),
                    text: error.response ? (error.response.data.message || self.$i18n.t('Failed to load departments')) : self.$i18n.t('Failed to load departments'),
                    type: 'error'
                });
            });
        },
        getConcernsByDepartment(departmentId) {
            const self = this;
            axios.get('api/tickets/departments/' + departmentId + '/concerns').then(function (response) {
                self.$set(self.departmentConcerns, departmentId, response.data);
            })
        },
        getCannedReplies() {
            const self = this;
            axios.get('api/dashboard/tickets/canned-replies').then(function (response) {
                self.cannedReplyList = response.data;
            })
        },
        saveTicket() {
            const self = this;
            self.loading.form = true;

            // Create a copy of the ticket data to avoid modifying the original
            const ticketData = { ...self.ticket };

            // If scheduled_visit_at is set, ensure it has a time component and
            // is properly formatted using ISO standard for consistent handling
            if (ticketData.scheduled_visit_at) {
                // Parse the datetime-local input value
                // The datetime-local input returns a string in the format YYYY-MM-DDThh:mm
                // which is interpreted as local time by the browser

                // Use moment.js to handle the timezone conversion properly
                // First create a moment object in the local timezone
                const localMoment = moment(ticketData.scheduled_visit_at);

                // Check if the time part is missing or set to midnight (browser default)
                // If so, set it to a reasonable time (4:00 PM)
                if (localMoment.hour() === 0 && localMoment.minute() === 0) {
                    localMoment.hour(16).minute(0); // Set to 4:00 PM
                }

                // Convert to UTC for storage in the database
                const utcMoment = localMoment.utc();

                // Set the value as an ISO string for the API
                ticketData.scheduled_visit_at = utcMoment.format();

                // Log for debugging
                console.log('Admin dashboard - Scheduled visit time set to:', localMoment.format('YYYY-MM-DD h:mm A'));
                console.log('UTC time for storage:', utcMoment.format());
            }

            axios.post('api/dashboard/tickets', ticketData).then(function (response) {
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: self.$i18n.t('Data saved correctly').toString(),
                    type: 'success'
                });
                self.$router.push('/dashboard/tickets/' + response.data.ticket.uuid + '/manage');
            }).catch(function () {
                self.loading.form = false;
            });
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
                self.ticket.attachments.push(response.data);
            }).catch(function () {
                self.loading.file = false;
                self.uploadingFileProgress = 0;
                self.$refs.fileInput.value = null;
            });
        },
        removeAttachment(attachment) {
            this.ticket.attachments.splice(attachment, 1);
        }
    },
}
</script>
