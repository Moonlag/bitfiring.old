<template>
    <transition name="fade">
        <loader-screen v-if="isPrelaoder"></loader-screen>
    </transition>
    <component :is="layout">
        <Suspense>
            <router-view></router-view>
        </Suspense>
    </component>
    <!--  <cookie-component :cookie="common_data.i_saw_cookie"></cookie-component>-->
    <Suspense v-if="route.name === 'home' && onChat">
        <BitChat :show="onChat"/>
    </Suspense>

    <BitChatButton :show="onChat" @onShow="onChat = !onChat"/>

    <div v-if="emulated" class="card shadow p-3 emulated"
         style="position: fixed; bottom: 0; right: 5%; z-index: 999">
        <h5 class="title"> {{ user.email }} </h5>
        <a class="btn btn_main" :href="emulated"> Return to admin session</a>
    </div>
</template>

<script setup>
import {ref, computed, inject, watch, onBeforeUnmount, defineAsyncComponent} from "vue";
import {useStore} from "vuex";
import {useRouter, useRoute} from "vue-router";

import CookieComponent from "./components/pop/CookieComponent.vue";
import loaderScreen from "./components/loaderScreen.vue";
import BitChatButton from "./components/bitchat/components/ChatButton.vue";

const BitChat = defineAsyncComponent(() =>
    import('./components/bitchat/App.vue')
)

import {useI18n} from "vue-i18n"
import {createToaster} from "@meforma/vue-toaster";

const store = useStore()
const router = useRouter()
const route = useRoute()
const socket = inject('socket')
const Toaster = createToaster({ /* options */});
const onChat = ref(false)

const currentLocale = computed({
    get() {
        return store.state.lang.locale;
    },
    set(value) {
        store.commit("lang/SET_LOCALE", value);
    }
});

const {emulated, translate} = window?.common_data

const layout = computed(() => route.meta.layout || "default-layout")

const loggedIn = computed(() => store.state.user.status.loggedIn || false)

const user = computed(() => store.state.user.user)

const isPrelaoder = computed(() => store.state.preloader.loader || false)

const primaryWallet = computed(() => store.getters['user/wallets/primary_wallet'] || false)

store.commit('games/SET_DEFAULT')
store.dispatch('games/onLoadMore')

watch(
    // getter
    () => loggedIn.value,
    // callback
    onLogged,
    // watch Options
    {
        lazy: false // immediate: true
    }
)

//
// watch(router.currentRoute, route => {
//     currentLocale.value = route.params.locale ? route.params.locale : 'en';
// })
//
// watch(currentLocale, (val, old) => {
//     if(old){
//         router.push({
//             name: router.currentRoute.value.name,
//             params: {locale: val === 'en' ? null : val}
//         })
//     }
// })

function onLogged(logged) {
    if (logged) {
        const user_id = user.value.id;
        socket.auth = {userID: user_id};
        socket.connect();
        socket.on(`channel wallets`, (msg) => {
            store.commit('user/updateWallets', msg);
        })
        socket.on(`logout`, (msg) => {
            store.dispatch('user/logout').then(
                async () => {
                    await router.push('/')
                }
            )
        })
        socket.on(`alert`, (msg) => {
            if (msg.op === 'deposit') {
                const {amount, currency} = msg;
                store.dispatch('user/wallets/getWallets')
                Toaster.success(`Balance updated!: ${amount} ${currency}`, {
                    position: "top-right",
                    duration: false
                });
            }
        })

        socket.on('bet', (wallet) => {
            store.commit('user/wallets/UPDATE_WALLET', wallet)
        })

        socket.on('locked bonus', (bonuses) => {
            store.commit('user/wallets/new_bonuses', bonuses)
        })

        socket.on('channel swap', ({amount, total, currency_id, bonuses}) => {
            store.commit('open_swap', {
                amount,
                total,
                currency_id,
                bonuses
            });
        })

        socket.on('deposit reject', ({amount}) => {
            store.commit('open_reject', amount);
        })

        socket.on('update balance', () => {
            store.dispatch('user/wallets/getWallets')
            store.dispatch('user/wallets/getBonuses')
        })

        socket.on('swap balance', ({from, to}) => {
            store.commit('user/wallets/updateBalance', from)
            store.commit('user/wallets/updateBalance', to)
        })

        socket.on('update notification', () => {
            store.dispatch('notification/getNotification')
        })

        store.dispatch('notification/getNotification')
        store.dispatch('lucky/loadConfig')
        store.dispatch('data/transaction_history')
        store.dispatch('data/paymentSystem')
        store.dispatch('data/currency')
        store.dispatch('games/onLoadPlayed')
        // let {id, current_page: page, slug} = this.$store.state.games.played
        // this.$store.dispatch('games/onLoadMore', {data: {page: page + 1, id}, slug})
    } else {
        socket.off('channel wallets')
        socket.off('logout')
        socket.off('alert')
        socket.off('swap balance')
        socket.off('locked bonus')

        socket.disconnect()
        store.dispatch('lucky/loadConfig')
        store.commit('user/wallets/clear')
        store.commit('games/CLEAR_PLAYED')
    }
}

onBeforeUnmount(async () => {
    await store.dispatch('lucky/loadConfig')
    let login = loggedIn.value
    if (!login) {
        store.commit('games/SET_DEFAULT')
        await store.dispatch('games/onLoadMore')
    }
})
</script>

<style scoped>

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.emulated {
    background: linear-gradient(99.8deg, rgba(196, 47, 237, 0.5) 14.73%, rgba(99, 158, 255, 0.5) 88.93%);
    display: flex;
    padding: 15px;
    flex-direction: column;
}

.emulated h5 {
    color: white;
    margin-bottom: 10px;
    text-align: center;
}
</style>
