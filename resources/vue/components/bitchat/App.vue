<template>
  <div>
    <TransitionGroup enter-from-class="chat-opacity-0 chat-translate-y-5 chat-scale-0"
                     enter-active-class="chat-transition chat-duration-200 chat-transform chat-ease chat-scale-100"
                     leave-active-class="chat-transition chat-duration-100 chat-transform chat-ease chat-scale-100"
                     leave-to-class="chat-opacity-0 chat-translate-y-5 chat-scale-0">
      <ChatBody ref="chat" v-if="show && connected" @onLogout="onLogout" :messages="history" @onSend="onSend" :connected="connected" :endChat="endChat"/>
      <ChatLogin v-else-if="show && !connected" @onAuth="onAuth"></ChatLogin>
    </TransitionGroup>

<!--    <button @click="onShow"-->
<!--            class="chat-fixed chat-z-40 chat-right-5 chat-bottom-5 chat-shadow-lg chat-flex chat-justify-center chat-items-center chat-w-14 chat-h-14 chat-bg-indigo-500 chat-rounded-full focus:chat-outline-none hover:chat-bg-indigo-600 focus:chat-bg-indigo-600 chat-transition chat-duration-300 chat-ease">-->
<!--      <TransitionGroup-->
<!--          enter-from-class="chat-opacity-0 -chat-rotate-45 chat-scale-75"-->
<!--          enter-active-class="chat-transition chat-duration-200 chat-transform chat-ease"-->
<!--          leave-active-class="chat-transition chat-duration-100 chat-transform chat-ease"-->
<!--          leave-to-class="chat-opacity-0 -chat-rotate-45"-->
<!--      >-->
<!--        <svg v-if="!show" class="chat-w-6 chat-h-6 chat-text-white chat-absolute" xmlns="http://www.w3.org/2000/svg" width="16"-->
<!--             height="16"-->
<!--             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"-->
<!--             stroke-linejoin="round">-->
<!--          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>-->
<!--        </svg>-->

<!--        <svg v-else class="chat-w-6 chat-h-6 chat-text-white chat-absolute" xmlns="http://www.w3.org/2000/svg" width="16" height="16"-->
<!--             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"-->
<!--             stroke-linejoin="round">-->
<!--          <line x1="18" y1="6" x2="6" y2="18"></line>-->
<!--          <line x1="6" y1="6" x2="18" y2="18"></line>-->
<!--        </svg>-->
<!--      </TransitionGroup>-->
<!--    </button>-->

  </div>
</template>

<script setup>
import {provide, reactive, ref} from "vue";
import io from "./utils/socket";
import ChatBody from './components/ChatBody.vue'
import ChatLogin from './components/ChatLogin.vue'
import useAudio from "./utils/audio";

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    }
})


const message = ref()
const upload = ref()
const connected = ref(false)
const endChat = ref(false)
const history = reactive([])
const session = ref(localStorage.getItem("sessionChat"))
const chat = ref(null)

if (session.value && !connected.value) {
    io.auth = {sessionID: session.value};
    io.connect();
}

provide('io', io)

const {createAudio} = useAudio()

function onSend(new_message) {
  history.push(new_message)
  io.emit('private message', new_message)
  setTimeout(() => chat.value.scrollEnd(), 100)
}

function onAuth(auth) {
  io.auth = auth;
  io.connect();
}

io.on("session", ({userID, sessionID, username, email, color, status}) => {
  endChat.value = !Boolean(status)
  io.auth = {userID, sessionID, username, email, color};
  localStorage.setItem("sessionChat", sessionID);
  session.value = sessionID
  io.userID = userID;
  connected.value = true
});

io.on("chat history", (messages) => {
  history.push(...messages)
});

io.on("private message", (message) => {
  history.push(message)
  setTimeout(() => chat.value.scrollEnd(), 100)
  createAudio();
});

io.on("end session", () => {
  endChat.value = true
});

io.on("connect_error", (err) => {
  if (err.message === "invalid username" || err.message === "invalid Session") {
    onLogout();
  }
});

function onLogout() {
  io.disconnect();
  connected.value = false
  localStorage.removeItem("sessionChat")
  session.value = null
  history.splice(0)
}

</script>

<style lang="scss">
@import "./assets/css/app.265c7dd5.css";
</style>
