require('dotenv').config({path: '../.env'})
const WebSocket = require('ws')
const axios = require("axios");
const DataBase = require("./database.js");
const dayjs = require('dayjs')
const utc = require('dayjs/plugin/utc')

class Deposit extends DataBase {
    constructor({
                    user,
                    address,
                    amount,
                    payment_id,
                    wallet_id,
                    payment_system_id,
                    address_id,
                    bonus,
                    currency_id,
                    aid,
                    usd,
                    freespin,
                    bonus_option_id
                }, io) {
        super();
        this.status = 0;
        this.user = user;
        this.address = address.wallet;
        this.address_id = address.id;
        this.amount = Number(amount);
        this.payment_id = payment_id;
        this.wallet_id = wallet_id;
        this.payment_system_id = Number(payment_system_id);
        this.address_id = address_id;
        this.bonus = bonus;
        this.ws = '';
        this.io = io;
        this.interval = '';
        this.aid = aid;
        this.currency_id = Number(currency_id);
        this.usd = Number(usd);
        this.hash = null;
        this.freespin = freespin;
        this.bonus_option_id = bonus_option_id;
        this.time = (60 * 1e3) * 30;
        this.timeout = setTimeout(() => {
            this.clear_evt()
            this.update_wallet_temp({address_id: this.address_id, used: 0});
            this.update_payment({status: 3, source_transaction: null, amount: null, usdt: null, id: this.payment_id})
            if (this.bonus_option_id) {
                this.update_bonus_option({enabled: 1, id: this.bonus_option_id})
            }
            this.io.to(this.user).emit('channel deposit', {success: 1})
            this.io.to(this.user).emit('deposit reject', {amount: amount})
            axios.post(process.env.APP_URL + '/a/reject_deposit', {
                user_id: user,
                payment_id: payment_id,
                wallet_id: address_id
            }, {
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                console.log(response.data)
            }).catch(error => {
                console.log(error)
            })
        }, this.time);
        this.check_interval = setInterval(() => {
            this.time -= 60 * 1e3
            console.log('Time', this.time)
        }, (60 * 1e3))
        this.until = {1: 'hour', 2: 'day', 3: 'week', 4: 'month', 5: 'year'};
        this.test = null
    }

    btc_handler() {
        dayjs.extend(utc)
        const now = dayjs.utc().unix();

        this.websocket('wss://ws.blockchain.info/inv', {
            op: "addr_sub",
            addr: this.address,
        }, (evt) => {
            console.log(evt);
            if (evt.op === 'utx') {
                let x = evt.x;
                let getOut = x.out;
                let calAmount = 0;
                let checkAddress = false

                getOut.forEach(el => {
                    console.log(el);
                    let outAmount = Number(el.value)
                    if (this.address.toLowerCase() == el.addr.toLowerCase()) {
                        calAmount += (outAmount / 100000000);
                        this.hash = x.hash
                        checkAddress = true;
                        console.log(calAmount)
                    }
                })

                this.check_deposit(checkAddress, calAmount)
            }
        })

        this.interval = setInterval(() => {
            this.http_axios('https://blockchain.info/rawaddr/' + this.address, null).then(response => {
                let result = response.data
                console.log('axios:', result);
                result = result.txs.filter(el => Number(el.time) >= now)

                for (let el of result) {
                    const calAmount = (Number(el.balance) / 100000000);
                    this.hash = el.hash
                    this.check_deposit(true, calAmount, 2)
                    breakж
                }
            }).catch(error => {
                console.log(error)
            })
        }, (60 * 1000))
    }

    doge_handler() {
        this.websocket('wss://ws.dogechain.info/inv', {
            op: "addr_sub",
            addr: this.address
        }, (evt) => {
            console.log(evt)
            if (evt.op === 'utx') {
                let x = evt.x;
                let getOut = x.outputs;
                let calAmount = 0;
                let checkAddress = false

                getOut.forEach(el => {
                    let outAmount = Number(el.value)
                    if (this.address.toLowerCase() == el.addr.toLowerCase()) {
                        calAmount += (outAmount / 100000000);
                        this.hash = x.hash
                        checkAddress = true;
                        console.log(calAmount)
                    }
                })

                this.check_deposit(checkAddress, calAmount)
            }
        });

        this.interval = setInterval(() => {
            this.http_axios('https://dogechain.info/api/v1/address/received/' + this.address, null).then(response => {
                const result = response.data
                console.log('axios:', result);
                if (result.received) {
                    const calAmount = result.received;
                    this.check_deposit(true, calAmount, 2)
                }
            })
        }, (60 * 1000))
    }

    eth_handler() {
        dayjs.extend(utc)
        const now = dayjs.utc().unix();
        console.log('NOW', now)

        this.interval = setInterval(() => {
            this.http_axios('https://api.etherscan.io/api', {
                module: 'account',
                action: 'txlist',
                address: this.address,
                startblock: '0',
                endblock: '99999999',
                sort: 'desc',
                apikey: 'EV28KPQEM9N6ZISWDIJR5CEP71KTC92IY2',
            }).then(response => {
                let result = response.data.result
                result = result.filter(el => Number(el.timeStamp) >= now)
                console.log('axios:', result);
                for (let el of result) {
                    console.log(Number(el.timeStamp), now)
                    const calAmount = (Number(el.value) / 1000000000000000000).toFixed(8);
                    this.amount = calAmount;
                    this.clear_evt();
                    this.hash = el.hash;
                    this.deposit_success()
                    break;
                }
            })
        }, (60 * 1000))
    }


    usdt_erc20_handler() {
        dayjs.extend(utc)
        const now = dayjs().utc().unix();
        console.log('NOW', now)

        this.interval = setInterval(() => {
            this.http_axios('https://api.etherscan.io/api', {
                module: 'account',
                action: 'tokentx',
                address: this.address,
                startblock: '0',
                endblock: '99999999',
                sort: 'desc',
                apikey: 'EV28KPQEM9N6ZISWDIJR5CEP71KTC92IY2',
            }).then(response => {
                let result = response.data.result
                console.log('axios:', result);
                result = result.filter(el => Number(el.timeStamp) >= now && el.tokenSymbol === 'USDT')
                for (let el of result) {
                    const calAmount = (Number(el.value) / 1000000).toFixed(2);
                    this.amount = calAmount
                    this.clear_evt();
                    this.hash = el.hash;
                    this.deposit_success()
                    break;
                }
            })
        }, (60 * 1000))
    }

    usdt_trc20_handler() {
        dayjs.extend(utc);
        const now = dayjs.utc().valueOf();
        this.interval = setInterval(() => {
            this.http_axios(`https://api.trongrid.io/v1/accounts/${this.address}/transactions/trc20?order_by=block_timestamp,desc&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t`).then(response => {
                let {data} = response.data
                data = data.filter(el => el.block_timestamp >= now);
                console.log(data)
                console.log('now:', now)
                for (let el of data) {
                    let calAmount = (Number(el.value) / 1000000).toFixed(2)
                    this.amount = calAmount
                    this.clear_evt();
                    this.hash = el.transaction_id;
                    this.deposit_success()
                    break;

                }
            })
        }, (60 * 1000))
    }

    websocket(url, options, callback) {
        this.ws = new WebSocket(url);

        this.ws.on('open', function open() {
            console.log(`WebSocket open, url:${url}`);
            this.send(JSON.stringify(options));
        });

        this.ws.on('close', function close() {
            console.log('Disconnected');
        });

        this.ws.on('message', (message) => {
            callback(JSON.parse(message))
        })
    }

    http_axios(url, params) {
        return axios.get(url, {
            params: params,
            headers: {
                'Content-Type': 'application/json'
            }
        })
    }

    async tester_user() {
        this.test = setTimeout(async () => {
            this.clear_evt()
            this.status = 1;
            await this.deposit_success()
        }, 60000)
    }

    async deposit_success() {
        const currency = await this.select_currency({id: this.currency_id})

        // if (this.bonus && this.usd >= Number(this.bonus.min)) {
        //     let last_bonus = await this.get_last_active_bonus_issue({user_id: this.user})
        //     if (last_bonus) {
        //         console.log('last_bonus', last_bonus)
        //         await this.handler_user_wallet(this.user, -1 * Number(last_bonus.fixed_amount))
        //         await this.update_bonus_issue({status: 4, id: last_bonus.id})
        //     }
        // }

        const wallet = await this.select_wallet({id: this.wallet_id})
        const {balance} = wallet
        this.usd = (this.amount / Number(currency.rate));
        console.log(Number(currency.rate), this.amount, this.usd, currency);
        if(this.usd > 5000){
            this.usd = 5000;
            this.amount = this.usd * Number(currency.rate)
        }

        await this.update_payment({status: 1, source_transaction: this.hash, amount: this.amount, usdt: this.usd, id: this.payment_id});

        if (this.aid) {
           axios.post(process.env.AFFILATES_APP_URL + 'register_deposit', {
                aid: this.aid,
                player_id: this.user,
                amount: this.usd,
                deposit_id: this.payment_id,
                currency_id: this.currency_id,
                bonus: this.bonus
            }, {
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                console.log('AFFILATES_APP', response.data)
            }).catch(error => {
                console.log('AFFILATES_ERROR',error)
            })
        }

        let new_balance = Number(balance) + Number(this.amount)
        let deposit_amount = this.usd;

        let bonus_crypto, bonus_fiat;
        let bonus_id = false;

        if (this.bonus) {

            let created_at = new Date();

            let percentage = (Number(this.bonus.amount) / 100);

            bonus_fiat = deposit_amount * percentage;

            if (this.payment_system_id === 3 || this.payment_system_id === 11) {
                bonus_fiat = (Number(this.amount) / Number(currency.rate)) * percentage;
            }

            bonus_fiat = bonus_fiat > Number(this.bonus.max) ? Number(this.bonus.max) : bonus_fiat;

            let locked_amount = Number(deposit_amount) + (Number(bonus_fiat));

            let to_wager = locked_amount * this.bonus.wager;

            bonus_crypto = bonus_fiat * Number(currency.rate);
            new_balance += bonus_crypto;
            console.log('bonus new_balance', new_balance)
            await this.bonuses_user({
                user_id: this.user,
                currency_id: this.currency_id,
                wager: this.bonus.wager,
                amount: bonus_fiat,
                bonus_id: this.bonus.id
            });

            let bonus_issue = await this.bonus_issue({
                amount: bonus_fiat,
                locked_amount: locked_amount,
                fixed_amount: locked_amount,
                user_id: this.user,
                currency_id: this.currency_id,
                bonus_id: this.bonus.id,
                to_wager: to_wager,
                payment_id: this.payment_id,
                active_until: !!this.bonus.duration ? dayjs(created_at).add(this.bonus.duration, this.until[this.bonus.duration_type]).format('YYYY-MM-DD HH:mm:ss') : null
            });

            this.io.to(this.user).emit('locked bonus', {
                id: bonus_issue.insertId,
                wagered: 0,
                to_wager: to_wager,
                locked_amount,
                fixed_amount: locked_amount
            })

            if (this.freespin) {
                await this.freespin_issue({
                    title: this.freespin.title,
                    player_id: this.user,
                    currency_id: this.freespin.currency_id,
                    bonus_id: this.freespin.id,
                    count: this.freespin.count,
                    active_until: !!this.freespin.duration ? dayjs(created_at).add(this.freespin.activate_duration, this.until[this.freespin.activate_duration_type]).format('YYYY-MM-DD HH:mm:ss') : null
                })
            }

            bonus_id = bonus_issue.insertId;
        }

        await this.update_wallet({
            new_balance: new_balance,
            new_bonus_balance: 0,
            new_locked_balance: 0,
            id: this.wallet_id
        });

        if (this.currency_id !== 14) {
            await this.handler_swap({
                currency_id: this.currency_id,
                amount: new_balance
            }, {
                currency_id: 14,
                amount: new_balance / currency.rate
            })
        }


        this.io.to(this.user).emit('channel deposit', {success: 1})
        this.io.to(this.user).emit('update notification');
        this.io.to(this.user).emit('update balance');

        this.io.to(this.user).emit('alert', {op: 'deposit', amount: this.amount, currency: currency.code})

        axios.post(process.env.APP_URL + '/a/success_deposit', {
            user_id: this.user,
            payment_id: this.payment_id,
            bonus_id: bonus_id || null,
            amount: this.usd || null,
        }, {
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            console.log(response.data)
             
        }).catch(error => {
            console.log(error)
        })

        if(this.payment_system_id === 10 || this.payment_system_id === 12 || this.payment_system_id === 13){
            this.update_wallet_temp({address_id: this.address_id, used: 0});
        }
    }


    clear_evt() {
        clearTimeout(this.timeout)
        clearTimeout(this.test)
        clearInterval(this.interval)
        clearInterval(this.check_interval)

        if (this.ws) {
            this.ws.close()
        }
        console.log('Clear EVT')
    }

    async check_deposit(checkAddress, calAmount, mode = 1) {
        if (calAmount <= 0) {
            return
        }
        if (checkAddress) {
            if (mode === 1) {
                this.clear_evt()
            }
            this.amount = calAmount
            if (mode === 2) {
                this.clear_evt()
            }
            this.status = 1;
            await this.deposit_success()
        }
    }

    async handler_user_wallet(user_id, amount) {
        let wallet = await this.get_wallet({id: user_id});
        console.log('wallet', wallet)
        if (wallet) {
            let currency = await this.select_currency({id: wallet.currency_id})
            console.log('currency', currency)
            console.log('balance', Number(wallet.balance))
            let new_amount = Number(wallet.balance) + (amount * Number(currency.rate))
            let new_balance = new_amount < 0 ? 0 : new_amount;
            console.log('new_balance', new_balance)
            await this.update_wallet({id: wallet.id, new_balance: new_balance})
            if (new_amount < 0) {
                await this.handler_user_wallet(user_id, new_amount)
            }
        }
    }

    handler_swap(from, to) {
        return new Promise(async (resolve) => {
            try {
                const wallet_from = await this.wallet_swap({
                    user_id: this.user,
                    currency_id: from.currency_id
                })

                const wallet_to = await this.wallet_swap({
                    user_id: this.user,
                    currency_id: to.currency_id
                })

                console.log(wallet_from, wallet_to)

                if (wallet_from && wallet_to) {
                    const {insertId: swapID} = await this.swap({
                        from_id: from.currency_id,
                        from_amount: from.amount,
                        to_id: to.currency_id,
                        to_amount: to.amount
                    });

                    await this.transactions_swap({
                        player_id: this.user,
                        currency_id: from.currency_id,
                        amount: from.amount,
                        reference_id: swapID,
                        type_id: 1,
                        wallet_id: wallet_from.id,
                    });

                    await this.transactions_swap({
                        player_id: this.user,
                        currency_id: to.currency_id,
                        amount: to.amount,
                        reference_id: swapID,
                        type_id: 2,
                        wallet_id: wallet_to.id,
                    });


                    let new_balance_from = Number(wallet_from.balance) - Number(from.amount);
                    new_balance_from = new_balance_from >= 0 ? new_balance_from : 0;

                    console.log('from', new_balance_from);
                    console.log('to', Number(wallet_to.balance) + Number(to.amount));

                    await this.update_wallet({new_balance: new_balance_from, id: wallet_from.id})
                    await this.update_wallet({new_balance: Number(wallet_to.balance) + Number(to.amount), id: wallet_to.id})
                }

            } catch (e) {
                console.log(e)
            }

            resolve(true)
        })

    }

}

module.exports = {Deposit};
