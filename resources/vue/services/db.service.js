import axios from 'axios';

const API_URL = '/a/';

class DbService {
    getWallet(){
        return axios.post(API_URL + 'profile/wallets').then(
            response => {
            return response;
        })
    }

    getCurrency(){
        return axios.post(API_URL + 'get_currency').then(
            response => {
                return response.data;
            }
        )
    }

    getCountries(){
        return axios.post(API_URL + 'countries').then(
            response => {
                return response.data;
            }
        )
    }

    getPaymentSystem(){
        return axios.post(API_URL + 'get_ps').then(
            response => {
                return response.data;
            }
        )
    }

    getBonuses(){
        return axios.post(API_URL + 'bonuses').then(
            response => {
                return response.data;
            }
        )
    }

    getDepositBonuses(){
        return axios.post(API_URL + 'user/deposit_bonuses').then(
            response => {
                return response.data;
            }
        )
    }

    getTransactionHistory(payload = null){
        return axios.post(API_URL + 'user/transaction', payload).then(
            response => {
                return response.data;
            }
        )
    }

    getGamePlayed(){
        return axios.post(API_URL + 'user/game_played').then(
            response => {
                return response.data;
            }
        )
    }
}

export default new DbService();
