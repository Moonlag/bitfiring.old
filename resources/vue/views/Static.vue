<template>
    <main class="main">
        <bubble-top-component></bubble-top-component>
        <header class="header container">
            <div class="header__caption flex_center"><h1 class="header__title"> {{post?.headline}} </h1></div>
        </header>
        <!-- TEXT BLOCK -->
        <div v-html="post?.description" class="text-block text-block_mb container">
        </div>
    </main>
</template>

<script>
import axios from "axios";
import {SUPPORT_LOCALES, defaultLanguage} from "../plugins/i18nPlugin"
export default {
    name: "Static",
    props: ['static'],
    data(){
        return {
            post: {},
            site_name: 'Bitfiring',
        }
    },
    beforeRouteEnter (to, from, next) {
        let locale = to.path.split('/')[1];
        if (!SUPPORT_LOCALES.includes(locale)) {
            locale = defaultLanguage
        }
        axios.post('/a/static', {slug: to.params.static, locale}).then(response => {
            next(vm => vm.setData(null, response.data.post))
        })
    },
    beforeRouteUpdate (to, from, next) {
        axios.post('/a/static', {slug: to.params.static, locale: to.params?.locale}).then(response => {
            this.setData(null, response.data.post)
        })
        next()
    },
    methods: {
        setData (err, post) {
            if (err) {
                this.error = err.toString()
            } else {
                this.post = post
                this.set_head(`${post.title}`, post.title, post.meta_description);
            }
        },
        set_head(v_title, m_title, m_description){
            let head = document.querySelector('head')
            let title = head.querySelector('title')
            let meta_title = head.querySelector('meta[name="title"]')
            let meta_description = head.querySelector('meta[name="description"]');
            title.textContent = v_title
            meta_title.textContent = m_title;
            meta_description.setAttribute("content", m_description);
        }
    },
    beforeUnmount() {
        this.set_head(this.site_name, this.site_name);
    }
}
</script>

<style scoped>

</style>
