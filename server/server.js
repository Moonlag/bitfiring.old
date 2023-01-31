require('dotenv').config({path: '../.env'})
const app = require('express')
const axios = require('axios')
const fs = require("fs"),
    http = require('https');

const privateKey = fs.readFileSync('./../../../../conf/web/bitfiring.com/ssl/bitfiring.com.key').toString();
const certificate = fs.readFileSync('./../../../../conf/web/bitfiring.com/ssl/bitfiring.com.crt').toString();
const credentials = {key: privateKey, cert: certificate};
const server = http.createServer(credentials, app);
const Redis = require("ioredis");
const redisClient = new Redis();

const io = require('socket.io')(server, {
    cors: {
        origin: "*",
    },
    adapter: require("socket.io-redis")({
        pubClient: redisClient,
        subClient: redisClient.duplicate(),
    }),
});

const affiliates_server = require('socket.io-client')('https://affiliates.bitfiring.com:3001/', {
    secure: true,
    reconnect: true,
    rejectUnauthorized: false,
    transports: ['websocket']
})

const subClientProxy = redisClient.duplicate();

const {InMemorySessionStore} = require("./sessionStore");
const sessionStore = new InMemorySessionStore();
const {Deposit} = require("./DepositClass.js");

io.use((socket, next) => {
    const userID = socket.handshake.auth.userID;
    if (!userID) {
        return next(new Error("invalid id"));
    }
    socket.userID = userID
    next();
})

io.on('connection', (socket) => {
    console.log(socket.userID)
    socket.join(socket.userID)

    socket.on('cancel', () => {
        // sessionStore.findSession(socket.userID).deposit.clear_evt()
        socket.to(socket.userID).emit('cancel')
    })

    socket.on('logout', () => {
        socket.to(socket.userID).emit('logout')
    })

    socket.on('new deposit', () => {
        socket.to(socket.userID).emit('new deposit')
    })
})

affiliates_server.on("connect_error", (err) => {
    console.log('error', err);
});


subClientProxy.subscribe(`channel-deposit`);
subClientProxy.subscribe(`channel-wallet`);
subClientProxy.subscribe(`swap-balance`);
subClientProxy.subscribe(`start game`);
subClientProxy.subscribe(`update-balance`);
subClientProxy.subscribe(`session-kick`);
subClientProxy.subscribe(`channel-notification`);
subClientProxy.on('message', async function (channel, message) {
    console.log(`Received ${message} from ${channel}`);

    if (channel === `channel-deposit`) {
        const data = JSON.parse(message).data;

        const deposit = new Deposit(data, io)
        let payment = sessionStore.findSession(Number(data.payment_id))

        // if(data.user == 4){
        //     const bets = await deposit.get_bets({id: 307})
        //     console.log(bets)
        //     for(let bet of bets){
        //         let amount = (Number(bet.profit) / 0.00002366).toFixed(8)
        //         console.log(amount);
        //         affiliates_server.emit('bet', {player_id: bet.user_id, bet: amount})
        //     }
        // }

        if (payment) {
            payment.deposit.clear_evt();
        }
        sessionStore.saveSession(Number(data.payment_id), {deposit})

        if (data.user <= 4 || data.user === 230 || data.user === 827 || data.user === 1551 || data.user === 2632) {
            deposit.tester_user()
            return
        }

        switch (data.payment_system_id) {
            case 3:
                deposit.doge_handler()
                break;
            case 10:
                deposit.eth_handler()
                break;
            case 11:
                deposit.btc_handler()
                break;
            case 12:
                deposit.usdt_trc20_handler()
                break;
            case 13:
                deposit.usdt_erc20_handler()
                break;
        }
    }

    if (channel === `channel-wallet`) {
        const {user, wallet_id, amount, bet, currency_id} = JSON.parse(message).data;
        affiliates_server.emit('bet', {player_id: user, bet: bet, currency_id: currency_id})
        io.to(user).emit('bet', {wallet_id: wallet_id, amount: amount});
    }

    if (channel === `start game`) {
        const {user, info} = JSON.parse(message);
        console.log(info)
        io.to(user).emit('start game', info);
    }

    if (channel === `swap-balance`) {
        const {user, from, to} = JSON.parse(message).data;
        console.log(from, to)
        io.to(user).emit('swap balance', {from, to});
    }

    if (channel === `session-kick`) {
        const {user} = JSON.parse(message).data;
        console.log('session-kick', user)
        io.to(user).emit('logout');
    }

    if (channel === `update-balance`) {
        const {user} = JSON.parse(message).data;
        io.to(user).emit('update balance');
    }

    if (channel === `channel-notification`) {
        const {user} = JSON.parse(message).data;
        io.to(user).emit('update notification');
    }
});

server.listen(3000, function () {
    console.log('Listening on Port: 3000');
})
