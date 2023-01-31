const httpServer = require("http").createServer();
const Redis = require("ioredis");
const redisClient = new Redis();
const redisClientProxy = redisClient.duplicate();
const fs = require('fs')
const {handlerAchievements} = require('./mysql/database')


const io = require("socket.io")(httpServer, {
    cors: {
        origin: "*",
    },
    adapter: require("socket.io-redis")({
        pubClient: redisClient,
        subClient: redisClient.duplicate(),
    }),
});

const events = new Map();

const eventFiles = fs.readdirSync(`${__dirname}/events`)
    .filter((file) => file.endsWith(".js"));

for (const file of eventFiles) {
    const event = require(`${__dirname}/events/${file}`);
    const eventName = `achievement-${file.replace('.js', '')}`
    events.set(eventName, event)
    redisClient.subscribe(eventName)
}

redisClient.on('message', async function (channel, message) {
    const data = JSON.parse(message).data;
    console.log(data)
    try {
        const event = events.get(channel);
        const success = await event.execute(data)

        if (success.length) {
            const {user_id} = data
            const newAchievements = await handlerAchievements(user_id, success);
            console.log('NEW:', newAchievements)

            // io.emit(`achievement-${user_id}`, newAchievements)
        }
    } catch (e) {
        console.error(e)
    }

})

io.listen(3016)
