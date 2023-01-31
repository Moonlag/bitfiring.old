<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>

        </div>
    </div>
</template>

<script>
export default {
    name: "AppGameReject",
    computed: {
        data(){
            return this.$store.state.gameReject.params
        }
    }
}
</script>

<style scoped>

</style>
