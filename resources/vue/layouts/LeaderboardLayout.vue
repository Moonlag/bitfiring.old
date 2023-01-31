<template>
    <header-component></header-component>
    <popup-component></popup-component>
    <div class="main">
        <bubble-top-component img="/assets/img/leader_board/background"/>
        <slot></slot>
    </div>
    <footer-component></footer-component>
    <popup-component></popup-component>
</template>

<script setup>
import HeaderComponent from "../components/header/HeaderComponent.vue";
import PopupComponent from "../components/pop/PopupComponent.vue";
import FooterComponent from "../components/footer/FooterComponent.vue";
import BubbleTopComponent from "../components/buble/BubbleTopComponent.vue";

</script>

<style scoped>

</style>
