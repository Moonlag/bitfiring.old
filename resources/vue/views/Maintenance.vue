<template>
    <main class="main maintenance">
        <bubble-top-component></bubble-top-component>
        <router-link class="navbar__logo" to="/"><picture><source srcset="/assets/img/navbar/logo.svg" type="image/webp"><img class="img_fluid" src="/assets/img/navbar/logo.svg" alt="logo" width="205" height="126"></picture></router-link>
        <div class="maintenance-logo">
            <picture>
                <source srcset="/assets/img/navbar/maintenance.svg" type="image/webp">
                <img class="img_fluid" src="/assets/img/navbar/maintenance.svg" alt="logo" width="245" height="245">
            </picture>
        </div>
        <header class="header container">
            <div class="header__caption">
                <h1 class="header__title mb-24">Oops, we are on maintenance</h1>
                <h2 class="header__text-sm mb-48">
                    Technical work are in progress and the project will be available as soon as possible
                </h2>
            </div>
        </header>
    </main>
</template>

<script>
import BubbleTopComponent from "../components/buble/BubbleTopComponent";
export default {
    name: "Maintenance",
    components: {
        BubbleTopComponent
    }
}
</script>

<style scoped>

</style>
