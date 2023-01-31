<template>

    <div v-click-outside="closeNotification" :class="{'dropdown__active': notification.active}"
         class="navbar__notification notification-nav dropdown__toggle">
        <div class="notification__counter" @click="toggleNotification">
            <svg class="notification-nav__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 20" width="18"
                 height="20" id="bell">
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M8.833 3.75A4.588 4.588 0 004.25 8.333v2.324a6.83 6.83 0 01-2.425 5.225.207.207 0 00-.075.16.21.21 0 00.208.208h13.75a.21.21 0 00.209-.208.206.206 0 00-.074-.159 6.832 6.832 0 01-2.426-5.226V8.333A4.588 4.588 0 008.833 3.75zM8.208.625v1.908A5.842 5.842 0 003 8.333v2.324c0 1.649-.723 3.206-1.99 4.276a1.458 1.458 0 00.948 2.567h3.813A3.13 3.13 0 008.833 20a3.13 3.13 0 003.063-2.5h3.812a1.46 1.46 0 00.94-2.572 5.583 5.583 0 01-1.981-4.271V8.333a5.842 5.842 0 00-5.209-5.8V.625a.625.625 0 00-1.25 0zm.625 18.125c-.815 0-1.51-.522-1.768-1.25h3.536a1.878 1.878 0 01-1.768 1.25z"/>
            </svg>

            <div v-if="bonuses.length || freespins.length" class="notification-nav__counter">
                {{ bonuses.length + freespins.length }}
            </div>
        </div>

        <transition @enter="onEnter" @before-enter="onBeforeEnter" @leave="onLeave" mode="out-in">
            <div v-if="notification.active" class="dropdown__content d-block">
                <div v-for="bonus in freespins" class="notification mb-8">
                    <div class="notification_header">
                        <div class="notification__icon col-auto">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/notification/notification_1.svg`"
                                        type="image/webp">
                                <img :src="`${$cdn}/assets/img/notification/notification_1.svg`" alt="img"></picture>
                        </div>

                        <div class="notification__title">{{ bonus.title_frontend }} <br></div>
                    </div>

                    <div class="notification__text mb-12">
                        {{ bonus.mini_description }}
                    </div>

                    <button @click="onFreespin(bonus)" class="btn btn_light btn-block">LAUNCH FREESPINS</button>
                </div>

                <div v-for="bonus in bonuses" class="notification mb-8">
                    <div class="notification_header">
                        <div class="notification__icon col-auto">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/notification/notification_1.svg`"
                                        type="image/webp">
                                <img :src="`${$cdn}/assets/img/notification/notification_1.svg`" alt="img"></picture>
                        </div>

                        <div class="notification__title">{{ bonus.title_frontend }} <br></div>
                    </div>

                    <div class="notification__text mb-12">
                        {{ bonus.mini_description }} <span>{{ bonus.created_at }}</span>
                    </div>

                    <router-link :to="{name: 'deposit', params: {bonuses: id}}" class="btn btn_light btn-block">DEPOSIT</router-link>
                </div>
            </div>
        </transition>
    </div>

    <div v-if="wallets.length" class="navbar__wallet wallet-nav dropdown__active dropdown__toggle"
         :class="{'dropdown__active': wallet.active}" v-click-outside="openWallet">
        <a href="javascript:;" @click="toggleWallet">
            <div class="wallet-nav">
                <picture>
                    <source :srcset="`${$cdn}/assets/img/common/nav_wallet.svg`" type="image/webp">
                    <img :src="`${$cdn}/assets/img/common/nav_wallet.svg`" alt="img" class="wallet-nav__img"></picture>

                <div class="wallet-nav__title">
                    <animated-balance :fix="primaryWallet.code === 'USDT' ? 2 : 8"
                                      :value="primaryWallet.balance" :animated="false"></animated-balance>
                    {{ primaryWallet.code }}
                </div>
            </div>
        </a>
        <transition @enter="onEnter" @before-enter="onBeforeEnter" @leave="onLeave" mode="out-in">
            <div v-if="wallet.active" class="dropdown__content d-block">
                <div @click="active_primary(wallet.id)" v-for="wallet in wallets" class="wallet_dropdown"
                     :class="{'active': primaryWallet.id === wallet.id}">
                    <div class="wallet_dropdown_header">
                        <div class="wallet_dropdown__icon col-auto">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/coins/${wallet.code}.svg`" type="image/webp">
                                <img :src="`${$cdn}/assets/img/coins/${wallet.code}.svg`" alt="img"></picture>
                        </div>
                        <div class="wallet_dropdown__title">
                            <animated-balance :fix="wallet.code === 'USDT' ? 2 : 8"
                                              :value="wallet.balance" :animated="false"></animated-balance>
                        </div>
                    </div>
                </div>

            </div>
        </transition>
    </div>

    <div class="navbar__user user-nav" data-da="navbar__box, 1, 768">
        <router-link :to="{name: 'promotions', params: {locale}}" class="profile_link">
            <svg class="user-nav__icon" xmlns="http://www.w3.org/2000/svg" id="user-nav" viewBox="0 0 24 24" width="24"
                 height="24">
                <path
                    d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                    d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="user-nav__text">
                {{ $t('header_account') }}
            </div>
        </router-link>
    </div>


</template>

<script setup>
import {gsap, Back, Circ} from "gsap";
import AnimatedBalance from "../../animate/AnimatedBalance";
import axios from 'axios';
import {reactive, computed, watch, inject} from "vue";
import {useStore} from "vuex";
import {useRouter} from "vue-router";
import {useI18n} from "vue-i18n";

const store = useStore();
const router = useRouter();
const api = inject('api');
const {locale} = useI18n()

const notification = reactive({
    active: false
})

const wallet = reactive({
    active: false
})

const wallets = computed(() => store.getters['user/wallets/wallets'].filter(el => el.balance > 0) || [])
const primaryWallet = computed(() => wallets.value.find(el => el.primary === 1) || [])
const bonuses = computed(() => store.state.notification.bonuses || [])
const freespins = computed(() => store.state.notification.freespins || [])

watch(bonuses, () => {
    setTimeout(() => this.notification.active = true, 250)
});

watch(freespins, () => {
    setTimeout(() => this.notification.active = true, 250)
});

function active_primary(id) {
    if (primaryWallet.value.id !== id) {
        api.wallet.setPrimary({id: id}).then(
            response => {
                if (response.data.success) {
                    wallets.value.forEach(el => {
                        el.primary = Boolean(el.id === id);
                    })
                    store.commit('user/wallets/updatePrimaryWallet', id)
                }
            }
        )
    }
}

function toggleNotification(evt) {
    notification.active = !notification.active
}

function toggleWallet(evt) {
    wallet.active = !wallet.active
}

function setBonuses() {
    api.bonuses.user().then(
        response => {
            bonuses.value = response.data.bonuses
            freespins.value = response.data.freespin
        }
    )
}

function closeNotification() {
    notification.active = false
}

function onBeforeEnter(el, done) {
    gsap.set(el, {
        opacity: 0,
        y: 30,
        onComplete: done,
    })
}

function onEnter(el, done) {
    gsap.to(el, {
        duration: 0.7,
        opacity: 1,
        y: 0,
        overwrite: true,
        onComplete: done,
        ease: Back.easeInOut
    })
}

function onLeave(el, done) {
    gsap.to(el, {
        opacity: 0,
        y: 30,
        onComplete: done,
    })
}

async function onFreespin(freespin) {
    const wallets = store.getters['user/wallets/wallets'];

    if (!wallets.some(el => el.currency_id === freespin.currency_id)) {
        await api.wallet.new({currency_id: freespin.currency_id, primary: 1}).then(
            response => {
                if (response.data.success) {
                    store.commit('user/wallets/setWallets', response.data.wallets)
                }
            }
        )
    }

    const wallet = wallets.value.find(el => el.currency_id === freespin.currency_id)

    if (wallet && !wallet.primary) {
        await api.wallet.setPrimary({id: wallet.id}).then(
            response => {
                if (response.data.success) {
                    wallets.value.forEach(el => {
                        el.primary = Boolean(el.id === wallet.id);
                    })
                    store.commit('user/wallets/updatePrimaryWallet', wallet.id)
                }
            }
        )
    }
    const payload = {id: freespin.id}
    const {data} = await getFreespin(payload)
    router.push({name: 'play', params: {provider: data.provider, slug: data.uri}})
}

function getFreespin(payload) {
    return axios.post('/a/user/set_freespin', payload)
}
</script>

<style scoped>

</style>
