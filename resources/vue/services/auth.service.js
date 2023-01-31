import axios from 'axios';

const API_URL = '/a/';


class AuthService {
    login(user) {
        return axios
            .post(API_URL + 'auth', {
                email: user.email,
                password: user.password,
                viewport: user.viewport
            })
    }

    logout() {
        return axios
            .post(API_URL + 'logout').then(response => {
                return response.data;
            })
    }

    register(user) {
        return axios.post(API_URL + 'join_reg', {
            email: user.email,
            password: user.password,
            affiliate_aid: user.affiliate_aid,
            condition: user.condition,
            viewport: user.viewport,
            query_string: user.query_string,
            prize: user.prize,
            lucky: user.lucky,
            promo: user.promo
        });
    }

    changePassword(password) {
        return axios
            .post(API_URL + 'change_pass', {
                old_password: password.old_password,
                new_password: password.new_password,
                repeat_password: password.repeat_password
            })
    }

    forgotPassword(email) {
        return axios.post('/forgot-password', {
            email: email
        });
    }

    updatePassword(reset) {
        return axios.post('/reset-password', {
            email: reset.email,
            token: reset.token,
            password: reset.password,
            password_confirmation: reset.password_confirmation,
        });
    }

    changeUser(user) {
        return axios
            .post(API_URL + 'profile/update', user)
    }

    getWallets(payload) {
        return axios
            .post(API_URL + 'user/get_wallets').then(response => {
                return response.data;
            })
    }

    getBonuses(payload) {
        return axios
            .post(API_URL + 'user/get_bonuses').then(response => {
                return response.data;
            })
    }
}

export default new AuthService();
