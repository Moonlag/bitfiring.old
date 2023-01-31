<template>
    <loader-screen v-if="deviceType === 2"></loader-screen>
    <main v-if="!error_game" class="play main top-bg" :class="{'slot__full': full}">
        <div class="slot__modal">
            <fullscreen v-model:fullscreen="fullscreen" :teleport="false" :page-only="false">
                <bubble-top-component :img="`/assets/img/top_bg/${background}`"
                                      style="margin-top: -55px; height: calc(100vh + 55px)"></bubble-top-component>
                <div class="slot__main ibg">
                    <header-play v-if="!full" @fullscreen="toggleApi"></header-play>
                    <iframe @load="onLoad" :src="src" frameborder="0" id="game" name="game_frame" allow="autoplay"
                            :style="style"
                    ></iframe>
                </div>
            </fullscreen>
        </div>
    </main>
    <div v-else style="margin-top: 60px">
        <not-found></not-found>
    </div>
</template>

<script>
import axios from "axios";
import notFound from "./404"
import loaderScreen from "../components/loaderScreen";
import HeaderPlay from "../components/header/HeaderPlay";
import {component} from 'vue-fullscreen';

export default {
    name: "Play",
    props: ['provider', 'slug', 'currency_id'],
    emits: ['fullscreen'],
    ratios: {
        1: {x: 16, y: 9},
        2: {x: 4, y: 3},
    },
    resize: {x: 16, y: 9, h: 0},
    async beforeRouteEnter(to, from, next) {

        next(vm => vm.setData(null, {game: {name: 'old Name v2', provider: 'evo', indenter: 'old'}}))
    },
    async beforeRouteUpdate(to, from, next) {
        this.post = null
        await axios.post("/a/get_game", {
            provider: to.params.provider,
            slug: to.params.slug,
            currency_id: to.params.currency_id
        }).then(response => {
            this.setData(null, response.data.game)
            next()
        }).catch(error => {
            this.setData(true, null)
            next({path: '/404'});
        })
    },
    data() {
        return {
            error_game: false,
            src: '',
            style: {width: '100%', height: '100%', filter: 'none'},
            background: 'background',
            full: false,
            fullscreen: false,
            ratio: 1
        }
    },
    computed: {
        providers() {
            return this.$store.state.data.providers
        },
        deviceType() {
            return this.$store.state.user.device
        },
        loggedIn() {
            return this.$store.state.user.status.loggedIn;
        },
    },
    methods: {
        async setData(err, game) {
            this.$store.commit('open_gameReject', game)
            await this.$router.push({name: 'home'})
            return
            this.add_session_played(game);

            if (err) {
                this.error_game = true
            } else {
                if (game.hasOwnProperty('game_url')) {

                    this.$store.commit('preloader/CLEAR_TIMEOUT')
                    if (this.deviceType === 2) {
                        await this.redirectMobile(game.game_url);
                    }
                    // this.$store.commit('preloader/SET_TITLE', game.name)
                    this.set_head(`Play ${game.name} Casino Games Online! For Real Money or Free`)

                    this.src = game.game_url

                    this.ratio = this.providers.find(el => el.id === game.provider_id).ratio || 1;

                    this.style = {
                        'display': 'block',
                        'margin': 'auto'
                    }
                    this.resize_game()
                    window.addEventListener('resize', this.resize_game)
                } else {
                    this.error_game = true
                }
            }
        },
        add_session_played(game) {
            if (this.loggedIn) {
                this.$store.commit('games/ADD_LAST_PLAYED', game);
            }
        },
        resize_game() {
            const width = (document.documentElement.clientWidth - 80);
            const height = (document.documentElement.clientHeight - 55);
            const resize = this.$options.ratios[this.ratio];

            // if (width < 650 || height < 650) {
            //     this.style.width = 100 + "vw";
            //     this.style.height = 100 + "vh";
            //     this.full = true
            //     return
            // }

            this.full = false

            let h = width / (resize.x / resize.y)
            h = h > height ? height : h;

            let w = resize.x / resize.y * h

            this.style.width = w + "px";
            this.style.height = h + "px";
        },
        async onLoad(event) {
            if (this.src) {
                setTimeout(() => this.$store.commit('preloader/close_loader'), 1000)
                const iframe = document.querySelector('iframe');
                try {
                    let content_frame = iframe.contentWindow.document;
                    if (content_frame) {
                        let meta = content_frame.querySelector('meta[name="csrf-token"]');
                        if (meta.dataset.platform && Number(meta.dataset.platform) === 1001) {
                            this.$router.push({name: 'home'})
                        }
                    }

                } catch (e) {

                }
            }
        },
        async proxyGame(game) {
            this.$socket.on('start game', ({url, provider, game_id}) => {
                game.game_url = url
                this.setData(null, game)
                this.$socket.off('start game')
            })
        },
        toggleApi() {
            this.fullscreen = !this.fullscreen
        },
        redirectMobile(url) {
            return new Promise((resolve, reject) => {
                let inside_call = this.$cookie.getCookie('inside_call')

                if (!inside_call) {
                    this.$cookie.setCookie('inside_call', 1, {
                        expire: '3y',
                    });
                    return window.location.href = url
                }
                this.$cookie.removeCookie('inside_call');

                this.$router.push({name: 'home'})
            })
        },
        redirectWager() {
            this.$router.push({
                name: 'home', params: {
                    wager: 1
                }
            })
        },
        set_head(v_title) {
            let head = document.querySelector('head')
            let title = head.querySelector('title')
            title.textContent = v_title
        }
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.resize_game)
        this.$socket.off('start game')
        this.set_head(`Bitfiring – Top Crypto Casino With Big Jackpots &amp; Modern Slots`);
    },
    components: {
        notFound,
        loaderScreen,
        HeaderPlay,
        fullscreen: component,
    }
}
</script>

<style>
.play {
    background-image: none;
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: 1000;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
}

iframe {
    border: none;
    margin: 0;
    max-width: 100%;
    max-height: 100%;
}

.slot__full iframe {
    margin: 0;
    outline: none;
}
</style>
