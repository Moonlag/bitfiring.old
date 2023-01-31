<template>
    <div class="main main_profile">
        <slot></slot>
    </div>
    <popup-component></popup-component>
</template>

<script>
import PopupComponent from "../components/pop/PopupComponent";
export default {
    name: "ProfileLayout",
    components: {
        PopupComponent
    },
}
</script>

<style scoped>

</style>
