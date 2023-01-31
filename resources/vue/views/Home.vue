<template>
  <main class="main">
    <bubble-top-component :img="`/assets/img/top_bg/${background}`"></bubble-top-component>
    <!-- HEADER -->
    <header class="header header__home container">

      <div class="header__caption">
        <div class="header_home_container" v-html="home.description"></div>
        <a v-if="!loggedIn" @click="$store.commit('open_pop', 1)" class="btn btn_main btn_xl"
           href="javascript:;">{{$t('main_create_account')}}</a>
        <router-link v-else :to="{name: 'deposit'}" class="btn btn_main btn_xl">{{$t('main_deposit')}}</router-link>
      </div>
    </header>

    <popular-component @onPopular="onTabs" @onRedirect="onGo" :slides="slides"/>

    <categories-component
        :isActive="isActive"
        :search="search"
        @onActive="onTabs"
        @search_game="onSearch">
    </categories-component>

    <div class="container" id="game_feed">
      <div v-if="isLoader" class="load">
        <svg version="1.1" id="L4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
             x="0px" y="0px"
             viewBox="0 0 50 75" enable-background="new 0 0 0 0" xml:space="preserve">
  <circle fill="#fff" stroke="none" cx="6" cy="50" r="6">
    <animate
        attributeName="opacity"
        dur="1s"
        values="0;1;0"
        repeatCount="indefinite"
        begin="0.1"/>
  </circle>
          <circle fill="#fff" stroke="none" cx="26" cy="50" r="6">
    <animate
        attributeName="opacity"
        dur="1.5s"
        values="0;1;0"
        repeatCount="indefinite"
        begin="0.2"/>
  </circle>
          <circle fill="#fff" stroke="none" cx="46" cy="50" r="6">
    <animate
        attributeName="opacity"
        dur="1.5s"
        values="0;1;0"
        repeatCount="indefinite"
        begin="0.3"/>
  </circle>
</svg>
      </div>
      <transition-group @enter="onEnter" @before-enter="onBeforeEnter" @leave="onLeave" mode="out-in">

        <GamesComponent
            :games.camel="main.games"
            :total="main.total"
            :loading="loading"
            @onLoad="onLoad(main)"
            :locale="locale"
            v-if="'main' === activeTab">
          <template #winner>
            <winners-feed-component
                v-if="winner_tab"
                @goGame="goGame"/>
          </template>
        </GamesComponent>
        <GamesComponent
            :games.camel="slots.games"
            :total="slots.total"
            :loading="loading"
            @onLoad="onLoad(slots)"
            :locale="locale"
                        v-if="'slots' === activeTab">
        </GamesComponent>
        <GamesComponent :games="roulette.games" :total="roulette.total" :loading="loading"
                        @onLoad="onLoad(roulette)"
                        :locale="locale"
                        v-if="'roulette' === activeTab">
        </GamesComponent>
        <GamesComponent :games="search.games" :total="search.total" :loading="loading" @onLoad="loadSearch()"
                        :locale="locale"
                        v-if="'search' === activeTab">
        </GamesComponent>
        <GamesComponent :games="blackjack.games" :total="blackjack.total" :loading="loading"
                        @onLoad="onLoad(blackjack)"
                        :locale="locale"
                        v-if="'blackjack' === activeTab">
        </GamesComponent>
        <GamesComponent :games="baccarat.games" :total="baccarat.total"
                        :loading="loading"
                        @onLoad="onLoad(baccarat)"
                        :locale="locale"
                        v-if="'baccarat' === activeTab">
        </GamesComponent>
        <GamesComponent :games="jackpot.games" :total="jackpot.total"
                        :loading="loading"
                        @onLoad="onLoad(jackpot)"
                        :locale="locale"
                        v-if="'jackpot' === activeTab">
        </GamesComponent>
        <GamesComponent :games="played.games" :total="played.total"
                        :loading="loading"
                        :message="!loggedIn ? 'Please, sign in to see your recent games' : 'No games found'"
                        @onLoad="onLoad(played)"
                        :locale="locale"
                        v-if="'played' === activeTab">
        </GamesComponent>
      </transition-group>
      <ShowMore :html="$t('block.home_seo_text.text')"></ShowMore>
    </div>
      <pre>
      {{route}} {{game}}
      </pre>
          <teleport to="body" v-if="route.params?.game">
              <fancybox-container>
                <div @click.stop class="popup fancybox-content" style=" display: inline-block;">
                  {{route}}
                </div>
              </fancybox-container>
          </teleport>
  </main>
</template>

<script setup>
import {ref, reactive, computed, onMounted} from "vue"
import PopularComponent from "../components/main/PopularComponent.vue";
import CategoriesComponent from "../components/main/CategoriesComponent.vue";
import BubbleTopComponent from "../components/buble/BubbleTopComponent.vue";
import WinnersFeedComponent from "../components/main/WinnersFeedComponent.vue";
import ShowMore from "../components/block/ShowMore.vue";
import GamesComponent from "../components/main/GamesComponent.vue";
import {gsap, Back, Circ} from "gsap";

import {useI18n} from "vue-i18n";
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const props = defineProps({
    game: {
        required: false,
        type: Object
    }
})

const store = useStore()
const route = useRoute()
const router = useRouter()

const {locale} = useI18n({ useScope: 'global' })

const background = 'background'

if (route.query.token) {
    store.commit('open_verify', {
        token: route.query.token,
        email: route.query.email
    })
}

const slot_search = reactive({active: false, model: ''})
const winner_tab = ref(true)
const disabled = ref(false)
const loading = ref(false)
let isSearch = null;
const isLoader = ref(false)

const currentLocale = computed({
    get() {
        return store.state.lang.locale;
    },
    set(value) {
        store.commit("lang/SET_LOCALE", value);
    }
});
const loggedIn = computed(() => store.state.user.status.loggedIn)
const home = computed(() => store.state.data.home || [])
const isActive = computed(() => store.state.games.active || false)
const main = computed(() => store.state.games.main || [])
const slots = computed(() => store.state.games.slots || [])
const roulette = computed(() => store.state.games.roulette || [])
const search = computed(() => store.state.games.search || [])
const blackjack = computed(() => store.state.games.blackjack || [])
const baccarat = computed(() => store.state.games.baccarat || [])
const jackpot = computed(() => store.state.games.jackpot || [])
const played = computed(() => store.state.games.played || [])
const activeTab = computed(() => {
    if (search.value?.model.length && search.value?.active) {
        return 'search'
    }
    return isActive.value
})

const slides = computed(() => store.state.data.slides || [])

async function onTabs(slug) {
    if (loading.value) {
        return
    }

    store.commit('games/REMOVE_SEARCH')
    store.commit('games/CLEAR_SEARCH')
    store.commit('games/ACTIVE_FILTER', slug)
    if (slug === 'played' && !loggedIn.value) {
        return
    }

    let current = store.state.games[slug]
    const {games, current_page} = current

    if (!games.length && !current_page) {
        await onLoad()
    }
}


function goGame(slug, provider_id, category) {
    if (loggedIn.value && category && category.length) {
        let wager = category.find(el => el.category_id === 38)
        if (wager) {
            store.commit('open_wager')
            return
        }
    }

    const provider = store.state.data.providers.find(el => el.id === provider_id);
    router.push({name: 'play', params: {provider: provider.code, slug: slug, locale: locale.value}})
}

async function onSearch() {
    isLoader.value = true
    loading.value = true
    store.commit('games/CLEAR_SEARCH')
    clearTimeout(isSearch)
    isSearch = setTimeout(async () => {
        if (store.state.games.search.model) {
            await loadSearch()
        }
        isLoader.value = false
        loading.value = false
    }, 350)
}

async function loadSearch() {
    // await this.onLoad()

    await store.dispatch('games/onLoadSearch')
}

async function onLoad() {
    if (!search.value.model.length) {
        isLoader.value = true
        loading.value = true
    }
    if (isActive.value === 'played') {
        await store.dispatch('games/onLoadPlayed')
        loading.value = false
        isLoader.value = false
        return
    }
    await store.dispatch('games/onLoadMore')
    setTimeout(() => {
        loading.value = false
        isLoader.value = false
    }, 350)
}

function onGo(url){
    router.push({name: url, params: {locale: locale === 'en' ? '' : locale}});
}

function onBeforeEnter(el, done) {
    gsap.set(el, {alpha: 0, height: '100%', y: 20, onComplete: done})
}

function onEnter(el, done) {
    gsap.to(el, {
        duration: 0.3,
        alpha: 1,
        y: 0,
        ease: "power3.inOut",
        onComplete: done
    })
}

function onLeave(el, done) {
    gsap.to(el, {
        duration: 0.3,
        opacity: 0,
        height: '0',
        y: -20,
        onComplete: done,
    })
}

function set_head(v_title, m_title, m_description){
    let head = document.querySelector('head')
    let title = head.querySelector('title')
    let meta_title = head.querySelector('meta[name="title"]')
    let meta_description = head.querySelector('meta[name="description"]');
    title.textContent = v_title
    meta_title.textContent = m_title;
    meta_description.setAttribute("content", m_description);
}

onMounted(() => {
    if (route.params.wager) {
        store.commit('open_wager')
    }
})
</script>

<style scoped>
pre {
    position: fixed;
    padding: 50px 10px;
    top: 0;
    left: 0;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    display: block;
    font-size: 87.5%;
    color: white;
    z-index: 9999;
}

pre {
    margin-top: 0;
    margin-bottom: 1rem;
    overflow: auto;
    -ms-overflow-style: scrollbar;
    max-width: 100vw;
}

#game_feed {
  position: relative;
  min-height: 164px;
  transition: height 0.1s ease-in;
}

.load {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  height: 148px;
  display: flex;
  justify-content: center;
}

.load svg {
  width: 100px;
  height: 100px;
  display: inline-block;
}

.header_home_container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.text-block h1
{
  color: #ff8700;
  font-size:32px;
  line-height:36px;
}

.text-block h1, .text-block h2, .text-block h3 {
  color: #ff8700;
}
</style>
