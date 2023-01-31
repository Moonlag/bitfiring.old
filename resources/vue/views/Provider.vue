<template>
    <main v-if="setProvide !== undefined" class="main provider">
        <bubble-top-component :img="`/assets/img/top_bg/background`"></bubble-top-component>
        <header class="header container">
            <div class="header__caption flex_center">
                <h1 class="header__title">
                    {{ setProvide.title }}
                </h1>
                <h2 class="header__subtitle" v-html="$t('provider_subtext')">

                </h2>
                <a v-if="!loggedIn" @click="$store.commit('open_pop', 1)" class="btn btn_main btn_xl"
                   href="javascript:;">{{ $t('provider_create_account') }}</a>
                <router-link v-else :to="{name: 'deposit', params: {locale}}" class="btn btn_main btn_xl">{{ $t('provider_deposit') }}
                </router-link>
            </div>
        </header>

        <div class="container" id="game_feed">
            <LoadingComponent v-if="loading">

            </LoadingComponent>
            <GamesComponent
                :games="games"
                :total="total"
                @onLoad="load_more"
                :loading="disabled"
                :message="message"
                :locale="locale"
            ></GamesComponent>
        </div>



    </main>

    <not-found v-else></not-found>
</template>

<script setup>
import {ref, computed, watch, onUnmounted} from "vue";
import NotFound from './404.vue'
import BubbleTopComponent from "../components/buble/BubbleTopComponent.vue"
import GamesComponent from '../components/main/GamesComponent.vue'
import LoadingComponent from '../components/LoadingComponent.vue'

import {useStore} from "vuex";
import {useRouter, parseQuery} from "vue-router";
import {useI18n} from "vue-i18n";
import axios from "axios";

const store = useStore();
const router = useRouter();
const route = useRoute();
const {locale} = useI18n();

const total = ref(28)
const current_page = ref(1)
const games = ref([])
const disabled = ref(false)
const message = ref('')
const loading = ref(true)

const props = defineProps({
    slug: {
        type: String,
        required: true,
    }
})

const loggedIn = computed(() => {
    return store.state.user.status.loggedIn;
})

const setProvide = computed(() => {
    return store.state.data.providers.find(el => el.code === props.slug)
})

const primaryWallet = computed(() => {
    return store.getters['user/wallets/primary_wallet'] || false
})

const current_wallet = store.getters['user/wallets/primary_wallet']

watch(() => primaryWallet,
    async (val, oldVal) => {
        if (val.value.id !== oldVal.value.id) {
            await setData()
        }
    }, {
        deep: true
    })

function set_head(v_title) {
    let head = document.querySelector('head')
    let title = head.querySelector('title')
    title.textContent = v_title
}

async function setData() {
    loading.value = true
    const params = {provider: props.slug, page: 1}
    if (primaryWallet.value) {
        params.currency_id = primaryWallet.value.currency_id
    }
    const result = await axios.post('/api/games', params)
    const {data, meta} = result.data
    setTimeout(() => {
        loading.value = false
        games.value = data
        current_page.value = meta.current_page
        total.value = meta.total
        if (!message.value) {
            message.value = 'No games found'
        }
    }, 250)

    set_head(`Play ${setProvide.value.title} Casino Game Online! For Real Money or Free`)
}

async function load_more() {
    if (disabled.value) {
        return
    }
    disabled.value = true
    const result = await axios.post('/api/games', {
        provider: props.slug,
        page: ++current_page.value,
        currency_id: primaryWallet.value ? primaryWallet.value?.currency_id : null
    })
    const {data, meta} = result.data
    games.value.push(...data)
    current_page.value = meta.current_page
    disabled.value = false
}

onUnmounted(() => {
    set_head(`Bitfiring – Top Crypto Casino With Big Jackpots &amp; Modern Slots`)
})
setData()
</script>

<style scoped lang="scss">

header.header {
    padding: 88px 16px 32px 16px;
}

#game_feed{
    position: relative;
}
</style>
