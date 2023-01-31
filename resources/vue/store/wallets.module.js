import AuthService from '../services/auth.service';
import {data} from './data.module'

export const wallets = {
    namespaced: true,
    state: {
        bonuses: window.common_data.user?.bonuses || [],
        wallets: window.common_data.user?.wallets || [],
    },
    getters: {
        wallets(state) {
            try {
                return state.wallets.map(el => {
                    const bonuses = state.bonuses;
                    el.currency = data.state.currency.find(cur => el.currency_id === cur.id)
                    el.fiat = parseFloat(el.balance) / el.currency.rate
                    el.locked = bonuses.map(bs => bs.fixed_amount).reduce((a, b) => a + b, 0)
                    el.bonus_balance = bonuses.map(bs =>{
                        if(!bs.locked_amount) return 0
                        return (bs.fixed_amount * (bs.amount / bs.locked_amount))
                    }).reduce((a, b) => a + b, 0)
                    el.real_balance = el.balance - el.bonus_balance;
                    return el
                })
            } catch (e) {
                return []
            }
        },
        primary_wallet(state, getters) {
            return getters.wallets.length ? getters.wallets.find(el => el.primary) : false;
        },
        current_balance: (state, getters) => {
            return getters.wallets.map(el => {
                return el.fiat
            }).reduce((a, b) => a + b, 0).toFixed(8)
        },
        locked_balance: (state) => {
            return state.bonuses.map(el => (el.fixed_amount)).reduce((a, b) => a + b, 0)
        },
        bonus_balance: (state) => {
            return state.bonuses.map(el => {
                if(!el.locked_amount) return 0
                return (el.fixed_amount * (el.amount / el.locked_amount))
            }).reduce((a, b) => a + b, 0)
        },
        real_balance: (state, getters) => {
            return getters.current_balance - getters.bonus_balance || 0
        },
        withdrawable_balance: (state, getters) => {
            return getters.current_balance - getters.locked_balance < 0 ? 0 : getters.current_balance - getters.locked_balance
        }
    },
    actions: {
        getWallets({commit}) {
            return AuthService.getWallets().then(
                response => {
                    commit('setWallets', response.wallets)
                    return Promise.resolve(response.wallets);
                }
            )
        },
        getBonuses({commit}) {
            return AuthService.getBonuses().then(
                response => {
                    commit('setBonuses', response.bonuses)
                    return Promise.resolve(response.bonuses);
                }
            )
        },
    },
    mutations: {
        setWallets(state, wallets) {
            state.wallets = wallets
        },
        UPDATE_WALLET(state, payload) {
            let wallet = state.wallets.find(el => el.id === payload.wallet_id);
            let idx = state.wallets.findIndex(el => el.id === payload.wallet_id);
            wallet.balance = payload.amount
            state.wallets[idx] = wallet
        },
        updateBalance(state, payload) {
            state.wallets.find(el => el.id === payload.wallet_id).balance = payload.balance.toFixed(8)
        },
        setBonuses(state, payload) {
            state.bonuses = payload.map(el => ({
                id: el.id,
                amount: Number(el.amount),
                fixed_amount: Number(el.fixed_amount),
                locked_amount: Number(el.locked_amount),
                to_wager: Number(el.to_wager),
                wagered: Number(el.wagered),
                currency_id: Number(el.currency_id),
                bonus_ratio: Number(el.bonus_ratio),
            }))
        },
        new_bonuses(state, payload) {
            state.bonuses.push(payload)
        },
        updatePrimaryWallet(state, id) {
            state.wallets.forEach(el => {
                el.primary = el.id === id ? 1 : 0
            })
        },
        clear(state) {
            state.wallets = []
            state.bonuses = []
        }
    }
};
