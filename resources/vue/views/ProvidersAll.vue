<template>
    <main class="main">
        <bubble-top-component></bubble-top-component>
        <header class="header container">
            <div class="header__caption flex_center"><h1 class="header__title"> {{ post?.headline }} </h1></div>
        </header>        <!-- GAMES GRID -->
        <div class="container ">
            <div class="games-grid">
                <router-link v-for="provider in providers" :key="provider.id" class="games-grid__item provider-card"
                             :to="{name: 'provider', params: {slug: provider.code, locale: $i18n.locale}}">
                    <picture>
                        <source :srcset="`${$cdn}/assets/img/provider/provider_${provider.code}.webp`" type="image/webp">
                        <img class="provider-card__img" :src="`${$cdn}/assets/img/provider/provider_${provider.code}.webp`"
                             alt="img"></picture>
                </router-link>
            </div>
        </div>
        <div v-html="post?.description" class="text-block text-block_mb container"></div>
        <pre>
      {{$route}}
      </pre>
    </main>
</template>

<script>
import axios from "axios";
import {wallets} from "../store/wallets.module";
import {defaultLanguage, SUPPORT_LOCALES} from "../plugins/i18nPlugin";

export default {
    name: "ProvidersAll",
    data() {
        return {
            post: {},
            provider_data: [],
            count_load_more: 16
        }
    },
    async beforeRouteEnter(to, from, next) {
        let locale = to.path.split('/')[1];
        if (!SUPPORT_LOCALES.includes(locale)) {
            locale = defaultLanguage
        }

      const current_wallet = wallets.state.wallets.find(el => el.primary)
      const params = {}
      if (current_wallet) {
        params.currency_id = current_wallet.currency_id
      }

       const {data: statics} = await axios.post('/a/static', {slug: 'providers', locale})
       const {data: provider} = await axios.post('/api/provider', params)
       next(vm => vm.setData(null, statics.post, provider))
    },
    async beforeRouteUpdate (to, from, next) {
        const {data: statics} = await axios.post('/a/static', {slug: 'providers', locale: to.params.locale})
        this.post = statics.post
        next()
    },
    watch: {
      primaryWallet: {
        async handler(val, oldVal) {
          if (val.id !== oldVal.id) {
            let params = {}
            if(val.currency_id){
              params.currency_id = val.currency_id
            }

            const {data} = await axios.post('/api/provider', params)
            this.setData(null, null, data)
          }
        },
        deep: true
      },
    },
    computed: {
        providers() {
            return this.provider_data || []
        },
        primaryWallet() {
          return this.$store.getters['user/wallets/primary_wallet'] || false
        }
    },
    methods: {
        setData(err, post, provider) {
            if (err) {
                this.error = err.toString()
            } else {
              if(post){
                this.post = post
              }
              if(provider){
                this.provider_data = provider
              }
            }
        }
    },
}
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

    .text-block{
        margin-top: 60px;
    }
</style>
