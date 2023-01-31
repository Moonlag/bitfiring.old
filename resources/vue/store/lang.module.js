export const lang = {
    namespaced: true,
    state: {
        locale: ''
    },
    mutations: {
        SET_LOCALE(state, payload){
            state.locale = payload
        },
    }
};
