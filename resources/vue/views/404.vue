<template>
    <main class="main not-found">
        <bubble-top-component img="/assets/img/top_bg/bg-404"></bubble-top-component>
        <header class="header container">
            <div class="header__caption">
                <h1 class="header__title mb-24">
                    Oops, this page <br> is not available
                </h1>
                <h2 class="header__text-sm mb-48">
                    Technical work are in progress and The page you are trying <br> to access does not exist or has been moved.
                    Try going back to our homepage.
                </h2>
                <button></button>
                <router-link class="btn btn-xl btn_gradient" to="/">
                       Go to the homepage
                </router-link>
            </div>
        </header>
    </main>
</template>

<script>
import BubbleTopComponent from "../components/buble/BubbleTopComponent";
export default {
    name: "404",
    components: {
        BubbleTopComponent
    },
    mounted() {
        setTimeout(() => {
            this.$store.commit('close_loader')
        }, 1700)
    },
}
</script>

<style scoped>
    .header__title{
        display: inline-block;
    }
</style>
