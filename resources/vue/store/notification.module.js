import axios from "axios"

export const notification = {
    namespaced: true,
    state: {
        bonuses: [],
        freespins: [],
    },
    actions: {
        getNotification({commit}) {
            return axios.post("/a/user/bonuses").then(
                response => {
                    commit('setNotification', response.data)
                    return Promise.resolve(response.data);
                }
            )
        },
    },
    mutations: {
        setNotification(state, payload) {
            console.log(payload)
            state.bonuses = payload.bonuses
            state.freespins = payload.freespin
        },
    }
};
