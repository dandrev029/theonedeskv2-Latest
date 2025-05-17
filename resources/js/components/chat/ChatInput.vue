<template>
  <div class="p-3 border-t border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700">
    <!-- Uploading progress -->
    <div v-if="uploadingFileProgress > 0" class="h-1 w-full mb-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
      <div :style="{width: uploadingFileProgress + '%'}" class="h-full bg-green-500 transition-all duration-300"></div>
    </div>

    <div class="flex items-center">
      <!-- Attachment Button -->
      <button @click="triggerAttachment" class="p-2 text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-green-400 transition-colors">
        <svg-vue icon="font-awesome/paperclip-solid" class="h-5 w-5"></svg-vue>
      </button>
      <input ref="fileInput" type="file" @change="handleFileUpload" class="hidden" />

      <!-- Message Input -->
      <input
        type="text"
        v-model="newMessage"
        @keyup.enter="sendMessage"
        :placeholder="$t('Type a message...')"
        class="flex-grow p-2 mx-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
      />

      <!-- Send Button -->
      <button
        @click="sendMessage"
        class="p-2 text-white bg-green-500 hover:bg-green-600 rounded-lg flex items-center justify-center h-10 w-10 transition-colors"
        :disabled="!newMessage.trim()"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
          <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
// import { VEmojiPicker } from 'v-emoji-picker'; // Example emoji picker

export default {
  name: 'ChatInput',
  // components: { VEmojiPicker },
  props: {
    uploadingFileProgress: {
      type: Number,
      default: 0,
    }
  },
  data() {
    return {
      newMessage: '',
      // showEmojiPicker: false, // Removed
    };
  },
  methods: {
    sendMessage() {
      if (this.newMessage.trim() === '' && (!this.attachments || this.attachments.length === 0)) {
        // If message is empty and no attachments, maybe trigger voice recording or do nothing
        if (!this.newMessage.trim()) {
          // console.log('Trigger voice recording'); // Placeholder for voice recording
          return;
        }
      }
      this.$emit('send-message', { body: this.newMessage, type: 'text' }); // Type can be dynamic
      this.newMessage = '';
      // this.showEmojiPicker = false; // Removed: Hide picker after sending
    },
    // toggleEmojiPicker() { // Removed
    //   this.showEmojiPicker = !this.showEmojiPicker;
    // },
    // onEmojiSelect(emoji) { // Removed
    //   this.newMessage += emoji.data;
    // },
    triggerAttachment() {
      this.$refs.fileInput.click();
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.$emit('file-selected', file);
      }
      this.$refs.fileInput.value = null; // Reset file input
    },
  },
};
</script>

<style scoped>
/* Add any specific styles if Tailwind classes are not sufficient */
</style>
