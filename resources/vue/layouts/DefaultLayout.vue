<template>
    <header-component></header-component>
            <slot></slot>
    <footer-component></footer-component>
    <popup-component></popup-component>
</template>

<script setup>
import HeaderComponent from "../components/header/HeaderComponent.vue";
import PopupComponent from "../components/pop/PopupComponent.vue";
import FooterComponent from "../components/footer/FooterComponent.vue";

</script>

<style>

.main-app{
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

</style>
