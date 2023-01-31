<template>
    <li class="clearfix">
        <div class="message-data align-right">
            <span class="message-data-time">{{ formatDate(content.created_at) }}</span> &nbsp; &nbsp;<span
            class="message-data-name">{{ content.username }}</span> <i class="fa fa-circle me"></i>
        </div>
        <div class="message other-message float-right">{{ content.message }}</div>
    </li>
</template>

<script>
import formatDateMixin from "../../../../mixins/formatDateMixin";

export default {
    name: "MessageOther",
    props: ['content'],
    mixins: [formatDateMixin],
}
</script>

<style scoped>

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
