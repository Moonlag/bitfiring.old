import { io } from "socket.io-client";

const URL = "https://bitchat.bitfiring.com:3002/users";
const socket = io(URL, { autoConnect: false});

socket.onAny((event, ...args) => {
    console.log(event, args);
});

socket.on("connect_error", (err) => {
    console.log(err.message)
});

export default socket;
