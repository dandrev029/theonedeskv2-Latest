<template>
  <div class="flex flex-col h-screen bg-gray-50 dark:bg-gray-800">
    <ChatHeader
      :contactName="ticket.user ? ticket.user.name : (ticket.customer_name || $t('Customer'))"
      :contactAvatar="ticket.user ? (ticket.user.avatar !== 'gravatar' ? ticket.user.avatar : ticket.user.gravatar) : defaultAvatar"
      :ticketStatus="ticket.status"
      :isOnline="isUserOnline"
      :department="ticket.department ? ticket.department.name : null"
      :returnUrl="returnUrl"
      :lastActive="ticket.updated_at || ticket.created_at"
    />

    <!-- Chat Messages Area -->
    <div ref="chatMessagesArea" class="flex-grow overflow-y-auto p-4">
      <loading :status="loading.messages" class="mt-10"/>

      <!-- Date Header -->
      <div v-if="ticket.created_at" class="text-center my-4">
        <div class="inline-block px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded-full text-xs text-gray-700 dark:text-gray-300">
          {{ formatDateHeader(ticket.created_at) }}
        </div>
      </div>

      <!-- Messages -->
      <div>
        <ChatMessage
          v-for="(reply, index) in ticket.ticketReplies"
          :key="reply.id"
          :message="mapReplyToMessage(reply)"
          :isSender="reply.user_id === $store.state.user.id"
          @content-loaded="scrollToBottomIfNear"
        />
      </div>

      <div v-if="!loading.messages && ticket.ticketReplies && ticket.ticketReplies.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-10">
        {{ $t('No messages in this conversation yet.') }}
      </div>
    </div>

    <ChatInput @send-message="handleSendMessage" @file-selected="handleFileSelected" :uploadingFileProgress="uploadingFileProgress"/>
  </div>
</template>

<script>
import ChatHeader from './ChatHeader.vue';
import ChatMessage from './ChatMessage.vue';
import ChatInput from './ChatInput.vue';
import Loading from '../elements/loading.vue'; // Assuming this path is correct
import moment from 'moment';

export default {
  name: 'ChatInterface',
  components: {
    ChatHeader,
    ChatMessage,
    ChatInput,
    Loading,
  },
  props: {
    ticketUuid: {
      type: String,
      required: true,
    },
    returnUrl: {
      type: String,
      default: '/tickets/list',
    },
  },
  data() {
    return {
      loading: {
        ticket: true,
        messages: true,
        sendingReply: false,
        file: false,
      },
      ticket: {
        user: null,
        customer_name: null,
        ticketReplies: [],
      },
      defaultAvatar: '/images/avatar_default.png', // Add a default avatar
      uploadingFileProgress: 0,
      attachmentsForNextReply: [],
    };
  },
  computed: {
    isUserOnline() {
      // This is a placeholder - in a real app, you would implement actual online status checking
      // For now, we'll return true for demonstration purposes
      return this.ticket && this.ticket.user && this.ticket.user.id ? true : false;
    }
  },
  mounted() {
    this.fetchTicketAndReplies();
    // Optionally, set up Pusher for real-time updates if configured
    // this.listenForNewReplies();
  },
  updated() {
    this.scrollToBottom();
  },
  methods: {
    formatDateHeader(date) {
      if (!date) return '';
      return moment(date).format('M/D/YYYY');
    },
    fetchTicketAndReplies() {
      const self = this;
      self.loading.ticket = true;
      self.loading.messages = true;
      axios.get(`api/tickets/${self.ticketUuid}`)
        .then(response => {
          self.ticket = response.data;
          self.loading.ticket = false;
          self.loading.messages = false;
          self.$nextTick(() => {
            self.scrollToBottom();
          });
        })
        .catch(error => {
          console.error('Error fetching ticket details:', error);
          self.loading.ticket = false;
          self.loading.messages = false;
          self.$notify({
            title: self.$i18n.t('Error').toString(),
            text: self.$i18n.t('Could not load ticket details.').toString(),
            type: 'error',
          });
        });
    },
    mapReplyToMessage(reply) {
      // Basic mapping, can be expanded for different attachment types
      let messageType = 'text';
      let attachment = null;

      if (reply.attachments && reply.attachments.length > 0) {
        const firstAttachment = reply.attachments[0];
        // Simple type detection based on file extension or mime_type
        if (firstAttachment.mime_type && firstAttachment.mime_type.startsWith('image/')) {
          messageType = 'image';
        } else if (firstAttachment.mime_type === 'application/pdf') {
          messageType = 'file'; // Or 'pdf' if you want specific handling
        } else if (firstAttachment.mime_type && firstAttachment.mime_type.startsWith('audio/')) {
          messageType = 'audio';
        } else {
          messageType = 'file'; // Default for other attachments
        }
        attachment = {
          url: firstAttachment.url,
          name: firstAttachment.name,
          mime_type: firstAttachment.mime_type, // Ensure mime_type is passed
          // Add other attachment properties if needed
        };
      }

      return {
        id: reply.id,
        body: reply.body,
        created_at: reply.created_at,
        user: reply.user, // For sender/recipient info if needed in ChatMessage
        type: messageType,
        attachment: attachment,
      };
    },
    handleSendMessage(messageData) {
      const self = this;
      if (self.loading.sendingReply) return;

      if (!messageData.body.trim() && self.attachmentsForNextReply.length === 0) {
        self.$notify({
          title: self.$i18n.t('Error').toString(),
          text: self.$i18n.t('Reply message cannot be empty').toString(),
          type: 'error'
        });
        return;
      }

      self.loading.sendingReply = true;
      const payload = {
        body: messageData.body,
        attachments: self.attachmentsForNextReply, // Send the full attachment objects
        // status_id: this.ticket.status_id, // Or let backend handle status
      };

      axios.post(`api/tickets/${self.ticketUuid}/reply`, payload)
        .then(response => {
          self.ticket.ticketReplies.push(response.data.reply); // Assuming API returns the new reply
          self.attachmentsForNextReply = []; // Clear attachments after sending
          self.$nextTick(() => {
            self.scrollToBottom();
          });
          self.$notify({
            title: self.$i18n.t('Success').toString(),
            text: self.$i18n.t('Reply sent successfully').toString(),
            type: 'success',
          });
        })
        .catch(error => {
          console.error('Error sending reply:', error);
          self.$notify({
            title: self.$i18n.t('Error').toString(),
            text: error.response && error.response.data.message
                  ? error.response.data.message
                  : self.$i18n.t('Could not send reply.').toString(),
            type: 'error',
          });
        })
        .finally(() => {
          self.loading.sendingReply = false;
        });
    },
    handleFileSelected(file) {
      const self = this;
      if (self.loading.file) {
        self.$notify({
          title: self.$i18n.t('Info').toString(),
          text: self.$i18n.t('Another file upload is in progress.').toString(),
          type: 'info'
        });
        return;
      }
      self.loading.file = true;
      const formData = new FormData();
      formData.append('file', file);

      axios.post('api/tickets/attachments', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: progressEvent => {
          self.uploadingFileProgress = Math.round((progressEvent.loaded / progressEvent.total) * 100);
        },
      })
      .then(response => {
        self.attachmentsForNextReply.push(response.data); // Store full attachment object
        self.$notify({
          title: self.$i18n.t('Success').toString(),
          text: self.$i18n.t('File uploaded. It will be attached to your next reply.').toString(),
          type: 'success'
        });
      })
      .catch(error => {
        console.error('Error uploading file:', error);
        self.$notify({
          title: self.$i18n.t('Error').toString(),
          text: self.$i18n.t('Could not upload file.').toString(),
          type: 'error',
        });
      })
      .finally(() => {
        self.loading.file = false;
        self.uploadingFileProgress = 0;
      });
    },
    scrollToBottom() {
      const chatArea = this.$refs.chatMessagesArea;
      if (chatArea) {
        chatArea.scrollTop = chatArea.scrollHeight;
      }
    },
    scrollToBottomIfNear() {
      const chatArea = this.$refs.chatMessagesArea;
      if (chatArea) {
        // If user is already near the bottom, scroll down. Otherwise, they might be reading older messages.
        const threshold = 100; // Pixels from bottom
        if (chatArea.scrollHeight - chatArea.scrollTop - chatArea.clientHeight < threshold) {
          this.scrollToBottom();
        }
      }
    },
    // listenForNewReplies() {
    //   if (window.Echo) {
    //     window.Echo.private(`tickets.${this.ticketUuid}`) // Adjust channel name as per your backend
    //       .listen('.TicketReplied', (event) => { // Adjust event name
    //         this.ticket.ticketReplies.push(this.mapReplyToMessage(event.reply));
    //         this.$nextTick(() => {
    //           this.scrollToBottomIfNear();
    //         });
    //       });
    //   }
    // }
  },
  beforeDestroy() {
    // if (window.Echo) {
    //   window.Echo.leave(`tickets.${this.ticketUuid}`);
    // }
  }
};
</script>

<style scoped>
/* Ensure the chat area takes up available space and scrolls */
.h-screen {
  height: calc(100vh - theme('spacing.16')); /* Adjust 16 based on your actual header/nav height if this is part of a larger layout */
}
/* For standalone page, 100vh is fine */
</style>
