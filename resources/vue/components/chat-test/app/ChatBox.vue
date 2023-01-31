<template>
  <div id="tricky-chat-box">
    <!--new_message_head-->
    <div class="chat">
      <div class="chat_area" style="height: 350px;">
        <ul class="chat-history">
          <component v-for="msg in history" :key="msg.id" :content="msg"
                     :is="layouts[msg.sender]"></component>
        </ul>
        <p class="typing-status"><b v-if="typing">Typing ...</b></p>
      </div>
    </div>
    <!--chat_area-->
    <div v-if="!endSession" class="message_write">
                 <textarea @keypress.enter="onMessage" v-model="message" class="chat-input-box"
                           contenteditable="true"></textarea>
      <button @click="onMessage" class="btn btn-primary btn-message_write">
        <svg fill="#fff" version="1.1" xmlns="http://www.w3.org/2000/svg" width="27" height="27"
             viewBox="0 0 32 32">
          <path
              d="M31.376 0c-0.191 0-0.422 0.054-0.691 0.168l-29.833 12.659c-1.074 0.456-1.142 1.334-0.151 1.951l8.43 5.251c0.991 0.617 2.301 1.94 2.912 2.939l5.053 8.274c0.29 0.474 0.64 0.71 0.977 0.71 0.372 0 0.727-0.286 0.97-0.851l12.758-29.805c0.345-0.808 0.148-1.296-0.426-1.297zM10.174 18.248l-6.833-4.257 22.925-9.726-14.756 15.006c-0.451-0.4-0.909-0.757-1.337-1.023zM17.898 28.602l-4.076-6.672c-0.241-0.394-0.558-0.814-0.912-1.231l14.825-15.075z"></path>
        </svg>
      </button>

      <div class="clearfix"></div>
    </div>
  </div>
</template>

<script setup>
import {markRaw, onMounted, nextTick} from "vue";
import MessageOther from "./part/MessageOther";
import MessageMe from "./part/MessageMe";
import $ from 'jquery';
import {ref} from "vue";

const layouts = {
  player: markRaw(MessageMe),
  operator: markRaw(MessageOther)
}

const message = ref('')
const props = defineProps({
  history: {
    type: Array,
    required: true
  },
  typing: {
    type: Boolean,
    default: false,
    required: false
  },
  endSession: {
    type: Boolean,
    default: false,
    required: false
  }
})
const emit = defineEmits(['onMessage'])

function onMessage(event) {
  if (!event.shiftKey) {
    event.preventDefault();
  } else {
    return;
  }
  if (!message.value.length) {
    return;
  }


  emit('onMessage', message.value)
  message.value = '';
}

function scrollEnd(){
  $(".chat_area").stop().animate({scrollTop: $(".chat_area")[0].scrollHeight}, 500);
}

onMounted(async () => {
  await nextTick();
  scrollEnd()
})

defineExpose({
  scrollEnd
})

// export default {
//     name: "ChatBox",
//     props: ['history', 'user', 'operator_id', 'end_session', 'typing'],
//     data() {
//         return {
//             timeout: null,
//             message: '',
//         }
//     },
//     computed: {
//         history_watch() {
//             return this.history.length
//         }
//     },
//     watch: {
//         history_watch: function (newVal, oldVal) {
//             console.log(newVal, oldVal)
//             if (newVal !== oldVal) {
//                 this.scrollToButton()
//             }
//         }
//     },
//     methods: {
//         scrollToButton() {
//             $(".chat_area").stop().animate({scrollTop: $(".chat_area")[0].scrollHeight}, 500);
//         },
//         onTyping(event) {
//             if (event.keyCode !== 13) {
//                 if (!this.timeout) {
//                     this.$io.emit('typing', {guest_id: this.$io.userID, typing: true});
//                 }
//                 clearTimeout(this.timeout);
//                 this.timeout = setTimeout(() => {
//                     this.$io.emit("typing", {guest_id: this.$io.userID, typing: false});
//                     this.timeout = null
//                 }, 2000);
//             }
//         },
//         onMessage(event) {
//             if (!event.shiftKey) {
//                 event.preventDefault();
//             } else {
//                 return;
//             }
//             if (!this.message.length) {
//                 return;
//             }
//
//             const content = {
//                 message: this.message,
//                 username: this.user,
//                 created_at: new Date(),
//                 sender: "user",
//                 receiver: "operator",
//                 operator_id: this.operator_id,
//             }
//
//
//             this.$io.emit("private message", {
//                 content,
//                 to: this.$io.auth.roomKey,
//                 from: 'user',
//                 room_id: this.$io.auth.roomID,
//                 hasNewMessages: true
//             });
//
//             this.history.push(content)
//
//             clearTimeout(this.timeout);
//             this.$io.emit('typing', {guest_id: this.$io.userID, typing: false})
//             this.timeout = null;
//
//             this.message = '';
//         }
//     },
//     mounted() {
//         $(".chat_area").stop().animate({scrollTop: $(".chat_area")[0].scrollHeight}, 0);
//     },
//     components: {
//         MessageOther,
//         MessageMe
//     }
// }
</script>

<style scoped>
#content-area {
  padding: 0px 20px;
}

.message_section {
  border: 1px solid #dddddd;
}

.message_write {
  position: relative;
  float: left;
  padding: 15px;
  width: 100%;
  background: linear-gradient(
      99.8deg, rgba(196, 47, 237, 0.5) 14.73%, rgba(99, 158, 255, 0.5) 88.93%);
  backdrop-filter: blur(10px);
}

.btn-message_write {
  position: absolute;
  top: 50%;
  right: 0.9rem;
  display: flex;
  justify-content: center;
  align-items: center;
  /* border-radius: 50%; */
  transform: translateY(-53%);
  border-radius: 0 5px 5px 0;
  height: 70px;
}

@supports (-moz-appearance:none) {
  .btn-message_write {
    transform: translateY(-50%);
  }
}

.message_write textarea.form-control {
  height: 70px;
  padding: 10px;
}

.chat-input-box {
  border: 1px solid #CCC;
  padding: 5px 8px;
  background: #FFF;
  border-radius: 4px;
  height: 70px;
  overflow-x: hidden;
  width: 100%;
}

.chat-input-box > .s-emboji {
  width: 20px;
  margin: 0px 2px;
}

.chat_area {
  float: left;
  height: 280px;
  overflow-x: hidden;
  overflow-y: auto;
  width: 100%;
  background-color: #151131;
}

/* для Chrome/Edge/Safari */
.chat_area::-webkit-scrollbar {
  height: 12px;
  width: 12px;
}

.chat_area::-webkit-scrollbar-track {
  background: transparent;
}

.chat_area::-webkit-scrollbar-thumb {
  background-color: rgba(79, 70, 229, var(--tw-bg-opacity));
  border-radius: 5px;
  border: 3px solid #151131;
}

.chat_area li {
  padding: 14px 0 14px 14px;
}

.chat_area li .chat-img1 img {
  height: 40px;
  width: 40px;
}

.chat_area .chat-body1 {
  margin-left: 50px;
}

.chat-body1 p {
  background: #fbf9fa none repeat scroll 0 0;
  padding: 10px;
}

.chat_area .admin_chat .chat-body1 {
  margin-left: 0;
  margin-right: 50px;
}

.chat_area li:last-child {
  padding-bottom: 10px;
}

/** Chat Window **/
#custom-search-input {
  background: #e8e6e7 none repeat scroll 0 0;
  margin: 0;
  padding: 10px;
}

#custom-search-input .search-query {
  background: #fff none repeat scroll 0 0 !important;
  border-radius: 4px;
  height: 33px;
  margin-bottom: 0;
  padding-left: 7px;
  padding-right: 7px;
}

#custom-search-input button {
  background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
  border: 0 none;
  border-radius: 3px;
  color: #666666;
  left: auto;
  margin-bottom: 0;
  margin-top: 7px;
  padding: 2px 5px;
  position: absolute;
  right: 0;
  z-index: 9999;
}

.search-query:focus + button {
  z-index: 3;
}

.all_conversation button {
  background: #f5f3f3 none repeat scroll 0 0;
  border: 1px solid #dddddd;
  height: 38px;
  text-align: left;
  width: 100%;
}

.all_conversation i {
  background: #e9e7e8 none repeat scroll 0 0;
  border-radius: 100px;
  color: #636363;
  font-size: 17px;
  height: 30px;
  line-height: 30px;
  text-align: center;
  width: 30px;
}

.all_conversation .caret {
  bottom: 0;
  margin: auto;
  position: absolute;
  right: 15px;
  top: 0;
}

.all_conversation .dropdown-menu {
  background: #f5f3f3 none repeat scroll 0 0;
  border-radius: 0;
  margin-top: 0;
  padding: 0;
  width: 100%;
}

.all_conversation ul li {
  border-bottom: 1px solid #dddddd;
  line-height: normal;
  width: 100%;
}

.all_conversation ul li a:hover {
  background: #dddddd none repeat scroll 0 0;
  color: #333;
}

.all_conversation ul li a {
  color: #333;
  line-height: 30px;
  padding: 3px 20px;
}

.member_list .chat-body {
  margin-left: 47px;
  margin-top: 0;
}

.top_nav {
  overflow: visible;
}

.member_list .contact_sec {
  margin-top: 3px;
}

.member_list li {
  padding: 6px;
}

.member_list ul {
  border: 1px solid #dddddd;
}

.chat-img img {
  height: 34px;
  width: 34px;
}

.member_list li {
  border-bottom: 1px solid #dddddd;
  padding: 6px;
}

.member_list li:last-child {
  border-bottom: none;
}

.member_list {
  height: 380px;
  overflow-x: hidden;
  overflow-y: auto;
}

.sub_menu_ {
  background: #e8e6e7 none repeat scroll 0 0;
  left: 100%;
  max-width: 233px;
  position: absolute;
  width: 100%;
}

.sub_menu_ {
  background: #f5f3f3 none repeat scroll 0 0;
  border: 1px solid rgba(0, 0, 0, 0.15);
  display: none;
  left: 100%;
  margin-left: 0;
  max-width: 233px;
  position: absolute;
  top: 0;
  width: 100%;
}

.all_conversation ul li:hover .sub_menu_ {
  display: block;
}

.new_message_head button {
  background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
  border: medium none;
}

.new_message_head {
  background: #f5f3f3 none repeat scroll 0 0;
  float: left;
  font-size: 13px;
  font-weight: 600;
  padding: 18px 10px;
  width: 100%;
}


.chat_bottom {
  float: left;
  margin-top: 13px;
  width: 100%;
}

.sub_menu_ > li a, .sub_menu_ > li {
  float: left;
  width: 100%;
}

.member_list li:hover {
  background: #428bca none repeat scroll 0 0;
  color: #fff;
  cursor: pointer;
}

.chat-input-box {
  border: 1px solid #CCC;
  padding: 5px 8px;
  background: #FFF;
  border-radius: 4px;
  height: 70px;
  overflow-x: hidden;
  width: 100%;
}

.chat-input-box > .s-emboji {
  width: 20px;
  margin: 0px 2px;
}

.emboji-container {
  display: none;
  width: 300px;
  background: #FFF;
  border: 1px solid #CCC;
  border-radius: 4px;
  overflow: hidden;
  padding: 5px;
  position: absolute;
  right: 41px;
  margin-top: -84px;
  z-index: 99;
}

.emboji {
  padding: 5px 0px;
  text-align: center;
}

.emboji:hover {
  background: #CCC;
}

.emboji > img {
  width: 30px;
}

/**Chat box Style **/
.chat-history {
  list-style: none;
  margin: 0;
  padding: 0;
}

.chat .chat-header {
  padding: 0px 10px 12px 0px;
  border-bottom: 2px solid #EEE;
}

.chat .chat-header img {
  float: left;
  width: 50px;
  border: 3px solid #86BB71;
  border-radius: 50%;
}

.chat .chat-header .chat-about {
  float: left;
  padding-left: 10px;
  margin-top: 6px;
  cursor: pointer;
}

.chat .chat-header .chat-with {
  font-weight: bold;
  font-size: 16px;
}

.chat .chat-header .chat-num-messages {
  color: #92959E;
}

.chat .chat-header .fa-star {
  float: right;
  color: #D8DADF;
  font-size: 20px;
  margin-top: 12px;
}

.chat .chat-history .message-data {
  margin-bottom: 8px;
}

.chat .chat-history .message-data-time {
  color: #a8aab1;
  padding-left: 6px;
}

.chat .chat-history .message-data-name {
  color: white;
}

.chat .chat-history .message {
  color: white;
  padding: 6px 12px;
  line-height: 26px;
  font-size: 16px;
  border-radius: 7px;
  margin-bottom: 15px;
  position: relative;
  display: inline-block;
  max-width: 100%;
}

.chat .chat-history .message:after {
  bottom: 100%;
  left: 10px;
  border: solid transparent;
  content: " ";
  height: 0;
  width: 0;
  position: absolute;
  pointer-events: none;
  border-bottom-color: #007bff;
  border-width: 6px;
}

.chat .chat-history .message a {
  color: #FFF;
}

.chat .chat-history .message a:hover {
  text-decoration: underline;
}

.chat .chat-history .message .s-emboji {
  width: 18px;
  margin: 0px 2px;
}

.chat .chat-history .my-message {
  background: #007bff;
}

.chat .chat-history .other-message {
  background: #ff7979;
}

.chat .chat-history .other-message:after {
  border-bottom-color: #ff7979;
  position: absolute;
  left: auto;
  right: 10px;
}

.chat .chat-message {
  padding: 30px;
}

.online, .offline, .me {
  margin-right: 3px;
  font-size: 10px;
}

.online {
  color: #007bff;
}

.offline {
  color: #E38968;
}

.me {
  color: #ff7979;
}

.align-left {
  text-align: left;
}

.align-right {
  text-align: right;
}

.float-right {
  float: right;
}

.clearfix:after {
  visibility: hidden;
  display: block;
  font-size: 0;
  content: " ";
  clear: both;
  height: 0;
}

.v-error {
  color: red;
}

.typing-status {
  position: relative;
  top: -20px;
  margin-right: 14px;
  text-align: right;
  color: #ff7979;
  font-size: 16px;
}

.typing-status b {
  font-family: monospace;
  overflow: hidden;
  border-right: .15em solid orange;
  white-space: nowrap;
  margin: 0 auto;
  letter-spacing: 0.07em;
  animation: typing 3.5s steps(30, end),
  blinking-cursor .5s step-end infinite;
}


.chat_history_area {
  float: left;
  height: 500px;
  overflow-x: hidden;
  overflow-y: auto;
  width: 100%;
}

.chat_history_area li {
  padding: 14px 14px 0;
}

</style>
