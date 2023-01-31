import axios from 'axios';

const API_URL = '/a/';

class GameService {
    getGames(payload) {
        return axios.post('/api/games', payload, {
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(
            response => {
                return response;
            })
    }

    getGamesCategory(payload) {
        return axios.post(API_URL + 'games/category', payload).then(
            response => {
                return response;
            })
    }

    getLastPlayed(payload) {
        return axios.post(API_URL + 'games/played', payload).then(
            response => {
                return response;
            })
    }

    getOption(payload) {
        return axios.post(API_URL + 'games/option', payload).then(
            response => {
                return response;
            })
    }

    getNew(payload) {
        return axios.post(API_URL + 'games/new', payload).then(
            response => {
                return response;
            })
    }

}

export default new GameService();
