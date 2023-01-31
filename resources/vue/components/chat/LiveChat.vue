<template>
    <button-chat @onOpen="onToggle" :open="open" :new_message="new_message"></button-chat>
    <div v-if="open" class="live-chat">
        <!--Widget Icon-->
        <!--Start Widget-->
        <div class="panel panel-default tricky_chat_widget modern-widget">
            <head-chat @onToggle="onToggle"></head-chat>
            <body-chat :connection="connection" :isUsername="username">
                <template #chat-box>
                    <ChatBox :end_session="end_session" :history="chat_history" :user="username"
                             :operator_id="operator_id" :typing="typing"></ChatBox>
                </template>
                <template #login-area>
                    <login-area @onLogin="onLogin" :loggedIn="loggedIn" :user="user"></login-area>
                </template>
            </body-chat>
        </div>
        <!--End Widget-->
    </div>
    <audio></audio>
</template>

<script>

import ButtonChat from "./app/ButtonChat";
import HeadChat from "./app/HeadChat";
import BodyChat from "./app/BodyChat";
import ChatBox from "./app/ChatBox";
import LoginArea from "./app/LoginArea";

export default {
    name: "LiveChat",
    props: ['loggedIn'],
    components: {LoginArea, ButtonChat, HeadChat, BodyChat, ChatBox},
    created() {
        const roomKey = localStorage.getItem("roomKey");
        if (roomKey) {
            this.connection = true;
            this.$io.auth = {roomKey};
            this.$io.connect();
        }

        this.$io.on("session", ({roomKey, roomID, userID, username, messages, operator_id}) => {
            this.$io.auth = {roomID, roomKey};
            localStorage.setItem("roomKey", roomKey);
            this.$io.userID = userID;
            this.$io.roomID = roomID;

            this.new_username = username;
            this.chat_history.push(...messages);
            this.operator_id = operator_id;
        });


        this.$io.on("connect_error", (err) => {
            if (err.message === "invalid username") {
                this.connection = false;
                this.open = false;
            }

            if (err.message === "invalid Session") {
                this.connection = false;
                this.open = false;
                localStorage.removeItem("roomID");
            }
        });

        this.$io.on('operator join room', ({operator_id, message}) => {
            this.operator_id = operator_id;
            this.$io.emit('operator join', {new_message: 0, operator_id: operator_id})
            this.chat_history.push(message)
        })

        this.$io.on('operator leave', ({message}) => {
            this.chat_history.push(message);
            this.$io.off("connect_error");
            this.$io.off("operator leave");
            this.$io.off("operator join");
            this.$io.off("session");
            this.$io.off("session end");
            this.$io.off("private message");
            this.$io.disconnect();
            this.end_session = true
        })

        this.$io.on('session end', () => {
            console.log('session end')
            this.$io.disconnect();
            this.connection = false
        })

        this.$io.on("private message", async ({content, from, to}) => {
            console.log({content, from, to})
            if (from === 'operator') {
                console.log({content, from, to})
                this.chat_history.push(content)
            }

            if (!this.open) {
                this.new_message++;
            }

            await this.onSound('default');
        });

        this.$io.on('typing admin', (typing) => {
            this.typing = typing
        })

    },
    data() {
        return {
            message_write: '',
            chat_history: [],
            open: false,
            typing: false,
            connection: false,
            end_session: false,
            new_username: '',
            operator_id: 0,
            new_message: 0,
        }
    },
    computed: {
        user() {
            return this.$store.state.user.user
        },
        username() {
            return this.new_username
        },
    },
    methods: {
        onToggle() {
            this.open = !this.open
            this.new_message = 0;
        },
        onLogin(user, email) {
            this.connection = true;
            let auth = {username: user, email: email, hash: ''};
            if (this.loggedIn) {
                auth.email = this.user.email
                auth.suspicious = this.user.suspicious
            }

            this.$io.auth = auth;
            this.$io.connect()
            this.new_username = user;
        },
        async onSound(sound) {
            let audio = new Audio();
            audio.src = `/public/sounds/${sound}.mp3`;
            await audio.play();
        },
    },
    onUnmounted() {
        this.$io.off("connect_error");
        this.$io.off("operator leave");
        this.$io.off("operator join");
        this.$io.off("session");
        this.$io.off("session end");
    },
}
</script>

<style scoped>

.live-chat {
    position: fixed;
    bottom: 0;
    right: 0.5rem;
    max-width: 360px;
    width: 100%;
    z-index: 999;
    padding: 1px 1px 0 1px;
    background-clip: padding-box;
}

.live-chat::after {
    position: absolute;
    top: -1px;
    bottom: -1px;
    left: -1px;
    right: -1px;
    background: linear-gradient(
        99.8deg, #C42FED, #639EFF, #639EFF, #C42FED);
    background-size: 300%;
    content: '';
    z-index: -1;
    border-radius: 4px;
}


#main-wrapper {
    width: 100%;
    margin: 0;
    padding: 0;
    height: auto;
}

.select_store {
    color: #222;
    width: 150px;
    float: right;
    padding: 0px 10px;
    border: none;
}

.bootstrap-select > .btn-default {
    border: 1px solid #CCC;
    padding: 7px 11px;
}

.clear {
    clear: both;
}

.no-export .btn-group {
    display: none;
}

.setting-logo {
    border: 1px solid #ccc;
    max-width: 100%;
    margin-bottom: 10px;
}

.mt-30 {
    margin-top: 30px;
}

.adjust-row-margin {
    margin: 0 !important;
}

.panel-heading > a.pull-right {
    margin-top: -1px;
}

#clickme {
    width: 100%;
    background: #fff;
    border: 1px solid #ebebeb;
}

.material-icons {
    font-family: 'Material Icons';
    font-weight: normal;
    font-style: normal;
    font-size: 24px;
    /* Preferred icon size */
    display: inline-block;
    line-height: 1;
    text-transform: none;
    letter-spacing: normal;
    word-wrap: normal;
    white-space: nowrap;
    direction: ltr;
    /* Support for all WebKit browsers. */
    -webkit-font-smoothing: antialiased;
    /* Support for Safari and Chrome. */
    text-rendering: optimizeLegibility;
    /* Support for Firefox. */
    -moz-osx-font-smoothing: grayscale;
    /* Support for IE. */
    font-feature-settings: 'liga';
}

.panel-default > .panel-heading,
.panel:not([class*="panel-"]) > .panel-heading {
    color: #fff;
    font-family: 'Poppins', sans-serif;
    padding: 20px 15px;
    font-size: 18px;
    background: linear-gradient(99.8deg, rgba(196, 47, 237, 0.5) 14.73%, rgba(99, 158, 255, 0.5) 88.93%);
    backdrop-filter: blur(10px);
}


/* Header Style */

.admin-head {
    width: 100%;
    height: 62px;
    background: #201f2b;
    border-bottom: 2px solid #db1430;
}

.left-header {
    width: 50%;
}

.right-header {
    width: 50%;
    float: right;
    margin-right: 30px;
}

.temp-logo {
    margin-top: 25px;
    margin-left: 15px;
    float: left;
}

.mini-nav {
    float: left;
    margin-top: 21px;
    margin-left: 23px;
    color: #fff;
}

.mini-nav i {
    font-size: 30px;
    cursor: pointer;
    background: #34495e;
    padding: 3px 5px;
}

.custom-navbar {
    background-color: #201f2b !important;
    height: 40px;
}

.custom-navbar ul li i {
    position: absolute;
    margin-left: -28px;
}

.top-right-menu {
    color: #CCC !important;
    font-weight: 400;
}


/** Toogle Button Style **/

.mini-nav .togglebutton label input[type=checkbox]:checked + .toggle:after,
.togglebutton-default label input[type=checkbox]:checked + .toggle:after {
    background-color: #db1430;
}

.mini-nav .togglebutton label input[type=checkbox]:checked + .toggle,
.togglebutton-default label input[type=checkbox]:checked + .toggle {
    background-color: aliceblue;
}

.mini-nav .togglebutton label .toggle:after {
    content: "";
    display: inline-block;
    width: 25px;
    height: 25px;
    background-color: #F1F1F1;
    border-radius: 20px;
    position: relative;
    box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
    left: -5px;
    top: -5px;
    margin-left: 5px;
    transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
}

.mini-nav .togglebutton label .toggle,
.togglebutton label input[type=checkbox][disabled] + .toggle {
    content: "";
    display: inline-block;
    width: 45px;
    height: 15px;
    background-color: rgba(80, 80, 80, 0.7);
    border-radius: 15px;
    margin-right: 10px;
    transition: background 0.3s ease;
    vertical-align: middle;
}

#main_modal {
    padding-right: 0px !important;
}

.modal-header .modal-btn {
    padding: 6px 10px;
    border-radius: 0px;
    border: 1px solid;
    margin-left: 5px;
    margin-top: -9px;
}

.fullscreen-modal {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
}

.fullscreen-modal > .modal-content {
    height: auto;
    min-height: 100%;
    border-radius: 0;
}

.fullscreen-modal > .modal-content .modal-body {
    padding-top: 10px;
    overflow-y: visible !important;
    max-height: 100% !important;
}

.modal-body {
    width: 100%;
}

.form-horizontal .control-label, .form-horizontal label {
    padding-top: 7px;
    margin-bottom: 0;
    text-align: left;
    font-weight: 500;
}

/* Preloader */
#preloader {
    display: none;
    background: rgba(39, 60, 117, .5);
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 1100 !important;
}

#preloader > .bar {
    display: inline-block;
    padding: 0px;
    text-align: left;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    width: 150px;
    height: 20px;
    border: 1px solid #2980b9;
    border-radius: 3px;
    background-image: repeating-linear-gradient(
        -90deg,
        #34495e,
        #34495e 11px,
        #eee 10px,
        #eee 20px /* determines size */
    );
    background-size: 28px 28px;
    animation: move .8s linear infinite;
}

@keyframes move {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 28px 0;
    }
}


/** Form File Control **/

.form-group input[type=file] {
    opacity: 0;
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100;
}

input[type=file] {
    display: block;
}

.form-control {
    border: 1px solid #CCC;
    border-radius: 4px;
    padding: 10px;
    background-image: none;
    -webkit-transition: all 0.30s ease-in-out;
    -moz-transition: all 0.30s ease-in-out;
    -ms-transition: all 0.30s ease-in-out;
    -o-transition: all 0.30s ease-in-out;
}

.form-control:focus,
.form-control.focus {
    padding: 10px;
    border-radius: 4px;
    background-image: none !important;
    background-size: 100% 2px, 100% 1px;
    box-shadow: 0 0 5px rgba(81, 203, 238, 1);
    border: 1px solid rgba(81, 203, 238, 1);
}

.form-control:disabled {
    border: 1px solid #CCC;
    background: #DDD;
}

select.input-sm {
    height: 36px;
    line-height: 30px;
}

select.form-control {
    border: 1px solid #DDD;
    box-shadow: none;
    border-radius: 4px;
    height: 34px;
    line-height: 30px;
    padding: 0px 10px;
}

select.form-control:focus {
    border: 1px solid #DDD;
    padding: 0px 10px;
}

.thumb-image-sm {
    width: 80px;
}

.thumb-image-md {
    width: 150px;
}

.thumb-image-lg {
    width: 200px;
}

.params-panel {
    border: 1px solid #CCC;
    overflow: hidden;
    padding: 15px;
    border-radius: 4px;
}

/* Content Area Style */

.content-area {
    width: 100%;
    min-height: 100%;
    position: absolute;
}

.page-title {
    width: 100%;
    height: 60px;
    background: #fff;
    color: #263238;
    margin-bottom: 20px;
}

.page-title h2 {
    margin: 0;
    padding-top: 20px;
    padding-left: 20px;
    font-size: 22px;
    font-weight: 400;
}

.sidebar {
    width: 250px;
    min-height: 100%;
    padding-bottom: 60px;
    position: absolute;
    background: #282733;
    z-index: 10;
}

.content {
    width: 100%;
    position: relative;
    padding-left: 250px;
    min-height: 800px;
}

.online_user_list {
    list-style: none;
    list-style: none;
    padding: 0;
}

.online_user_list li {
    font-size: 16px;
    border-bottom: 1px solid #d4c3c3;
    padding: 5px;
}

.online_user_list li.active_visitor:before, .online_user_list li.active_operator:before {
    content: "\f05d";
    font-family: FontAwesome;
    margin-right: 6px;
    color: #2ecc71;
}

.online_user_list li.guest_visitor a {
    color: #36a9e1;
    font-weight: normal;
}

.online_user_list li.guest_visitor:before {
    content: "\f059";
    font-family: FontAwesome;
    margin-right: 6px;
    color: #36a9e1;
}

.online_user_list li.transfer_visitor a {
    color: #E91E63;
    font-weight: normal;
}

.online_user_list li.transfer_visitor:before {
    content: "\f0ec ";
    font-family: FontAwesome;
    margin-right: 6px;
    color: #E91E63;
}

.online_user_list li a {
    color: #2ecc71;
    font-weight: bold;
    transition: all 1s;
}

.online_user_list li:hover {
    background: #EEE;
}

/* cart box style */

.widget-label {
    font-size: 16px;
    color: #fff;
    text-transform: uppercase;
    text-align: center;
    padding-top: 10px;
    font-weight: 200;
}

.admin-widget-label {
    font-size: 16px;
    color: #fff;
    text-transform: uppercase;
    text-align: center;
    padding-top: 10px;
    font-weight: 300;
    font-family: 'Poppins', sans-serif;
}

.admin-widget-content {
    font-size: 30px;
    color: #fff;
    text-transform: uppercase;
    text-align: center;
    padding-top: 10px;
    font-weight: 400;
    font-family: 'Poppins', sans-serif;
}

.middle-content {
    padding-top: 30px;
}

.middle-content > i {
    color: #fff;
    font-size: 40px;
}

.cart-box-blue {
    height: 170px;
    background: #03a9f4;
    margin-top: 10px;
    text-align: center;
}

.cart-box-sky {
    height: 170px;
    background: #00bcd4;
    margin-top: 10px;
    text-align: center;
}

.cart-box-purpel {
    height: 170px;
    background: #3f51b5;
    margin-top: 10px;
    text-align: center;
}

.cart-box-red {
    height: 170px;
    background: #e91e63;
    margin-top: 10px;
    text-align: center;
}

#income-preview {
    color: #fff;
}

#expense-preview {
    color: #fff;
}

.cart-text {
    color: #fff;
}

.admin-panel {
    margin-top: 20px;
}

.panel-border-bottom {
    border-bottom: 1px solid rgba(0, 0, 0, .12);
}


.clear {
    clear: both;
}

.admin-footer {
    height: 60px;
    background: #fff;
    width: 100%;
    float: left;
    color: #263238;
}

.admin-footer p {
    float: right;
    margin: 0 0 10px;
    padding-top: 20px;
    font-weight: bold;
    padding-right: 15px;
    font-size: 14px;
}


/*sidebar Style */

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.tabular {
    display: table;
    width: 100%;
}

.tabular .tabular-row {
    display: table-row;
}

.tabular .tabular-row .tabular-cell {
    display: table-cell;
    vertical-align: middle;
}

.sidebar .welcome-box {
    transition: 0.5s all ease;
    padding: 24px;
    margin: 0 !important;
}

.sidebar .welcome-box .welcome-avatar {
    width: 56px;
}

.sidebar .welcome-box .welcome-avatar img,
.sidebar .welcome-box .welcome-avatar a {
    display: block;
}

.sidebar .welcome-box .welcome-options {
    margin-left: 48px;
    padding-top: 3px;
}

.sidebar .welcome-box .welcome-options .name,
.sidebar .welcome-box .welcome-options .welcome-text {
    display: block;
}

.sidebar .welcome-box .welcome-options .name:hover,
.sidebar .welcome-box .welcome-options .welcome-text:hover {
    text-decoration: none;
}

.sidebar .welcome-box .welcome-options .name {
    font-weight: 400;
    font-size: 14px;
    color: #fff;
}

.sidebar .welcome-box .welcome-options .welcome-text {
    font-size: 11px;
    color: #aaa;
}

.sidebar .welcome-box .welcome-edit {
    width: 20px;
    text-align: right;
    vertical-align: top !important;
}

.sidebar .welcome-box .welcome-edit .btn-link {
    padding: 0;
    border: none;
    vertical-align: top;
    min-width: 0;
    color: rgba(255, 255, 255, 0.3) !important;
}

.sidebar .welcome-box .welcome-edit .btn-link:hover {
    color: #fff !important;
    text-decoration: none;
}

.border-bottom {
    border-bottom: 1px solid #ecf0f1;
    margin-left: 15px;
    margin-right: 15px;
}

.sidebar.navigation-divider {
    margin: 10px 0;
    height: 1px;
    background-color: rgba(255, 255, 255, .1);
}


/* Color Control Box CSS */

#control-box {
    position: fixed;
    right: 0;
    width: 250px;
    padding: 20px;
    background: #e4e9f0;
    z-index: 999;
    top: 50%;
    margin-top: -150px;
}

.close-box {
    background: #444;
    position: absolute;
    right: 250px;
    top: 0;
    cursor: pointer;
}

#control-box i {
    color: #fff;
    font-size: 20px;
    height: 40px;
    padding-top: 9px;
    text-align: center;
    width: 40px;
}

#control-box i .box-close {
    display: none
}

#control-box i .box-open {
}

.color-box {
    margin-left: -10px
}

.color-box span {
    display: block;
    float: left;
    height: 30px;
    margin-bottom: 10px;
    margin-left: 10px;
    width: 62px;
    cursor: pointer;
}

.default-color {
    background: #263238 !important
}

.green-color {
    background: #16a085 !important
}

.blue-color {
    background: #2980b9 !important
}

.darkblue-color {
    background: #34495e !important
}

.lightgreen-color {
    background: #41C484 !important
}

.red-color {
    background: #c0392b !important
}

.carrot-color {
    background: #e67e22 !important
}

.orange-color {
    background: #f39c12 !important
}

.darkpurpel-color {
    background: #e91e63 !important
}

.default-font-color {
    color: #263238 !important
}

.green-font-color {
    color: #16a085 !important
}

.blue-font-color {
    color: #2980b9 !important
}

.darkblue-font-color {
    color: #34495e !important
}

.lightgreen-font-color {
    color: #41C484 !important
}

.red-font-color {
    color: #c0392b !important
}

.carrot-font-color {
    color: #e67e22 !important
}

.orange-font-color {
    color: #f39c12 !important
}

.darkpurpel-font-color {
    color: #e91e63 !important
}

.remove-item {
    cursor: pointer;
    font-size: 20px;
    color: #c0392b;
}

#control-box h2 {
    color: #5c5c5c;
    font-size: 16px;
    margin-bottom: 25px;
}


/* Datatable */

#DataTables_Table_0_filter {
    margin-top: -35px;
}

#DataTables_Table_0_filter input {
    font-size: 14px;
}

table .btn {
    margin: 0;
}

.note-btn {
    padding: 5px 14px !important;
}

thead {
    background: #ffffff; /* Old browsers */
    background: -moz-linear-gradient(top, #ffffff 0%, #e5e5e5 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, #ffffff 0%, #e5e5e5 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, #ffffff 0%, #e5e5e5 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#e5e5e5', GradientType=0); /* IE6-9 */
}

/* Cart Box*/

.cart-icon {
    display: block;
    color: #fff;
    font-size: 70px;
    line-height: 103px;
}


.smtp-section {
    display: none;
}

.sugesstion {
    z-index: 999;
    display: none;
    min-width: 100px;
    min-height: 31px;
    background: rgba(0, 0, 0, .6);
    position: absolute;
}

.sugesstion a,
.sugesstion span {
    display: block;
    color: #fff;
    padding: 5px;
    border-bottom: 1px solid #CCC;
}

.sugesstion a:hover {
    background: rgba(0, 0, 0, .7);
}

.item-active {
    background: rgba(0, 0, 0, .7);
}


#general-model {
    height: 545px;
    overflow: auto;
    border-bottom: 2px solid #CCC
}

.modal-content .modal-header {
    border-bottom: none;
    padding-top: 20px;
    padding-right: 24px;
    padding-bottom: 15px;
    padding-left: 24px;
    border-bottom: 1px solid rgba(0, 0, 0, .12);
}

.modal-title {
    font-size: 16px;
}


.modal-content .modal-body {
    padding-top: 10px;
    overflow-y: scroll !important;
    overflow-x: hidden;
    max-height: 500px;
}


#invoice {
    border-bottom: 1px solid #ccc;
}

@media only screen and (max-width: 600px) {
    .invoice-box table tr.top table td {
        width: 100%;
        display: block;
        text-align: center;
    }

    .invoice-box table tr.information table td {
        width: 100%;
        display: block;
        text-align: center;
    }
}

.report-params {
    border: 2px solid #DDD;
    padding-top: 15px;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.input-group .input-group-addon {
    border: 0;
    background: #ddd;
}

.pace {
    -webkit-pointer-events: none;
    pointer-events: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
}

.pace-inactive {
    display: none;
}

.pace .pace-progress {
    background: #e74c3c;
    position: fixed;
    z-index: 2000;
    top: 0;
    right: 100%;
    width: 100%;
    height: 4px;
}


/* activity Menu*/

.submit-activity .activity-header .activity-toolbar li {
    display: inline-block;
}

.submit-activity {
    border: 1px solid #d1d1d1;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    background-color: #fff;
}

.submit-activity textarea {
    background-color: transparent;
    display: block;
    width: 100%;
    padding: 10px 10px 5px;
    border: 0;
    resize: none;
    -webkit-border-radius: 3px;
    border-radius: 3px;
}

.submit-activity .activity-header .activity-toolbar {
    list-style: none;
    margin: 0;
    padding: 0 3px;
    float: left;
}

.submit-activity .activity-header .activity-toolbar li a {
    display: block;
    text-align: center;
    font-size: 16px;
    line-height: 30px;
    width: 30px;
    color: #95a5a6;
}

.submit-activity .activity-header .activity-toolbar li a:hover {
    background-color: #e8eaed;
}

.action-active {
    background-color: #e8eaed;
}

#activity-area {
    margin-top: 50px;
    height: 285px;
    overflow: hidden;
}

.cal {
    position: absolute;
    top: 23px;
    color: #f1c40f;
    font-size: 22px;
    margin-left: 10px;
}

.cal:hover {
    color: #e67e22;
}

.cal:focus {
    color: #e67e22;
}

.fake-input {
    border: 1px solid #CCC;
    width: 100%;
    padding: 6px 10px;
    font-weight: bold;
    margin-bottom: 30px;
}

/* Jquery Validation Style*/

.vertical-form label.error {
    position: relative !important;
    display: inline-block;
    margin-top: 4px !important;
    margin-bottom: -8px !important;
    color: #fff !important;
    font-weight: normal !important;
    font-size: 14px !important;
    padding: 5px 8px !important;
    background: #e74c3c !important;
    border-radius: 4px !important;
    border-bottom: 0px !important;
}

.vertical-form label.error.valid {
    display: none !important;
}

.vertical-form label.error:after {
    bottom: 100%;
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
    border-bottom-color: #e74c3c;
    border-width: 5px;
    left: 30px;
    margin-left: -5px;
}

.error {
    border: 1px solid red !important;
    background-position: center bottom, center calc(100% - 0px) !important;
}

#system-name {
    display: inline-block;
    color: #FFF;
    font-size: 18px;
    float: left;
    margin-top: 17px;
    width: 250px;
    text-align: center;
    font-weight: 400;
}

.member-container {
    border: 1px solid #CCC;
    overflow: hidden;
    padding: 10px 0px;
}


.stepwizard-step p {
    margin-top: 10px;
}

.stepwizard-row {
    display: table-row;
}

.stepwizard {
    display: table;
    width: 100%;
    position: relative;
}

.stepwizard-step button[disabled] {
    opacity: 1 !important;
    filter: alpha(opacity=100) !important;
}

.stepwizard-row:before {
    top: 25px;
    bottom: 0;
    position: absolute;
    content: " ";
    width: 100%;
    height: 1px;
    background-color: #ccc;
    z-order: 0;

}

.stepwizard-step {
    display: table-cell;
    text-align: center;
    position: relative;
    width: 33.33%;
}

.btn-circle {
    width: 30px;
    height: 30px;
    text-align: center;
    padding: 6px 0;
    font-size: 12px;
    line-height: 1.428571429;
    border-radius: 15px;
}

.stepwizard-step .btn-default {
    border: 1px solid #CCC;
    background-color: #CCC !important;
}

#clickme {
    height: 38px;
    background: #e74c3c;
    color: #FFF;
    max-width: 191px;
}

.setting-logo {
    max-width: 191px;
}

/* responsive Design */

@media (max-width: 768px) {
    .client-account-sammary {
        width: 100%;
    }

    .right-header {
        display: none;
    }

    .left-header {
        width: 100%;
    }

    .mini-nav {
        display: none;
        float: right;
        margin-right: 15px;
    }

    #DataTables_Table_0_filter {
        margin-top: 0px;
    }

    /* Sidebar */
    .menu-title {
        display: none !important;
    }

    #menu span {
        display: none !important;
    }

    .sidebar {
        width: 60px !important;
    }

    .welcome-options {
        display: none !important;
    }

    .welcome-box {
        padding: 10px !important;
    }

    #menu {
        padding-left: 7px !important;
    }

    #menu li ul {
        display: none !important;
    }

    .content {
        padding-left: 60px !important;
    }
}

@media (min-width: 400px) and (max-width: 600px) {
    .invoice-control .btn {
        width: 100%;
        margin-top: 3px;
    }
}


/* Jquery Validation Style*/

.vertical-form label.error {
    position: relative !important;
    display: inline-block;
    margin-top: 4px !important;
    margin-bottom: -8px !important;
    color: #fff !important;
    font-weight: normal !important;
    font-size: 14px !important;
    padding: 5px 8px !important;
    background: #e74c3c !important;
    border-radius: 4px !important;
    border-bottom: 0px !important;
}

.vertical-form label.error.valid {
    display: none !important;
}

.vertical-form label.error:after {
    bottom: 100%;
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
    border-bottom-color: #e74c3c;
    border-width: 5px;
    left: 30px;
    margin-left: -5px;
}

.error {
    border: 1px solid #e74c3c !important;
    background-position: center bottom, center calc(100% - 0px) !important;
}

select.error + span {
    border: 1px solid #e74c3c !important;
    border-radius: 4px;
}

checkbox.error + span {
    border: 1px solid #e74c3c !important;
    border-radius: 4px;
}

.ajax-submit label.error {
    display: none !important;
}

#receipt-table i {
    color: red;
    font-size: 16px;
}

.print-block {
    display: none;
}

.receipt-header {
    text-align: center;
    margin-top: 10px;
}

.receipt-header p, h1, h2, h3, h4, h5 {
    margin: 0;
    padding: 0;
}

.receipt-header p {
    font-size: 12px;
}

.receipt-header h5 {
    font-size: 14px;
}

.required {
    color: #e74c3c;
}

#sales_note {
    width: 100%;
    border: 1px solid #ddd;
}

.sales_note {
    border: 1px solid #ddd;
    width: 100%;
}

.sales_note h3 {
    margin: 15px 0px;
}

.table-seperator {
    width: 100%;
    border: 1px dashed #000;
}

.btn-primary:not(.btn-link):not(.btn-flat) {
    background-color: #007bff;
}

.btn-primary:not(.btn-link):not(.btn-flat):hover {
    background-color: #006cee;
}

.img-avatar {
    width: 60px;
}

.modal-content {
    border-radius: 0px !important;
}

table.table-bordered.dataTable td {
    vertical-align: middle;
}

.tab-content > .tab-pane {
    overflow: hidden;
}

.default-border {
    border: 1px solid #DDD;
    border-radius: 4px;
}

ul.nav-tabs > li.active {
    background: #007bff;
    margin-bottom: 0px;
}

@media print {
    #sales_receipt {
        border: none;
    }

    th {
        font-weight: bold;
    }

    .remove-row {
        display: none;
    }

    .no-print {
        display: none;
    }

    .print-block {
        display: block;
    }
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

/* The typing effect */
@keyframes typing {
    from {
        width: 0
    }
    to {
        width: 100%
    }
}

/* The typewriter cursor effect */
@keyframes blinking-cursor {
    from, to {
        border-color: transparent
    }
    50% {
        border-color: orange;
    }
}


#transfer-request {
    padding: 1px 5px;
    background: red;
    border-radius: 4px;
    margin-left: 5px;
    display: none;
}

/* Dropup Style */
.dropup {
    position: relative;
    display: inline-block;
}

.dropup-content {
    display: none;
    position: absolute;
    background-color: #FFF;
    border: 1px solid #CCC;
    min-width: 160px;
    bottom: 40px;
    z-index: 1;
}

.dropup-content a {
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    display: block;
}

.dropup-content a:hover {
    background-color: #DDD
}

.dropup:hover .dropbtn {
    background-color: #2980B9;
}

.message_count {
    background: #e74c3c;
    color: #fff;
    padding: 1px 4px;
    margin-left: 5px;
    border-radius: 4px;
}

.end_chat_session {
    margin-top: 10px;
}

.end_chat_session > i {
    font-size: 22px;
    color: #e74c3c;
}

#popover {
    background: #FFF;
    border: 1px solid #CCC;
    border-radius: 4px;
    padding: 15px;
    min-width: 300px;
    display: none;
    z-index: 999;
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

.end_session {
    background: red !important;
}

.end_session:after {
    border-bottom-color: red !important;
}

.unread {
    font-weight: bold;
}

.count-badge {
    padding: 1px 8px;
    background: #E91E63;
    border-radius: 4px;
    margin-left: 5px;
}
</style>
