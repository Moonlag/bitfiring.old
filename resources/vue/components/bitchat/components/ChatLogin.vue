<template>
  <div
      class="chat-fixed chat-flex chat-overflow-hidden chat-flex-col chat-z chat-bottom-[100px] chat-top-0 chat-right-0 chat-h-auto chat-left-0 sm:chat-top-auto sm:chat-right-5 sm:chat-left-auto chat-h-[calc(100%-95px)] sm:chat-w-[350px] chat-overflow-auto chat-min-h-[250px] sm:chat-h-[600px] chat-bg-white chat-shadow-2xl chat-rounded-md">
    <div class="chat-flex chat-p-5 chat-flex-col chat-justify-center chat-items-center chat-h-32 chat-bg-indigo-600">
      <h3 class="chat-text-lg chat-text-white">How can we help?</h3>
      <p class="chat-text-white chat-opacity-50">We usually respond in a few minutes</p>
    </div>
    <LoadingComponent v-if="isLoading"></LoadingComponent>
    <div v-else class="chat-bg-gray-50 chat-flex-grow chat-p-6 chat-relative">
      <Auth v-if="isOffline" @onSubmit="onAuth"/>
      <Offline v-else/>
    </div>
  </div>
</template>

<script setup>
import {ref, onUnmounted} from "vue";
import Offline from './Form/Offline.vue'
import Auth from './Form/Auth.vue'
import LoadingComponent from './Form/LoadingComponent.vue'
import axios from "axios";

const isOffline = ref(false)
const isLoading = ref(true)


const emit = defineEmits(['onAuth'])


function onSend() {

}

function onAuth(auth) {
  emit('onAuth', auth)
}

async function onlineOperators() {
  setTimeout(() => isLoading.value = false, 1500)
  const {data} = await axios.get('https://bitchat.bitfiring.com:3002/operators')
  if (data) {
    isOffline.value = data?.isOnline || false

  }
}

onlineOperators()
</script>

<style scoped>
.chat-z{
  z-index: 99999!important;
}
</style>