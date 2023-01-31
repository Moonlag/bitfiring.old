import { io } from "socket.io-client";
const URL = "https://bitfiring.com:3001";
const socket = io(URL, { autoConnect: false });

export default {
    install: (app, option) => {
        app.config.globalProperties.$io = socket
        app.provide('io', socket)
    }
}
