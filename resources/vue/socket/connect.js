export default function (io){
    let socket = '';
    return {
        connect(){
            socket = io('http://localhost:3000')
        },
        socketID(){
            return socket.id
        },
        disconnect(){
            if(socket){
                socket.disconnect((evt) => {
                    console.log(evt)
                });
            }
        },
        subscribe(payload){
            socket.on("connect", () => {
                socket.emit('subscribe', payload)
            });
        },
        subscribeWallet(user, callback){
            socket.on(`channel-wallet-${user}`, (msg) => {
                callback(msg)
            })
        },
        subscribeDeposit(user, callback){
            socket.on(`channel-deposit-${user}`, (msg) => {
                callback(msg)
            })
        },
        unSubscribeDeposit(user){
            socket.off(`channel-deposit-${user}`)
        }
    }
}
