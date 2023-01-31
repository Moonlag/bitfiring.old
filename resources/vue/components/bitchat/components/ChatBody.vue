<template>
  <div
      class="chat-fixed chat-overflow-hidden chat-flex chat-flex-col chat-z-9999 chat-bottom-[100px] chat-top-0 chat-right-0 chat-left-0 sm:chat-top-auto sm:chat-right-5 sm:chat-left-auto chat-h-[calc(100%-95px)] sm:chat-w-[350px] chat-overflow-auto chat-min-h-[250px] chat-h-[600px] chat-bg-white chat-shadow-2xl chat-rounded-md"
      :class="{'chat-fullscreen': fullscreen}">
    <div
        class="chat-flex chat-items-center chat-justify-between chat-border-b chat-p-2 chat-bg-indigo-600 chat-text-white">
      <!-- user info -->
      <div class="chat-flex chat-items-center">
        <div
            class="chat-flex chat-items-center chat-justify-center chat-h-10 chat-w-10 chat-rounded-full chat-flex-shrink-0 chat-bg-indigo-500 chat-text-indigo-200">
          {{ io.auth?.username.charAt(0).toUpperCase() }}
        </div>
        <div class="chat-pl-2">
          <div class="chat-font-semibold">
            <a class="hover:chat-underline" href="#">{{ io.auth.username }}</a>
          </div>
          <div class="chat-text-xs chat-text-gray-300">{{ connected ? 'Online' : 'Offline' }}</div>
        </div>
      </div>
      <!-- end user info -->
      <!-- chat box action -->
      <div>

        <a @click.prevent="onLogout" class="chat-inline-flex hover:chat-bg-indigo-700 chat-rounded-full chat-p-2"
           href="#">
          <svg xmlns="http://www.w3.org/2000/svg" class="chat-h-6 chat-w-6" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </a>

        <a @click.prevent="fullscreen = !fullscreen"
           class="chat-inline-flex hover:chat-bg-indigo-700 chat-rounded-full chat-p-2" href="#">
          <svg xmlns="http://www.w3.org/2000/svg" class="chat-h-6 chat-w-6" fill="none" viewBox="0 0 24 24"
               stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
          </svg>
        </a>

      </div>
      <!-- end chat box action -->
    </div>

    <div ref="scroll" class="chat-flex-1 chat-px-4 chat-py-4 chat-overflow-y-auto">
      <!-- chat message -->

      <template v-for="(history, idx) in messages" :key="history">
        <component :is="layout[history.sender]"
                   :history="history"></component>
      </template>

      <!-- end chat message -->
    </div>

    <div v-if="!endChat" class="chat-flex chat-items-center chat-border-t chat-p-2">
      <!-- chat input action -->
      <div class="chat-w-full chat-border chat-rounded-3xl chat-px-2 chat-relative chat-beforeUpload chat-mr-2">
        <Upload ref="upload"></Upload>
        <div class="chat-flex chat-flex-row">
          <button @click="upload?.onAppend"
                  class="chat-flex chat-items-center chat-justify-center chat-h-10 chat-w-10 chat-text-gray-400 hover:chat-text-gray-600 chat-ml-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="chat-h-6 chat-w-6" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </button>
          <!-- end chat input action -->

          <label for="message" class="chat-w-full chat-py-2">
            <AutoSizeTextarea ref="textarea" name="message" @update:value="message = $event" :value="message"
                              @onEnter="onSend" placeholder="Type your message...."/>
          </label>
        </div>
      </div>

      <!-- chat send action -->

      <div>
        <button
            class="chat-flex chat-items-center chat-justify-center chat-h-10 chat-w-10 chat-rounded-full chat-bg-gray-200 hover:chat-bg-gray-300 chat-text-indigo-800 chat-text-white"
            @click="onSend">
          <svg class="chat-w-5 chat-h-5 chat-transform chat-rotate-90 -mr-px" fill="none" stroke="currentColor"
               viewBox="0 0 24 24"
               xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
          </svg>
        </button>
      </div>

      <!-- end chat send action -->
    </div>
  </div>
</template>

<script setup>
import {ref, reactive, inject, markRaw, onMounted, nextTick} from "vue";
import AutoSizeTextarea from './UI/AutoSizeTextarea.vue'
import Upload from './UI/Upload.vue'
import MessagePlayer from './Message/MessagePlayer.vue'
import MessageOperator from './Message/MessageOperator.vue'
import axios from "axios";
import $ from 'jquery';

const message = ref('')
const upload = ref()
const textarea = ref()
const scroll = ref()
const fullscreen = ref(false)
const io = inject('io')

const layout = reactive({player: markRaw(MessagePlayer), operator: markRaw(MessageOperator)})

const props = defineProps({
  messages: {
    type: Array,
    required: true
  },
  connected: {
    type: Boolean,
    default: false
  },
  endChat: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['onLogout', 'onSend'])

function onLogout() {
  emit('onLogout')
}

async function onSend() {
  if (!event.shiftKey) {
    event.preventDefault();
  } else {
    return;
  }

  if (!message.value.length) {
    return;
  }

  const files = await upload.value.onUpload();
  const msg = message.value.replace(/\s+$/, '').replace(/^\s+/g, '').trim();

  if (msg.length > 1 || files) {
    const new_message = {
      content: msg,
      sender: 'player',
      receiver: 'operator',
      name: io.auth.username,
      created_at: new Date(),
      files
    }

    emit('onSend', new_message)

    message.value = '';
    setTimeout(() => textarea.value.resize(), 1)
    upload.value.onReset();
  }
}

function scrollEnd() {
  $(scroll.value).stop().animate({scrollTop: $(scroll.value)[0].scrollHeight}, 700);
}

onMounted(async () => {
  await nextTick();
  scrollEnd()
})


defineExpose({
  scrollEnd
})
</script>

<style scoped>
.chat-z-9999 {
  z-index: 9999;
}

::-webkit-scrollbar {
  width: 8px;
  height: 1px;
}

::-webkit-scrollbar-button {
  width: 0px;
  height: 0px;
}

::-webkit-scrollbar-corner {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(229, 231, 235, 0.74);
  border: 0px none #ffffff;
  border-radius: 50px;
}

::-webkit-scrollbar-track {
  background: rgba(229, 231, 235, 0.33);
  border: 0px none #ffffff;
  border-radius: 50px;
}
</style>