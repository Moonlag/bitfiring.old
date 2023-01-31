const httpServer = require("http").createServer();
const Redis = require("ioredis");
const crypto = require("crypto");
const redisClient = new Redis();
const redisClientProxy = redisClient.duplicate();
const io = require("socket.io")(httpServer, {
    cors: {
        origin: "*",
    },
    adapter: require("socket.io-redis")({
        pubClient: redisClient,
        subClient: redisClient.duplicate(),
    }),
});
const subscribes = {}
const site = 'bitfiring.com:3003'

io.listen(3016)


const parsed = (url)=> {
    let urlParsed = new URL(url)
    return urlParsed.pathname + urlParsed.search
}

const randomId = () => crypto.randomBytes(8).toString("hex");

let wait;
let reject;
let resolve;

redisClient.subscribe(`channel-games`);
redisClient.subscribe(`on connection`);
redisClient.subscribe(`start server`);
redisClient.on('message', async function (channel, message) {

    console.log(`Received ${message} from ${channel}`);

    if (channel === 'channel-games') {
        wait = () => new Promise((ok, fail) => {
            resolve = ok;
            reject = fail
        })

        const {url, user, provider, game_id} = JSON.parse(message).data
        subscribes[url] = {
            user,
            prefix: `/${randomId()}${user}`
        }

        redisClientProxy
            .multi()
            .set(`urls`, JSON.stringify(subscribes))
            .exec();

        redisClientProxy.publish('restart', true)

        await wait();
        console.log('wait')
        redisClientProxy.publish('start game', JSON.stringify({user, info: {url: `https://${site}${parsed(url)}`, user, provider, game_id} }));
        setTimeout(() => {
            delete subscribes[url]
        }, 60000)
    }

    if (channel === 'on disconnected') {
        const {url} = JSON.parse(message).data
        delete subscribes[url]
    }

    if(channel === 'start server'){
        console.log('start server')
        if(resolve)
        resolve(true);
    }
});
