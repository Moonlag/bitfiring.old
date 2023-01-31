import {io} from 'socket.io-client'
import connectModule from './connect'
import chatModule from "./chat"

export default {
    client: connectModule(io),
    chat: chatModule(io('http://localhost:3001', { autoConnect: false }))
}
