<template>
  <div v-if="showUpload"
       style="top: -70px"
       class="chat-absolute chat-flex chat-bg-white chat-shadow-lg chat-rounded-lg chat-border-4 chat-border-white">
    <button @click="onAppend" class="chat-text-gray-400 chat-p-2 hover:chat-text-gray-700 hover:chat-bg-gray-100 chat-rounded-lg"
            type="submit">
                         <span class="chat-flex chat-text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" class="chat-h-5 chat-w-5 chat-mr-2" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload a File
                         </span>
      <span class="chat-flex chat-text-xs">Tip: Double-click the  <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="chat-h-4 chat-w-4 chat-ml-1" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg></span>
    </button>
  </div>
  <ul id="gallery" v-show="previews.length > 0 || drag" class="chat-flex chat-flex-1 chat-flex-wrap -chat-m-1 chat-pt-2">
    <li v-for="(pre, i) in files" :key="pre" class="chat-block chat-p-1 chat-h-24 chat-w-full">
      <article tabindex="0"
               class="chat-group chat-w-full chat-h-full chat-rounded-md focus:chat-outline-none focus:chat-shadow-outline chat-elative chat-bg-gray-100 chat-cursor-pointer chat-relative chat-shadow-sm">
        <img alt="upload chat-preview"
             class="chat-img-preview chat-hidden chat-w-full chat-h-full chat-sticky chat-object-cover chat-rounded-md chat-bg-fixed">

        <section
            class="chat-flex chat-flex-col chat-rounded-md chat-text-xs chat-break-words chat-w-full chat-h-full chat-z-20 chat-absolute chat-top-0 chat-py-2 chat-px-3">
          <h1 class="chat-flex-1 group-hover:chat-text-blue-800">{{ pre.name }}</h1>
          <div class="chat-flex">
              <span class="chat-p-1 chat-text-blue-800">
                <i>
                  <svg class="chat-fill-current chat-w-4 chat-h-4 chat-ml-auto chat-pt-1" xmlns="http://www.w3.org/2000/svg" width="24"
                       height="24" viewBox="0 0 24 24">
                    <path d="M15 2v5h5v15h-16v-20h11zm1-2h-14v24h20v-18l-6-6z"></path>
                  </svg>
                </i>
              </span>
            <p class="chat-p-1 chat-size chat-text-xs chat-text-gray-700">{{ pre.size }}kb</p>
            <button @click="deleteFile(i)"
                    class="chat-delete chat-ml-auto focus:chat-outline-none hover:chat-bg-gray-300 chat-p-1 chat-rounded-md chat-text-gray-800">
              <svg class="chat-pointer-events-none chat-fill-current chat-w-4 chat-h-4 chat-ml-auto"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path class="pointer-events-none"
                      d="M3 6l3 18h12l3-18h-18zm19-4v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.316c0 .901.73 2 1.631 2h5.711z"></path>
              </svg>
            </button>
          </div>
        </section>
      </article>
    </li>
  </ul>
  <input
      class="upload chat-pointer-events-none"
      type="file"
      style="z-index: 1"
      accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*"
      ref="uploadInput"
      @change="onPreviewFile"
      multiple
  />
</template>

<script setup>
import {ref} from "vue";
import axios from "axios";

const previews = ref([])
const files = ref([])
const showUpload = ref(false)
const drag = ref(false)
const uploadInput = ref(null)
const dropped = ref(0)
const errorMsg = ref("")
const info = ref(null)

const props = defineProps({
  max: {type: Number, default: 10},
  uploadMsg: String,
  maxError: String,
  fileError: String,
  clearAll: String,
})

const emit = defineEmits(['change'])

function onShowUpload() {
  showUpload.value = !showUpload.value
}

function closeShowUpload() {
  showUpload.value = false
}

function onAppend() {
  uploadInput.value.click();
}

async function onUpload() {
  return new Promise(async (resolve, reject) => {
    if (!files.value.length) return resolve();
    const formData = new FormData();
    files.value.forEach((file) => {
      formData.append(file.name, file);
    })

    const response = await axios.post('https://bitchat.bitfiring.com/api/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    resolve(response.data)
  })
}

function onPreviewFile(event) {
  if (dropped.value == 0) files.value.push(...event.currentTarget.files);
  let readers = [];
  if (!files.value.length) return;
  for (let i = 0; i < files.value.length; i++) {
    readers.push(readAsDataURL(files.value[i]));
  }
  Promise.all(readers).then((values) => {
    previews.value = values;
  });
}

function readAsDataURL(file) {
  return new Promise(function (resolve, reject) {
    let fr = new FileReader();
    fr.onload = function () {
      resolve(fr.result);
    };
    fr.onerror = function () {
      reject(fr);
    };
    fr.readAsDataURL(file);
  });
}

function deleteFile(index) {
  previews.value.splice(index, 1);
  files.value.splice(index, 1);
  uploadInput.value.value = null;
}

function drop(e) {
  let status = true;
  let filess = Array.from(e.dataTransfer.files)
  console.log(filess)
  if (e && filess) {
    filess.forEach((file) => {
      if (['application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'text/plain', 'application/pdf', 'image/png', 'image/jpeg', 'image/webp'].includes(file.type) === false) status = false;
    });
    if (status == true) {
      if (
          props.max &&
          filess.length + files.value.length > props.max
      ) {
        errorMsg.value = props.maxError
            ? props.maxError
            : `Maximum files is ` + props.max;
      } else {
        files.value.push(...filess);
        onPreviewFile(e);
        dragLeave()
      }
    } else {
      errorMsg.value = `Unsupported file type`;
    }
  }
  dropped.value = 0;
}

function onReset() {
  uploadInput.value.value = null;
  previews.value = [];
  files.value = [];
}

function dragOver(event) {
  dropped.value = 2
}

function dragLeave(event) {
  errorMsg.value = ''
}

defineExpose({
  closeShowUpload,
  onShowUpload,
  onAppend,
  onReset,
  onUpload,
  dragOver,
  dragLeave,
})
</script>

<style>
.upload {
  width: 100%;
  margin: auto;
  height: 100%;
  opacity: 0;
  position: absolute;
  background: red;
  display: block;
  z-index: 99;
}
</style>
