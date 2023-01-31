import { io } from "socket.io-client";
const URL = "https://bitfiring.com:3000";
const socket = io(URL, { autoConnect: false });

export default {
    install: (app, option) => {
        app.config.globalProperties.$socket = socket
        app.provide('socket', socket)
    }
}
