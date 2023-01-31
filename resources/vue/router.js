import {createRouter, createWebHistory} from 'vue-router';
import Providers from "./views/ProvidersAll";
import Provider from "./views/Provider";
import MainComponent from "./views/Home";
import NotFound from "./views/404";
import Play from "./views/Play";
import Verify from "./views/Verify";
import Profile from "./views/Profile";
import Promotions from "./views/Profile/Promotions";
import YourProfile from "./views/Profile/YourProfile";
import Personal from "./views/Profile/Personal";
import state from "./store/state";
import GameHistory from "./views/Profile/GameHistory";
import Support from "./views/Profile/Support";
import Tournaments from "./views/Tournaments";
import Bonuses from "./views/Bonuses";
import Maintenance from "./views/Maintenance";
import BalanceComponent from "./views/Profile/Personal/BalanceComponent";
import DepositComponent from "./views/Profile/Personal/DepositComponent";
import WithdrawalComponent from "./views/Profile/Personal/WithdrawalComponent";
import TransactionHistoryComponent from "./views/Profile/Personal/TransactionHistoryComponent";
import BonusesComponent from "./views/Profile/Promotions/BonusesComponent";
import FreespinsComponent from "./views/Profile/Promotions/FreespinsComponent";
import GeneralComponent from "./views/Profile/Settings/GeneralComponent";
import SecurityComponent from "./views/Profile/Settings/SecurityComponent";
import {VueCookieNext} from "vue-cookie-next";

const LeaderboardAll = () => import(/* webpackChunkName: "Leaderboards" */'./views/LeaderboardAll.vue')
const Leaderboard = () => import(/* webpackChunkName: "leaderboard" */'./views/Leaderboard.vue')
const Static = () => import(/* webpackChunkName: "static" */'./views/Static.vue')

import {loadLocaleMessages, setI18nLanguage, SUPPORT_LOCALES, defaultLanguage} from "./plugins/i18nPlugin";

function removeQueryParams(to) {
    if (Object.keys(to.query).length)
        return { path: to.path, query: {}, hash: to.hash }
}

export function setupRouter(i18n, store) {

    const routes =
        [
            {path: '', component: MainComponent, name: 'home',  props: route => ({ query: route.query.q })},
            {path: 'providers', component: Providers, name: 'providers', props: route => ({ query: route.query.q })},
            {path: 'provider/:slug', component: Provider, props: true, name: 'provider'},
            {
                path: ':provider/:slug',
                component: Play,
                meta: {
                    layout: "play-layout",
                    preloader: true
                },
                props: true,
                name: 'play',
                alias: ':locale?/:provider/:slug'
            },
            {path: ':static', component: Static, props: true, name: 'static', alias: ':locale?/:static'},
            {path: 'leaderboard', component: LeaderboardAll, props: true, name: 'leaderboard', meta: {
                    layout: "leaderboard-layout",
                }},
            {path: 'leaderboard/:id', component: Leaderboard, props: true, name: 'leaderboard.id', meta: {
                    layout: "leaderboard-layout",
                }},
            {path: 'registration-:slug', component: MainComponent, props: true, name: 'landing'},
            {path: 'bonuses', component: Bonuses, name: 'bonuses', props: true},
            {path: 'tournaments', component: Tournaments, name: 'tournaments'},
            {path: 'password/reset/:token', component: MainComponent, name: 'forgot', props: true},
            {
                path: 'maintenance', component: Maintenance, meta: {
                    layout: "error-layout"
                }
            },
            {
                path: 'profile',
                component: Profile,
                meta: {
                    layout: "profile-layout",
                    requiresLogin: true
                },
                props: true,
                name: 'profile',
                children: [
                    {
                        path: "promotion",
                        component: Promotions,
                        props: true,
                        name: 'promotions',
                        redirect: {name: 'promo-bonuses'},
                        children: [
                            {
                                path: "bonuses",
                                component: BonusesComponent,
                                props: true,
                                name: 'promo-bonuses',
                            },
                            {
                                path: "freespins",
                                component: FreespinsComponent,
                                props: true,
                                name: 'promo-freespins',
                            }
                        ],
                    },
                    {
                        path: "settings",
                        component: YourProfile,
                        props: true,
                        name: 'settings',
                        redirect: {name: 'setting-general'},
                        children: [
                            {
                                path: "general",
                                component: GeneralComponent,
                                props: true,
                                name: 'setting-general',
                            },
                            {
                                path: "security",
                                component: SecurityComponent,
                                props: true,
                                name: 'setting-security',
                            }
                        ],
                    },
                    {
                        path: "personal",
                        component: Personal,
                        name: 'personal',
                        redirect: {name: 'wallet'},
                        children: [
                            {
                                path: "wallet",
                                component: BalanceComponent,
                                props: true,
                                name: 'wallet',
                            },
                            {
                                path: "deposit",
                                component: DepositComponent,
                                props: true,
                                name: 'deposit',
                                alias: '/:locale/deposit',
                            },
                            {
                                path: "withdrawal",
                                component: WithdrawalComponent,
                                props: true,
                                name: 'withdrawal',
                            },
                            {
                                path: "transaction-history",
                                component: TransactionHistoryComponent,
                                props: true,
                                name: 'transaction'
                            },
                        ],
                    },
                    {
                        path: "game-history",
                        component: GameHistory,
                        props: true,
                        name: 'game-history'
                    },
                    {
                        path: "support",
                        component: Support,
                        props: true,
                        name: 'profile-support'
                    },
                ]
            },
            {
                path: 'verify/:token',
                component: Verify,
                props: true,
                name: 'verify'
            },
        ]


    // Creates regex (en|fr)
    function getLocaleRegex() {
        let reg = ''
        SUPPORT_LOCALES.forEach((locale, index) => {
            reg = `${reg}${locale}${index !== SUPPORT_LOCALES.length - 1 ? '|' : ''}`
        })
        return `(${reg})`
    }

    // Adding aliases to routes
    function addAliasesToRoutes(routes, lang, child) {

        // Iterate over each route
        routes.forEach(function (route) {

            // Translate the path
            let alias = translatePath(route.path, lang);

            // Add language prefix to alias (only if route is at the top level)
            if (!child) {
                alias = '/' + lang + (alias.charAt(0) != '/' ? '/' : '') + alias;
            }

            // Make sure alias array exists & add any pre-existing value to it
            if (route.alias) {
                if (!Array.isArray(route.alias)) {
                    route.alias = [route.alias];
                }
            } else {
                route.alias = [];
            }

            // Push alias into alias array
            if (route.path != alias && route.alias.indexOf(alias) == -1) {
                route.alias.push(alias);
            }

            // If the route has children, iterate over those too
            if (route.children) {
                addAliasesToRoutes(route.children, lang, true);
            }

        });
    }

    // Path translation
    function translatePath(path, langTo, langFrom, matchedPath) {

        // Split the path into chunks
        let pathChunks = path.split('/');

        // If the path is in some language already
        if (langFrom) {

            // Split the matched path into chunks
            let matchedPathChunks = matchedPath.split('/');

            // Translate the path back to original path names
            for (let i = 0; i < pathChunks.length; i++) {
                let pathChunk = pathChunks[i];

                // If the original path chunk is a variable, do not translate it
                if (matchedPathChunks[i].charAt(0) == ':') {
                    continue;
                }

                // If there is an alias, use it, otherwise use given path
                pathChunks[i] = pathChunk;
            }
        }

        // Translate all the non-variable chunks of the path
        for (let i = 0; i < pathChunks.length; i++) {
            let pathChunk = pathChunks[i];

            // If the path chunk is a variable, do not translate it
            if (pathChunk.charAt(0) == ':') {
                continue;
            }

            // If there is an alias, use it, otherwise use given path
            pathChunks[i] = pathChunk;
        }

        // Join path chunks and return
        return pathChunks.join('/');
    }

    for (let lang of SUPPORT_LOCALES) {
        if (lang !== defaultLanguage) {
            addAliasesToRoutes(routes, lang);
        }
    }

    const router = createRouter({
        history: createWebHistory(),
        routes: [{
            path: `/:locale${getLocaleRegex()}?`,
            component: {
                template: '<router-view></router-view>'
            },
            children: routes,
        }],
        scrollBehavior(to, from, savedPosition) {
            if (savedPosition) {
                return savedPosition
            } else {
                return {top: 0}
            }
        }
    })

    // Retrieving preferred language from browser
    function getPrefferedLanguage() {

        // Extraction of language shortcut from language string
        function extractLanguage(s) {
            return s.split('-')[0].toLowerCase();
        }

        // Use navigator.languages if available
        if (navigator.languages && navigator.languages.length) {
            return extractLanguage(navigator.languages[0] || '');
        }

        // Otherwise use whatever is available
        return extractLanguage(navigator.language || navigator.browserLanguage || navigator.userLanguage || '');
    }



    router.beforeEach(async (to, from, next) => {
        let lang = to.path.split('/')[1];

        if (!SUPPORT_LOCALES.includes(lang)) {
            // Set the language to saved one if available
            const savedLang = store.state.lang.locale;
            if (savedLang && SUPPORT_LOCALES.includes(savedLang)) {
                lang = savedLang;
            } else {

                // Set the language to preferred one if available
                const preferredLang = getPrefferedLanguage();
                if (preferredLang && SUPPORT_LOCALES.includes(preferredLang)) {
                    lang = preferredLang;
                }

                // Otherwise set default language
                else {
                    lang = defaultLanguage;
                }
            }

            // If the language isn't default one, translate path and redirect to it
            if (lang != defaultLanguage) {

                // Translate path
                let translatedPath = translatePath(to.path, lang);

                // Add language prefix to the path
                translatedPath = '/' + lang + (translatedPath.charAt(0) != '/' ? '/' : '') + translatedPath;

                return next({path: translatedPath, query: to.query, hash: to.hash});
            }
        }

        if(store.state.lang.locale !== lang){
            store.state.lang.locale = lang
        }

        // load locale messages
        if (!i18n.global.availableLocales.includes(lang)) {
            await loadLocaleMessages(i18n, lang)
        }

        // set i18n language
        setI18nLanguage(i18n, lang === defaultLanguage ? '' : lang)

        if (to.matched.some(record => record.meta.preloader)) {
            store.commit('preloader/open_loader')
        }

        if (to.matched.some(record => record.meta.layout !== "play-layout")) {
            if (store.state.user.device === 2) {
                if (VueCookieNext.isCookieAvailable('inside_call')) {
                    VueCookieNext.removeCookie('inside_call');
                }
            }
        }

        if (store.state.user.status.loggedIn) {
            to.params.currency_id = store.getters['user/wallets/primary_wallet'].currency_id;
        }

        if (to.matched.some(record => record.meta.requiresLogin) && !store.state.user.status.loggedIn) {
            next('/');
        }

        if (to.query.lucky_spin) {
            state.commit('lucky/OPEN')
        }

        if (to.query.registration) {
            store.commit('open_pop', 1)
        }

        if (to.query.aid) {
            delete to.query.aid
            next({name: to.name})
        }

        next();
    })


    return router;
}
