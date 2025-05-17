<template>
  <div class="mb-4">
    <!-- Sender Message (Right side, green) -->
    <div v-if="isSender" class="flex justify-end">
      <div class="rounded-lg px-4 py-2 max-w-xs lg:max-w-md bg-green-500 text-white shadow-sm">
        <!-- Message Body -->
        <div v-if="message.type === 'text' || !message.type" v-html="message.body" class="text-sm break-words"></div>

        <!-- Image Attachment -->
        <div v-if="message.type === 'image' && message.attachment" class="mt-1">
          <img :src="message.attachment.url" alt="Image attachment" class="rounded max-w-full h-auto" style="max-height: 200px;" @load="scrollToBottomIfNear" @error="handleImageError"/>
          <p v-if="message.body" v-html="message.body" class="text-sm mt-1 break-words"></p>
        </div>

        <!-- File Attachment -->
        <div v-if="message.type === 'file' && message.attachment" class="mt-1 p-2 rounded bg-green-600 bg-opacity-30">
          <a :href="message.attachment.url" target="_blank" class="flex items-center text-white hover:underline">
            <svg-vue :icon="fileIcon" class="h-5 w-5 mr-2 flex-shrink-0"></svg-vue>
            <span class="text-sm font-medium truncate">{{ message.attachment.name || 'Attachment' }}</span>
          </a>
          <p v-if="message.body" v-html="message.body" class="text-sm mt-1 break-words"></p>
        </div>

        <!-- Audio Attachment -->
        <div v-if="message.type === 'audio' && message.attachment" class="mt-1">
          <audio controls :src="message.attachment.url" class="w-full">
            Your browser does not support the audio element.
          </audio>
          <p v-if="message.body" v-html="message.body" class="text-sm mt-1 break-words"></p>
        </div>

        <!-- Timestamp -->
        <div class="text-xs mt-1 text-white text-opacity-80 text-right">
          {{ message.created_at | momentFormatTime }}
        </div>
      </div>
    </div>

    <!-- Recipient Message (Left side with avatar) -->
    <div v-else class="flex items-start">
      <!-- User Avatar -->
      <div class="mr-2 flex-shrink-0">
        <img
          :src="message.user && message.user.avatar ? (message.user.avatar !== 'gravatar' ? message.user.avatar : message.user.gravatar) : defaultAvatar"
          alt="Avatar"
          class="h-8 w-8 rounded-full"
        />
      </div>

      <div class="flex flex-col">
        <!-- User Name and Department/Role -->
        <div class="flex items-center mb-1" v-if="message.user">
          <span class="font-medium text-sm text-gray-800 dark:text-white">{{ message.user.name }}</span>
          <span v-if="message.user.department" class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ message.user.department }}</span>
        </div>

        <!-- Message Bubble -->
        <div class="rounded-lg px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white shadow-sm">
          <!-- Message Body -->
          <div v-if="message.type === 'text' || !message.type" v-html="message.body" class="text-sm break-words"></div>

          <!-- Image Attachment -->
          <div v-if="message.type === 'image' && message.attachment" class="mt-1">
            <img :src="message.attachment.url" alt="Image attachment" class="rounded max-w-full h-auto" style="max-height: 200px;" @load="scrollToBottomIfNear" @error="handleImageError"/>
            <p v-if="message.body" v-html="message.body" class="text-sm mt-1 break-words"></p>
          </div>

          <!-- File Attachment -->
          <div v-if="message.type === 'file' && message.attachment" class="mt-1 p-2 rounded bg-gray-200 dark:bg-gray-600">
            <a :href="message.attachment.url" target="_blank" class="flex items-center text-blue-600 dark:text-blue-400 hover:underline">
              <svg-vue :icon="fileIcon" class="h-5 w-5 mr-2 flex-shrink-0"></svg-vue>
              <span class="text-sm font-medium truncate">{{ message.attachment.name || 'Attachment' }}</span>
            </a>
            <p v-if="message.body" v-html="message.body" class="text-sm mt-1 break-words"></p>
          </div>

          <!-- Audio Attachment -->
          <div v-if="message.type === 'audio' && message.attachment" class="mt-1">
            <audio controls :src="message.attachment.url" class="w-full">
              Your browser does not support the audio element.
            </audio>
            <p v-if="message.body" v-html="message.body" class="text-sm mt-1 break-words"></p>
          </div>

          <!-- Timestamp -->
          <div class="text-xs mt-1 text-gray-500 dark:text-gray-400">
            {{ message.created_at | momentFormatTime }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';

export default {
  name: 'ChatMessage',
  props: {
    message: {
      type: Object,
      required: true,
    },
    isSender: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      defaultAvatar: '/images/avatar_default.png',
    };
  },
  computed: {
    fileIcon() {
      if (!this.message.attachment) return 'file-extension/default';

      const mimeType = this.message.attachment.mime_type;
      const fileName = this.message.attachment.name || '';
      let extension = '';

      if (fileName.includes('.')) {
        extension = fileName.split('.').pop().toLowerCase();
      }

      // Prioritize extension for common types if mime type is generic (e.g. application/octet-stream)
      if (mimeType === 'application/octet-stream' || !mimeType) {
        if (['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'mp3', 'mp4', 'avi', 'mov'].includes(extension)) {
          return `file-extension/${extension}`;
        }
      }

      // MIME type mapping
      if (mimeType) {
        if (mimeType.startsWith('image/')) {
          const imgExt = mimeType.split('/')[1];
          if (['jpeg', 'jpg', 'png', 'gif', 'svg', 'bmp'].includes(imgExt)) return `file-extension/${imgExt}`;
        }
        if (mimeType.startsWith('audio/')) return 'file-extension/mp3'; // Or a generic audio icon
        if (mimeType.startsWith('video/')) return 'file-extension/mp4'; // Or a generic video icon
        if (mimeType === 'application/pdf') return 'file-extension/pdf';
        if (mimeType === 'application/zip' || mimeType === 'application/x-zip-compressed') return 'file-extension/zip';
        if (mimeType === 'application/x-rar-compressed') return 'file-extension/rar';
        if (mimeType === 'text/plain') return 'file-extension/txt';
        if (mimeType === 'text/html') return 'file-extension/html';
        if (mimeType === 'text/css') return 'file-extension/css';
        if (mimeType === 'application/javascript' || mimeType === 'text/javascript') return 'file-extension/js';
        if (mimeType === 'application/json') return 'file-extension/default'; // No json.svg, use default
        if (mimeType === 'application/xml' || mimeType === 'text/xml') return 'file-extension/xml';
        if (mimeType === 'application/msword') return 'file-extension/doc';
        if (mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') return 'file-extension/docx';
        if (mimeType === 'application/vnd.ms-excel') return 'file-extension/xls';
        if (mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') return 'file-extension/xlsx';
        if (mimeType === 'application/vnd.ms-powerpoint') return 'file-extension/ppt';
        if (mimeType === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') return 'file-extension/pptx';
      }

      // Fallback to extension if specific mime type not found or not specific enough
      if (['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'mp3', 'mp4', 'avi', 'mov', 'html', 'css', 'js', 'xml'].includes(extension)) {
        return `file-extension/${extension}`;
      }

      return 'file-extension/default';
    }
  },
  filters: {
    momentFormatTime: function (value) {
      if (!value) return '';
      return moment(value).locale(window.app.app_date_locale || 'en').format('h:mm A');
    },
  },
  methods: {
    scrollToBottomIfNear() {
      // Emit an event to parent to check if scroll to bottom is needed
      // This is useful for when images load and change content height
      this.$emit('content-loaded');
    },
    handleImageError(event) {
      // When an image fails to load, replace it with a fallback message
      console.error('Image failed to load:', event.target.src);

      // Replace the image with a fallback message
      const img = event.target;
      const parent = img.parentNode;

      // Create a fallback element
      const fallback = document.createElement('div');
      fallback.className = 'p-2 bg-gray-200 dark:bg-gray-600 rounded text-sm';
      fallback.innerHTML = `<svg-vue icon="font-awesome/image-solid" class="h-5 w-5 mr-2 inline-block"></svg-vue> ${this.$t('Image attachment unavailable')}`;

      // Replace the image with the fallback
      parent.replaceChild(fallback, img);
    }
  }
};
</script>

<style scoped>
/* Scoped styles for ChatMessage */
.max-w-xs { max-width: 20rem; /* 320px */ }
.lg\:max-w-md { max-width: 28rem; /* 448px */ }

audio {
  filter: invert(var(--tw-dark-mode-invert, 0)) sepia(var(--tw-dark-mode-sepia, 0%)) saturate(var(--tw-dark-mode-saturate, 100%)) hue-rotate(var(--tw-dark-mode-hue-rotate, 0deg)) brightness(var(--tw-dark-mode-brightness, 100%)) contrast(var(--tw-dark-mode-contrast, 100%));
}
.dark audio {
  --tw-dark-mode-invert: 100%;
  --tw-dark-mode-sepia: 10%;
  --tw-dark-mode-saturate: 50%;
  --tw-dark-mode-hue-rotate: 180deg; /* Adjust to match your dark theme's tint */
  --tw-dark-mode-brightness: 90%;
  --tw-dark-mode-contrast: 110%;
}
</style>
