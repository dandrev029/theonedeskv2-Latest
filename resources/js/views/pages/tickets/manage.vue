<template>
    <div class="py-4">
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <loading :status="loading.form"/>

                    <!-- Chat Interface Integration -->
                    <ChatInterface
                        v-if="ticket.uuid"
                        :ticketUuid="ticket.uuid"
                        returnUrl="/tickets/list"
                    />

                    <template v-else-if="!loading.form">
                         <div class="h-full flex border-t">
                            <div class="m-auto">
                                <div class="grid grid-cols-1 justify-items-center h-full w-full py-24">
                                    <div class="flex justify-center items-center">
                                        <svg-vue class="h-full h-auto w-48 mb-6" icon="undraw.task-list"></svg-vue>
                                    </div>
                                    <div class="flex justify-center items-center">
                                        <div class="w-full font-semibold text-2xl">{{ $t('Ticket data not available for chat.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
import ChatInterface from '../../../components/chat/ChatInterface.vue'; // Import the new chat interface

export default {
    name: "manage-ticket", // Changed name to be more specific
    components: { // Added ChatInterface to components
        ChatInterface,
    },
    metaInfo() {
        return {
            title: this.$i18n.t('Ticket details')
        }
    },
    data() {
        return {
            loading: {
                form: true,
                // reply and file loading states will be handled by ChatInterface
            },
            // replyForm, uploadingFileProgress, and ticketReply are no longer needed here
            ticket: {
                uuid: null, // Ensure uuid is initialized for the prop
                subject: null,
                created_at: null,
                status: null, // Ensure status is initialized
                // ticketReplies will be fetched by ChatInterface
            },
        }
    },
    filters: {
        momentFormatDateTimeAgo: function (value) {
            if (!value) return '';
            return moment(value).locale(window.app.app_date_locale || 'en').fromNow();
        },
        momentFormatDateTime: function (value) {
            if (!value) return '';
            // Parse the ISO string with the timezone information preserved
            return moment.utc(value).tz(window.app.app_timezone || 'UTC').locale(window.app.app_date_locale || 'en').format((window.app.app_date_format || 'YYYY-MM-DD') + ' h:mm A');
        },
    },
    mounted() {
        this.getTicketHeaderDetails(); // Renamed to reflect it only gets header details now
    },
    methods: {
        getTicketHeaderDetails() { // Renamed method
            const self = this;
            self.loading.form = true;
            axios.get('api/tickets/' + self.$route.params.uuid).then(function (response) {
                self.loading.form = false;
                // Only update ticket properties needed for the header
                self.ticket.uuid = response.data.uuid;
                self.ticket.subject = response.data.subject;
                self.ticket.created_at = response.data.created_at;
                self.ticket.status = response.data.status;
                self.ticket.scheduled_visit_at = response.data.scheduled_visit_at;
                // User and customer_name will be passed to ChatInterface via ticket prop,
                // and ChatInterface will handle displaying them in its header.
                self.ticket.user = response.data.user;
                self.ticket.customer_name = response.data.customer_name;

            }).catch(function () {
                self.loading.form = false; // Ensure loading is false on error
                self.$notify({
                    title: self.$i18n.t('Error').toString(),
                    text: self.$i18n.t('Could not load ticket details.').toString(),
                    type: 'error'
                });
                // Optionally redirect or show an error message specific to header loading
                // self.$router.push('/tickets/list'); // Or handle error differently
            });
        },
        // addReply, discardReply, selectUploadFile, uploadFile, removeAttachment methods are removed
        // as their functionality is now within ChatInterface.vue or its children.
    }
}
</script>
