const WebSocket = require('ws')

class Btc {
    constructor() {
        this.ws = new WebSocket('ws://127.0.0.1:3003', {rejectUnauthorized: false});
    }
}

module.exports = Btc;
