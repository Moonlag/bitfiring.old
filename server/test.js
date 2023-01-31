require('dotenv').config({path: '../.env'})
const app = require('express')
const axios = require('axios')
const fs = require("fs"),
    http = require('https');

const privateKey = fs.readFileSync('./../../../../conf/web/bitfiring.com/ssl/bitfiring.com.key').toString();
const certificate = fs.readFileSync('./../../../../conf/web/bitfiring.com/ssl/bitfiring.com.crt').toString();
const credentials = {key: privateKey, cert: certificate};
const server = http.createServer(credentials, app);

(async () => {
	
	console.log('https://127.0.0.1:3000/a/success_deposit_test');
	
    try {
        const {data} = await axios.post('https://bitfiring.com/a/success_deposit_test', {
            user_id: 4,
            payment_id: null,
            bonus_id: null,
            amount: null,
        }, {
            headers: {
                'Content-Type': 'application/json'
            }
        })

        console.log(data);


    } catch (e) {
        console.error(e)
    }

})()