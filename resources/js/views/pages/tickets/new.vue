<template>
    <div class="py-10">
        <header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-5">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="py-0.5 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:leading-9 sm:truncate">
                            {{ $t('New ticket') }}
                        </h2>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <router-link
                            class="btn btn-blue shadow-sm rounded-md"
                            to="/tickets/list"
                        >
                            {{ $t('Return to tickets list') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </header>
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mt-10 my-6 bg-white shadow overflow-hidden sm:rounded-md">
                    <loading :status="loading.form"/>
                    <form @submit.prevent="saveTicket">
                        <div class="bg-white md:grid md:grid-cols-3 px-4 py-5">
                            <div class="md:col-span-2">
                                <div class="grid grid-cols-3 gap-6">
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
                                    <div v-if="departmentList.length > 0" class="col-span-3">
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
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div v-if="isWifiDepartment" class="col-span-3">
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
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input
                                                id="scheduled_visit_at"
                                                v-model="ticket.scheduled_visit_at"
                                                type="datetime-local"
                                                class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                :placeholder="$t('Select date and time for visit')"
                                            >
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">{{ $t('Select your preferred date and time for a visit') }}</p>
                                    </div>
                                    <div class="col-span-3">
                                        <label class="block text-sm font-medium leading-5 text-gray-700" for="priority">{{ $t('Priority') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input-select
                                                id="priority"
                                                v-model="ticket.priority_id"
                                                :options="priorityList"
                                                option-label="name"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-span-3">
                                        <label class="block text-sm font-medium leading-5 text-gray-700" for="ticket_body">{{ $t('Ticket body') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input-wysiwyg
                                                id="ticket_body"
                                                v-model="ticket.body"
                                                :plugins="{images: true, attachment: true}"
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
                        <div class="bg-gray-100 px-4 py-3 sm:px-6">
                            <div class="inline-flex">
                                <button
                                    class="btn btn-green shadow-sm rounded-md"
                                    type="submit"
                                >
                                    {{ $t('Create ticket') }}
                                </button>
                            </div>
                        </div>
                    </form>
                    <input ref="fileInput" hidden type="file" @change="uploadFile($event)">
                </div>
            </div>
        </main>
    </div>
</template>

<script>
export default {
    name: "index",
    metaInfo() {
        return {
            title: this.$i18n.t('New ticket')
        }
    },
    data() {
        return {
            loading: {
                form: false,
                file: false,
            },
            uploadingFileProgress: 0,
            ticket: {
                subject: null,
                concern_id: null,
                voucher_code: null,
                department_id: null,
                priority_id: null,
                body: '',
                attachments: [],
                scheduled_visit_at: null,
            },
            departmentList: [],
            concernList: [],
            departmentConcerns: {},
            priorityList: [],
        }
    },
    mounted() {
        this.getDepartmentsByCondoLocation();
        this.getPriorities();
    },
    computed: {
        filteredConcerns() {
            if (!this.ticket.department_id) {
                return [];
            }
            return this.departmentConcerns[this.ticket.department_id] || [];
        },
        isWifiDepartment() {
            const wifiKeywords = ['wifi', 'wireless', 'internet', 'network', 'connection'];
            const selectedDepartment = this.departmentList.find(dept => dept.id === this.ticket.department_id);
            if (!selectedDepartment) return false;

            const departmentName = selectedDepartment.name.toLowerCase();
            return wifiKeywords.some(keyword => departmentName.includes(keyword));
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
        }
    },
    methods: {
        getDepartments() {
            const self = this;
            self.loading.form = true;
            axios.get('api/tickets/departments').then(function (response) {
                self.departmentList = response.data;
                self.loading.form = false;
            }).catch(function () {
                self.loading.form = false;
            });
        },
        getDepartmentsByCondoLocation() {
            const self = this;
            self.loading.form = true;
            axios.get('api/tickets/user-departments-by-location').then(function (response) {
                self.departmentList = response.data;
                self.loading.form = false;
            }).catch(function (error) {
                self.loading.form = false;
                // Fall back to regular departments endpoint if the filtered one fails
                self.getDepartments();
                self.$notify({
                    title: self.$i18n.t('Warning').toString(),
                    text: self.$i18n.t('Could not load department filters. Showing all available departments.').toString(),
                    type: 'warning'
                });
            });
        },
        getConcernsByDepartment(departmentId) {
            const self = this;
            self.loading.form = true;
            axios.get('api/tickets/departments/' + departmentId + '/concerns').then(function (response) {
                self.$set(self.departmentConcerns, departmentId, response.data);
                self.loading.form = false;
            }).catch(function () {
                self.loading.form = false;
            });
        },

        getPriorities() {
            const self = this;
            self.loading.form = true;
            axios.get('api/tickets/priorities').then(function (response) {
                self.priorityList = response.data;
                self.loading.form = false;
            }).catch(function () {
                self.loading.form = false;
            });
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
                console.log('Scheduled visit time set to:', localMoment.format('YYYY-MM-DD h:mm A'));
                console.log('UTC time for storage:', utcMoment.format());
            }

            axios.post('api/tickets', ticketData).then(function (response) {
                self.$notify({
                    title: self.$i18n.t('Success').toString(),
                    text: self.$i18n.t('Data saved correctly').toString(),
                    type: 'success'
                });
                self.$router.push('/tickets/' + response.data.ticket.uuid);
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
                'api/tickets/attachments',
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
    }
}
</script>
