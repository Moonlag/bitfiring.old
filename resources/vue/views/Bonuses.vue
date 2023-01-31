<template>
    <main className="main">
        <bubble-top-component></bubble-top-component>
        <header className="header container">
            <div className="header__caption flex_center">
                <h1 className="header__title">{{ post?.headline }}</h1>
            </div>
        </header>

        <div ref="statics" v-html="post?.description" className="text-block text-block_mb container"></div>
        <div v-html="$t('block.bonus.text')" className="text-block text-block_mb container"></div>
    </main>
</template>

<script>

import axios from "axios";
import {defaultLanguage, SUPPORT_LOCALES} from "../plugins/i18nPlugin";

export default {
    name: "Bonuses",
    button: '',
    beforeRouteEnter(to, from, next) {
        let locale = to.path.split('/')[1];
        if (!SUPPORT_LOCALES.includes(locale)) {
            locale = defaultLanguage
        }
        axios.post('/a/static', {slug: 'bonuses', locale}).then(response => {
            next(vm => vm.setData(null, response.data.post))
        })
    },
    async beforeRouteUpdate(to, from, next) {
        const {data: statics} = await axios.post('/a/static', {slug: 'bonuses', locale: to.params.locale})
        await this.setData(null, statics.post)
        next()
    },
    data() {
        return {
            bonuses: [],
            post: {},
            block: window.common_data?.block_bonus || {text: ''}
        }
    },
    computed: {
        loggedIn() {
            return this.$store.state.user.status.loggedIn;
        },
    },
    watch: {
        loggedIn: {
            async handler(val, oldVal) {
                if (val !== oldVal) {
                    this.setButton()
                }
            },
        },
    },
    methods: {
        async setData(err, post) {
            if (err) {
                this.error = err.toString()
            } else {
                this.post = post
                this.$options.button = this.$refs.statics.querySelector('.bonus-card__dash button')
                this.setButton()
                this.$options.button.addEventListener('click', () => this.setEvent())
                this.set_head(`${post?.title}`, post?.title, post?.meta_description);
            }
        },
        setButton() {
            if (this.loggedIn) {
                this.$options.button.textContent = this.$t('bonuses_claim_bonus')
            } else {
                this.$options.button.textContent = this.$t('bonuses_create_account')
            }
        },
        setEvent() {
            if (this.loggedIn) {
                this.$router.push({name: 'deposit'})
            } else {
                this.$store.commit('open_pop', 1)
            }
        },
        set_head(v_title, m_title, m_description) {
            let head = document.querySelector('head')
            let title = head.querySelector('title')
            let meta_title = head.querySelector('meta[name="title"]')
            let meta_description = head.querySelector('meta[name="description"]');
            title.textContent = v_title
            meta_title.textContent = m_title;
            meta_description.setAttribute("content", m_description);
        }
    },
    unmounted() {
        this.$options.button.removeEventListener('click', this.setEvent)
    }
}
</script>

<style scoped>

</style>
