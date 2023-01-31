import axios from 'axios';

export const lucky = {
    namespaced: true,
    state: {
        config: {
            gameWidth: 424,
            gameHeight: 528,
            tickers: [],
            re_spin: 0,
            spin: true,
            winn: 0,
            auth: false
        },
        isOpen: false
    },
    actions: {
        loadConfig({commit, state}) {
            axios.post('/lucky_wheel/auth').then(response => {
                const {data} = response;
                commit('SET_CONFIG', data)
                return Promise.resolve(data);
            })
        }
    },
    mutations: {
        SET_CONFIG(state, payload) {
            state.config.tickers = payload.tickers
            state.config.re_spin = payload.re_spin
            state.config.spin = payload.spin
            state.config.winn = payload.winn
            state.config.auth = payload.auth
        },
        SET_SPIN(state, payload){
            state.config.spin = payload
        },
        OPEN(state){
            state.isOpen = true
        },
        CLOSE(state){
            state.isOpen = false
        },
    }
};
