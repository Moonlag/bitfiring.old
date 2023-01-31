<template>
        <div class="fullscreen-wrapper">
            <slot></slot>
        </div>
    <popup-component></popup-component>
</template>

<script>
import HeaderPlay from "../components/header/HeaderPlay";
import PopupComponent from "../components/pop/PopupComponent";

export default {
    name: "PlayLayout",
    components: {
        HeaderPlay,
        PopupComponent
    }
}
</script>

<style scoped>
</style>
