export default function (socket){
    return {
        connect(user){
            socket.auth = {user: user};
            socket.connect();
        },
        message(payload){
            socket.emit('private message', payload)
        },
        new_message(callback){
            socket.on('private message', (msg) => {
                callback(msg)
            })
        },
    }
}
