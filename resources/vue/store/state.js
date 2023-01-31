import { createStore, mapGetters } from 'vuex';
import { user } from "./user.module";
import { data } from "./data.module";
import {games} from "./games.module";
import {lucky} from "./lucky.module";
import {preloader} from "./preloader.module";
import {notification} from "./notification.module";
import {lang} from "./lang.module";

export default createStore({
    modules: {
        user,
        data,
        games,
        lucky,
        preloader,
        notification,
        lang
    },
    state(){
        return {
            state_pop: [
                {id: 1, slug: 'registration' , title: 'Registration'},
                {id: 2, slug: 'login' , title: 'Login'},
                {id: 3, slug: 'forgot' , title: 'Forgot'},
                {id: 4, slug: 'change-password' , title: 'ChangePassword'},
                {id: 5, slug: 'popup' , title: 'Cancel'},
                {id: 6, slug: 'verify' , title: 'Verify'},
                {id: 7, slug: 'wheel-pixi' , title: 'Verify'},
                {id: 8, slug: 'convert' , title: 'Verify'},
                {id: 9, slug: 'banned-user' , title: 'Verify'},
                {id: 10, slug: 'pixi' , title: 'Pixi'},
            ],
            pop: '',
            pop_arg: null,
            args_swap: null,
            args_reject: null,
            popmessage: '',
            outside: false,
            forgot: {
                email: '',
                token: '',
                password: '',
                password_confirmation: ''
            },
            cancelBonus:{
                resolve: null,
            },
            activeBonus:{
                resolve: null,
            },
            withdrawal:{
                resolve: null,
            },
            gameReject: {
                params: {}
            }
        }
    },
    actions:{
         info({state, commit}, pop){
            commit('info_pop', pop)
        },
        cancelBonus({state, commit}){
             return new Promise(resolve => {
                 commit('open_cancelBonus', {resolve})
             })
        },
        activeBonus({state, commit}){
             return new Promise(resolve => {
                 commit('open_activeBonus', {resolve})
             })
        },
        rejectWithdraw({state, commit}){
            return new Promise(resolve => {
                commit('open_reject_withdraw', {resolve})
            })
        },
        approveWithdraw({state, commit}, payload){
            return new Promise(resolve => {
                commit('open_withdrawal', {resolve, amount: payload.amount, ps: payload.ps})
            })
        }
    },
    mutations: {
        update_profile(state, payload){
            state.user = payload;
        },
        close_pop(state){
            state.pop = '';
            state.pop_arg = null;
            state.args_reject = null;
            state.args_swap = [];
            const el = document.body;
            el.classList.remove('fancybox-active', 'compensate-for-scrollbar');
        },
        open_pop(state, payload){
            state.outside = false
            state.pop = state.state_pop.find(el => el.id === payload).slug
            const el = document.body;
            el.classList.add('fancybox-active', 'compensate-for-scrollbar');
        },
        info_pop(state, pop){
            state.popmessage = pop
            state.pop = 'info'
        },
        open_verify(state, payload){
            state.pop = state.state_pop.find(el => el.id === 6).slug
            state.forgot.email = payload.email
            state.forgot.token = payload.token
        },
        open_submit(state, payload){
            state.pop = state.state_pop.find(el => el.id === 5).slug
            state.pop_arg = payload;
        },
        open_reject_withdraw(state, payload){
            state.pop = 'withdrawal-reject'
            state.pop_arg = payload;
        },
        open_swap(state, payload){
            state.pop = 'convert'
            state.args_swap = payload;
        },
        close_swap(state){
            state.pop = ''
            state.args_swap = [];
        },
        open_reject(state, payload){
            state.pop = 'deposit-reject'
            state.args_reject = payload
        },
        open_wager(state, payload){
            state.pop = 'bonus-wager'
        },
        open_withdrawal(state, {resolve, amount, ps}){
            state.withdrawal.resolve = resolve
            state.withdrawal.amount = amount
            state.withdrawal.ps = data.state.payment_system.find(el => el.id === Number(ps))
            state.pop = 'withdrawal'
        },
        open_withdrawal_cancel(state){
            state.pop = 'withdrawal-cancel'
        },
        open_cancelBonus(state, {resolve}){
            state.pop = 'cancel-bonus'
            state.cancelBonus.resolve = resolve
        },
        open_activeBonus(state, {resolve}){
            state.pop = 'active-bonus'
            state.activeBonus.resolve = resolve
        },
        open_gameReject(state, params){
            state.pop = 'game-reject'
            state.gameReject.params = params
        },
    },
})
