<template>
    <div @click="onToggleMenu" v-if="profileBurger <= 752"
         :class="['profile-aside__burger aside-toggler col-auto _dynamic_adapt_768', openMenu ? 'aside-toggler__toggle' : '']"
         data-da="main, 0 , 768"
         data-da-index="0"><span></span> <span class="aside-toggler__middle"></span> <span></span></div>
    <aside :class="['profile-aside', openMenu ? 'aside-open' : '']">
        <div class="profile-aside__inner">
            <div class="profile-aside__header d-flex" href="#">
                <div @click="onToggleMenu" v-if="asideBurger >= 768 && asideBurger < 1181"
                     :class="['profile-aside__burger aside-toggler col-auto', openMenu ? 'aside-toggler__toggle' : '']"
                     data-da="main, 0 , 768"><span></span> <span
                    class="aside-toggler__middle"></span> <span></span></div>
                <router-link to="/" class="profile-aside__logo">
                    <picture>
                        <source :srcset="`${$cdn}/assets/img/navbar/logo.svg`" type="image/webp">
                        <img class="img_fluid" :src="`${$cdn}/assets/img/navbar/logo.svg`" alt="logo" width="285"
                             height="89"/>
                    </picture>
                </router-link>
            </div>
            <div class="profile-aside__block profile-aside__user" href="#">
                <a class="link-aside" link="profile" href="javascript:;">
                    <svg class="user-nav__icon" xmlns="http://www.w3.org/2000/svg" height="24" width="24"
                         viewBox="0 0 24 24">
                        <path
                            d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path
                            d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="link-aside__text">{{ profile.email }}</span>
                </a>
            </div>
            <div class="profile-aside__block profile-aside__balance balance-aside">
                <div class="balance-container">
                    <div class="balance-aside__row">
                        <div class="balance-aside__item">
                            <div class="balance-aside__text">{{ $t('profile_total_balance') }}</div>
                            <div class="balance-aside__title balance-aside__title_lg">
                                <animated-balance :fix="2" :value="current_balance" left="USDT"></animated-balance>
                            </div>
                        </div>
                    </div>
                    <div class="balance-aside__row">
                        <div class="balance-aside__group">
                            <div class="balance-aside__item">
                                <div class="balance-aside__text">{{ $t('profile_real_balance') }}</div>
                                <div class="balance-aside__title">
                                    <animated-balance :fix="2" :value="real_balance" left="USDT"></animated-balance>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="balance-aside__row">
                        <div class="balance-aside__group">
                            <div class="balance-aside__item">
                                <div class="balance-aside__text">{{ $t('profile_bonus_balance') }}</div>
                                <div class="balance-aside__title">
                                    <animated-balance :fix="2" :value="bonus_balance" left="USDT"></animated-balance>
                                </div>
                            </div>
                        </div>
                    </div>
                    <router-link class="btn btn_main btn_lg btn-block" :to="{name: 'deposit', params: {locale}}">{{$t('profile_deposit_btn')}}</router-link>
                </div>
            </div>
            <nav class="profile-aside__block profile-aside__menu">
                <ul class="menu-aside">
                    <li>
                        <router-link @click="openMenu = false" :to="{name: 'personal', params: {locale}}" active-class="link-aside_active"
                                     exact-active-class="link-aside_active"
                                     :class="['link-aside', {'link-aside_active': ($route.path.includes('/personal') || $route.name === 'deposit')}]">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_3.svg`" type="image/webp">
                                <img class="link-aside__icon" :src="`${$cdn}/assets/img/profile-aside/link_aside_3.svg`"
                                     alt="img">
                            </picture>
                            <span class="link-aside__text">{{ $t('profile_nav_personal') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link @click="openMenu = false" :to="{name: 'promotions', params: {locale}}" class="link-aside"
                                     exact-active-class="link-aside_active"
                                     :class="['link-aside', {'link-aside_active': $route.path.includes('/promotion') && $route.name === 'promo-bonuses'}]"
                                     active-class="link-aside_active">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_1.svg`" type="image/webp">
                                <img class="link-aside__icon" :src="`${$cdn}/assets/img/profile-aside/link_aside_1.svg`"
                                     alt="img">
                            </picture>
                            <span class="link-aside__text">{{ $t('profile_nav_bonus') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link @click="openMenu = false" :to="{name: 'promo-freespins', params: {locale}}" class="link-aside"
                                     exact-active-class="link-aside_active"
                                     :class="['link-aside', {'link-aside_active': $route.path.includes('/promotion') && $route.name === 'promo-freespins'}]"
                                     active-class="link-aside_active">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_11.svg`"
                                        type="image/webp">
                                <img class="link-aside__icon"
                                     :src="`${$cdn}/assets/img/profile-aside/link_aside_11.svg`"
                                     alt="img">
                            </picture>
                            <span class="link-aside__text">{{ $t('profile_nav_freespins') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link @click="openMenu = false" :to="{name: 'settings', params: {locale}}" class="link-aside"
                                     active-class="link-aside_active"
                                     exact-active-class="link-aside_active"
                                     :class="['link-aside', {'link-aside_active': $route.path.includes('/settings')}]">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_2.svg`" type="image/webp">
                                <img class="link-aside__icon" :src="`${$cdn}/assets/img/profile-aside/link_aside_2.svg`"
                                     alt="img">
                            </picture>
                            <span class="link-aside__text">{{ $t('profile_nav_profile') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link @click="openMenu = false" :to="{name: 'game-history', params: {locale}}"
                                     active-class="link-aside_active"
                                     class="link-aside">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_4.svg`" type="image/webp">
                                <img class="link-aside__icon" :src="`${$cdn}/assets/img/profile-aside/link_aside_4.svg`"
                                     alt="img">
                            </picture>
                            <span class="link-aside__text">{{ $t('profile_nav_games') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <a @click="logout" class="link-aside">
                            <picture>
                                <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_7.svg`" type="image/webp">
                                <img class="link-aside__icon" :src="`${$cdn}/assets/img/profile-aside/link_aside_7.svg`"
                                     alt="img">
                            </picture>
                            <span class="link-aside__text">{{ $t('profile_nav_log_out') }}</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="profile-footer">
                <div class="profile-aside__block profile-aside__btn">
                    <router-link class="btn btn-block btn__transparent" to="/">

                        <picture>
                            <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_9.svg`" type="image/webp">
                            <img class="link-aside__icon lobby"
                                 :src="`${$cdn}/assets/img/profile-aside/link_aside_9.svg`"
                                 alt="img">
                        </picture>
                        <span class="link-aside__text">{{ $t('profile_nav_lobby') }}</span>
                    </router-link>
                </div>
                <div class="profile-aside__mobile">
                    <router-link :to="{name: 'home', params: {locale}}" class="link-aside"
                                 exact-active-class="link-aside_active"
                                 active-class="link-aside_active">
                        <picture>
                            <source :srcset="`${$cdn}/assets/img/profile-aside/link_aside_9.svg`" type="image/webp">
                            <img class="link-aside__icon" :src="`${$cdn}/assets/img/profile-aside/link_aside_9.svg`"
                                 alt="img">
                        </picture>
                        <span class="link-aside__text">{{ $t('profile_nav_lobby') }}</span>
                    </router-link>
                </div>
            </div>
        </div>
    </aside>
    <main class="profile-main">
        <router-view></router-view>
    </main>
</template>

<script setup>
import {ref, computed, inject, onMounted, onUnmounted} from "vue"
import LanguageComponent from "../components/lang/LanguageComponent";
import AnimatedBalance from "../components/animate/AnimatedBalance";
import {useStore} from "vuex"
import {useRouter} from "vue-router"
import {useI18n} from "vue-i18n"

const store = useStore();
const router = useRouter();
const {locale} = useI18n();

const asideBurger = ref(0)
const profileBurger = ref(0)
const openMenu = ref(false)
const socket = inject('socket')

const loggedIn = computed(() => store.state.user.status.loggedIn)
const profile = computed(() => store.state.user.user || [])
const current_balance = computed(() => store.getters['user/wallets/current_balance'])
const bonus_balance = computed(() => store.getters['user/wallets/bonus_balance'])
const real_balance = computed(() => store.getters['user/wallets/real_balance'])

onMounted(() => {
    window.addEventListener('resize', resize)
})

onUnmounted(() => {
    window.removeEventListener('resize', resize)
})

function logout() {
    socket.emit('logout', {success: 1})
    store.dispatch('user/logout').then(
        async () => {
            await router.push('/')
        }
    )
}
function onToggleMenu() {
    openMenu.value = !openMenu.value
}

function resize() {
    asideBurger.value = document.documentElement.clientWidth;
    profileBurger.value = document.documentElement.clientWidth;
}

resize()
</script>

<style>
.w-50 {
    width: 50%;
}

.lobby {
    margin-right: 9px;
}
</style>
