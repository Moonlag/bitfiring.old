import { io } from "socket.io-client";
const URL = "https://bitfiring.back-dev.com:3008";
const socket = io(URL, { autoConnect: false });

export default {
    install: (app, option) => {
        app.config.globalProperties.$whell = socket
        app.provide('whell', socket)
    }
}
