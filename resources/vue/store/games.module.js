import GameService from '../services/game.service';

const category = {
    active: "main",
    search: {id: 0, active: true, model: '', query: {id: 4}, current_page: 0, total: 0, games: [], slug: 'search'},
    main: {id: 0, current_page: 0, total: 0, games: [], slug: 'main', last_page: 0},
    slots: {id: 1, category_id: 1, current_page: 0, total: 0, games: [], slug: 'slots', last_page: 0},
    roulette: {id: 2, category_id: 3, current_page: 0, total: 0, games: [], slug: 'roulette', last_page: 0},
    blackjack: {id: 3, category_id: 29, current_page: 0, total: 0, games: [], slug: 'blackjack', last_page: 0},
    baccarat: {id: 4, category_id: 2, current_page: 0, total: 0, games: [], slug: 'baccarat', last_page: 0},
    jackpot: {id: 5, category_id: 28, current_page: 0, total: 0, games: [], slug: 'jackpot', last_page: 0},
    played:  {id: 6, total: 0, current_page: 0, last_page: 0, games: [], slug: 'played'},
};

export const games = {
    namespaced: true,
    state: category,
    actions: {
        onLoadMore({commit, state, rootGetters, rootState }) {

            let current = state[state.active];
            let slug = current.slug;
            let query = {};

            if (rootState.user.status.loggedIn) {
                let wallet = rootGetters['user/wallets/primary_wallet']
                query.currency_id = wallet.currency_id
            }

            query.category_id = current.category_id || 0;
            query.page = current.current_page + 1;

            return GameService.getGames(
                query
            ).then(
                response => {
                    commit('SET_GAMES', {games: response.data, slug})
                    return Promise.resolve(response.data);
                }
            )
        },
        onLoadPlayed({commit, state}) {
            let current = state.played;
            let query = {};

            query.page = current.current_page + 1;

            return GameService.getLastPlayed(
                query
            ).then(
                response => {
                    commit('SET_PLAYED', {games: response.data.games})
                    return Promise.resolve(response.data.games);
                }
            )
        },
        onLoadSearch({commit, state, rootState, rootGetters}){
            let search = state.search.model.length ? state.search : false;
            let current = state.main;
            let query = {};

            query.page = !!search ? search.current_page + 1 : current.current_page + 1;
            let method = !!search ? 'SET_SEARCH' : 'SET_MAIN' ;

            if (rootState.user.status.loggedIn) {
                let wallet = rootGetters['user/wallets/primary_wallet']
                query.currency_id = wallet.currency_id
            }

            if(search){
                query.search = search.model;
            }

            return GameService.getGames(
                query
            ).then(
                response => {
                    commit(method, {games: response.data})
                    return Promise.resolve(response.data);
                }
            )
        }
    },
    mutations: {
        SET_DEFAULT(state, slug) {
            state.search.current_page = 0
            state.search.games = []
            state.search.total = 0
            state.main = category.main;
            state.slots = category.slots;
            state.roulette = category.roulette;
            state.blackjack = category.blackjack;
            state.baccarat = category.baccarat;
            state.jackpot = category.jackpot;
        },
        ACTIVE_FILTER(state, slug) {
            state.active = slug
        },
        SET_GAMES(state, {games, slug}) {
            const {data, meta} = games
            state[slug].games.push(...data)
            state[slug].current_page = meta.current_page
            state[slug].total = meta.total
            state[slug].last_page = meta.last_page
        },
        SET_PLAYED(state, {games}) {
            const {data, current_page, total} = games
            state.played.games.push(...data)
            state.played.current_page = current_page
            state.played.total = total
        },
        SET_SEARCH(state, {games}) {
            const {data, current_page, total} = games
            state.search.games.push(...data)
            state.search.current_page = current_page
            state.search.total = total
        },
        SET_MAIN(state, {games}) {
            const {data, current_page, total} = games
            state.main.games.push(...data)
            state.main.current_page = current_page
            state.main.total = total
        },
        ON_SEARCH(state, model) {
            state.search.model = model
        },
        CLEAR_SEARCH(state) {
            state.search.current_page = 0;
            state.search.games = [];
            state.search.total = 0;
        },
        REMOVE_SEARCH(state) {
            state.search.model = '';
        },
        TOGGLE_SEARCH(state) {
            state.search.active = !state.search.active
        },
        CLEAR_PLAYED(state) {
            state.played.current_page = 0
            state.played.total = 0
            state.played.games = []
        },
        ADD_LAST_PLAYED(state, game) {
            if (state.played.games.some(el => el.id === game.id)) {
                state.played.games = state.played.games.filter(el => el.id !== game.id)
            } else {
                if (state.played.games.length >= (28 * state.played.current_page)) {
                    state.played.games.pop()
                }
            }

            state.played.games.unshift(game)
        }
    }
};
