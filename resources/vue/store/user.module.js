import AuthService from '../services/auth.service';
import { wallets } from "./wallets.module";

const {user: auth, device_type} = window?.common_data

export const user = {
    namespaced: true,
    modules: {
        wallets
    },
    state: {
        user: auth,
        status: {
            loggedIn: !!auth
        },
        deposits: null,
        device: device_type,
        session: [],
    },
    actions: {
        login({commit}, user) {
            return AuthService.login(user).then(
                response => {
                    if(response.data.user){
                        commit('loginSuccess', response.data.user);
                    }
                    return Promise.resolve(response.data);
                },
                error => {
                    commit('loginFailure');
                    return Promise.reject(error);
                }
            );
        },
        logout({commit}) {
            return AuthService.logout().then(
                response => {
                    commit('logout')
                    return Promise.resolve(response);
                }
            );
        },
        register({commit}, user) {
            return AuthService.register(user).then(
                response => {
                    commit('registerSuccess');
                    return Promise.resolve(response);
                },
                error => {
                    commit('registerFailure');
                    return Promise.reject(error);
                }
            );
        },
        changePassword({commit}, password) {
            return AuthService.changePassword(password).then(
                response => {
                    return Promise.resolve(response.data);
                },
                error => {
                    return Promise.reject(error);
                }
            )
        },
        forgotPassword({commit}, email) {
            return AuthService.forgotPassword(email).then(
                response => {
                    commit('registerFailure');
                    return Promise.resolve(response.data);
                },
                error => {
                    return Promise.reject(error);
                }
            )
        },
        updatedPassword({commit}, reset) {
            return AuthService.updatePassword(reset).then(
                response => {
                    commit('registerFailure');
                    return Promise.resolve(response);
                },
                error => {
                    return Promise.reject(error);
                }
            )
        },
        changeUser({commit}, user) {
            return AuthService.changeUser(user).then(
                response => {
                    if (response.data.success) {
                        commit('changeUser', response.data)
                    }
                    return Promise.resolve(response.data);
                }
            )
        },
    },
    mutations: {
        setUser(state, user) {
            state.user = user.user;
            state.status.loggedIn = !!user.user;
        },
        loginSuccess(state, user) {
            state.user = user.user;
            state.status.loggedIn = true;
        },
        loginFailure(state) {
            state.status.loggedIn = false;
            state.user = null;
        },
        logout(state) {
            state.status.loggedIn = false;
            state.user = null;
        },
        registerSuccess(state) {
            state.status.loggedIn = false;
        },
        registerFailure(state) {
            state.status.loggedIn = false;
        },
        changeUser(state, user) {
            state.user = user.user
        },
        approvedDeposits(state, deposit) {
            state.deposits = deposit
        },
        setDevice(state, device) {
            state.device = device
        },
        SET_SESSION(state, payload) {
            state.session = payload
        },
    }
};
