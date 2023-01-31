<template>
  <!-- chat message -->

  <div class="chat-flex chat-items-center chat-mb-4">
    <div class="chat-flex-none chat-flex chat-flex-col chat-items-center chat-space-y-1 chat-mr-4">
      <div
          class="chat-flex chat-items-center chat-justify-center chat-h-10 chat-w-10 chat-rounded-full chat-flex-shrink-0 chat-bg-indigo-500 chat-text-indigo-200">
        {{ history.name.charAt(0).toUpperCase() }}
      </div>
    </div>
    <div class="chat-flex-1 chat-bg-indigo-400 chat-text-white chat-p-2 chat-rounded-lg chat-mb-2 chat-relative">
      <div class="chat-flex chat-flex-wrap" v-if="history?.files">
        <a :href="`https://cdn.bitfiring.com/bitchat/${file.path}${file.name}.${file.extension}`" target="_blank"
           :download="file.original_name" v-for="file in history.files"
           class="chat-block chat-relative chat-p-1 chat-h-24 chat-w-full chat-text-black">
          <article tabindex="0"
                   class="chat-group chat-w-full chat-h-full chat-rounded-md focus:chat-outline-none focus:chat-shadow-outline chat-elative chat-bg-gray-100 chat-cursor-pointer chat-relative chat-shadow-sm">
            <img alt="upload preview"
                 class="chat-img-preview chat-hidden chat-w-full chat-h-full chat-sticky chat-object-cover chat-rounded-md chat-bg-fixed">

            <section
                class="chat-flex chat-flex-col chat-rounded-md chat-text-xs chat-break-words chat-w-full chat-h-full chat-z-20 chat-absolute chat-top-0 chat-py-2 chat-px-3">
              <h1 class="chat-flex-1 chat-text-base group-hover:chat-text-blue-800">{{ file.original_name }}</h1>
              <div class="chat-flex">
                    <span class="chat-p-1 chat-text-blue-800">
                      <i>
                        <svg class="chat-fill-current chat-w-4 chat-h-4 chat-ml-auto chat-pt-1"
                             xmlns="http://www.w3.org/2000/svg" width="24"
                             height="24" viewBox="0 0 24 24">
                          <path d="M15 2v5h5v15h-16v-20h11zm1-2h-14v24h20v-18l-6-6z"></path>
                        </svg>
                      </i>
                    </span>
                <p class="chat-relative chat-z-5 chat-p-1 chat-size chat-text-xs chat-text-gray-700">{{
                    file.size
                  }}kb</p>
                <button
                    class="chat-absolute chat-z-0 chat-top-6 chat-right-4 chat-ml-auto chat-ring chat-ring-indigo-200 chat-rounded-full focus:chat-outline-none chat-p-1 chat-rounded-md chat-text-indigo-200">
                  <svg xmlns="http://www.w3.org/2000/svg" class="chat-w-10 chat-h-10 chat-ml-auto" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                </button>
              </div>
            </section>
          </article>
        </a>
      </div>
      <div class="message">{{ history.content }}</div>

      <!-- arrow -->
      <div
          class="chat-absolute chat-left-0 chat-top-1/2 chat-transform -chat-translate-x-1/2 chat-rotate-45 chat-w-2 chat-h-2 chat-bg-indigo-400"></div>
      <!-- end arrow -->
    </div>
  </div>

  <!-- end chat message -->
</template>

<script setup>
import {computed} from "vue";
import {formatRelative, differenceInMinutes} from 'date-fns'
import axios from 'axios'

const props = defineProps({
  history: {
    type: Object,
    required: true
  }
})

// function forceFileDownload(response, title) {
//   console.log(title)
//   const url = window.URL.createObjectURL(new Blob([response.data]))
//   const link = document.createElement('a')
//   link.href = url
//   link.setAttribute('download', title)
//   document.body.appendChild(link)
//   link.click()
// }
//
// function downloadWithAxios(url, title) {
//   axios({
//     method: 'get',
//     url,
//     responseType: 'arraybuffer',
//   })
//       .then((response) => {
//         forceFileDownload(response, title)
//       })
//       .catch(() => console.log('error occured'))
// }
</script>

<style scoped>
.message {
  white-space: pre-wrap;
}
</style>
