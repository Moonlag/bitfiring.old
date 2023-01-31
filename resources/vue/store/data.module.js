import DbService from '../services/db.service';

const {
    landing,
    ip_info,
    home_seo_text,
    home,
    home_slider,
    provider,
    currency,
    payment_system
} = window?.common_data

export const data = {
    namespaced: true,
    state: {
        games: [],
        base_url: `/${landing}` || '/',
        providers: provider,
        currency: currency.map(el => {
            return {id: el.id, text: el.code, excluded: el.excluded, rate: el.rate, parent_id: el.parent_id, fee: el.fee}
        }),
        payment_system: payment_system,
        bonuses: null,
        deposit_bonuses: null,
        countries: null,
        ip_info: ip_info || [],
        transactions: [],
        last_played: [],
        home: home || '',
        categories: [
            {id: 1, text: 'Popular', active: false, slug: 'popular', current_page: 1, total: 0, games: []},
            {id: 2, text: 'New', active: false, slug: 'new', current_page: 1, total: 0, games: []},
            {id: 3, text: 'Bonuses', active: false, slug: 'bonus_enabled',  current_page: 1, total: 0, games: []},
            {id: 4, text: 'Races', active: false, slug: 'races',  current_page: 1, total: 0, games: []},
            {id: 5, text: 'Providers', active: false, slug: 'providers'},
            {id: 6, text: 'Jackpots', active: false, slug: 'jp', current_page: 1, total: 0, games: []},
        ],
        filters: [
            {id: 1, text: 'Slots', active: true, slug: 'slot', current_page: 1, total: 0, games: []},
            {id: 2, text: 'Live', active: false, slug: 'live', current_page: 1, total: 0, games: []},
            {id: 3, text: 'Roulette', active: false, slug: 'roulette', current_page: 1, total: 0, games: []},
            {id: 4, text: 'SEARCH', active: false},
            {id: 5, text: 'BlackJack', active: false, slug: 'blackjack', current_page: 1, total: 0, games: []},
            {
                id: 6,
                text: 'Other',
                active: false,
                slug: ['slot', 'live', 'roulette', 'blackjack'],
                current_page: 1,
                total: 0,
                games: []
            },
            {id: 7, text: 'Played', active: false, slug: 'played', current_page: 1, total: 0, games: []},
        ],
        slides: home_slider,
    },
    getters: {
        payment_system(state) {
            let ps = state.payment_system.filter(el => el.id !== 13)
            let idx = ps.findIndex(el => el.id === 12)
            ps[idx] = {
                "id": 12,
                "parent": "",
                "currency_id": 14,
                "name": "USDT",
                "fee": "1.00",
                "minimum": "25.00",
                "image": "usdt",
                "active": 1,
                "sorting": 2,
                "updated_at": null,
                "rate": "1.00000000",
                "code": "USDT",
                "currency": "USDT",
                "children": [...state.payment_system.filter(el => el.id === 13 || el.id === 12)]
            }
            return ps
        },
    },
    actions: {
        currency({commit, state}) {
            if (!state.currency) {
                return DbService.getCurrency().then(
                    response => {
                        commit('updatedCurrency', response.currency)
                        return Promise.resolve(response.currency);
                    }
                )
            }
        },
        countries({commit, state}) {
            if (!state.countries) {
                return DbService.getCountries().then(
                    response => {
                        commit('updatedCountries', response.countries)
                        return Promise.resolve(response.countries);
                    }
                )
            }
        },
        paymentSystem({commit, state}) {
            if (!state.payment_system) {
                return DbService.getPaymentSystem().then(
                    response => {
                        commit('updatedPaymentSystem', response.payment_system)
                        return Promise.resolve(response.payment_system);
                    }
                )
            }
        },
        async bonuses({commit, state}) {
            if (!state.bonuses) {
                return await DbService.getBonuses().then(
                    response => {
                        commit('updatedBonuses', response.bonus)
                        return Promise.resolve(response.bonus);
                    }
                )
            }
        },
        deposit_bonuses({commit, state}) {
            if (!state.deposit_bonuses) {
                return DbService.getDepositBonuses().then(
                    response => {
                        commit('updatedDepositBonuses', response.bonuses)
                        return Promise.resolve(response.bonuses);
                    }
                )
            }
        },
        transaction_history({commit}, payload = null) {
            return DbService.getTransactionHistory(payload).then(
                response => {
                    commit('updateTransactionHistory', response.transactions)
                    return Promise.resolve(response.transactions);
                })
        }
    },
    mutations: {
        SET_IP_INF(state, payload){
            state.ip_info = payload
        },
        setGames(state, games) {
            const {data, current_page, total} = games
            const filter = state.filters.find(el => el.active === true)
            const idx = state.filters.findIndex(el => el.active === true)
            filter.games = data
            filter.current_page = current_page
            filter.total = total
            state.filters[idx] = filter;
        },
        ON_FILTER(state, id) {
            state.filters.map(el => {
                el.active = el.id === id
                return el
            })
        },
        setProviderGame(state, providers) {
            state.providers = providers
        },
        updatedCurrency(state, currency) {
            state.currency = currency.map(el => {
                return {id: el.id, text: el.code, excluded: el.excluded, rate: el.rate, parent_id: el.parent_id, fee: el.fee}
            })
        },
        updatedCountries(state, countries) {
            state.countries = countries.map(el => {
                return {id: el.id, text: el.name, code: el.code}
            })
        },
        updatedPaymentSystem(state, ps) {
            state.payment_system = ps
        },
        updatedBonuses(state, bonus) {
            state.bonuses = bonus
        },
        updatedDepositBonuses(state, bonuses) {
            state.deposit_bonuses = bonuses
        },
        updateTransactionHistory(state, payload) {
            state.transactions = payload
        },
        SET_HOME(state, payload){
            state.home = payload
        },
        updatedHomeSlider(state, payload){
            state.slides = payload
        },
        updatedBlockBrands(state, payload){
            state.brands = payload
        },
        SET_BASE_URL(state, payload){
            state.base_url = `/${payload}`
        },
        SET_HOME_SEO_TEXT(state, payload){
            state.home_seo_text = payload?.text
        }

    }
};
