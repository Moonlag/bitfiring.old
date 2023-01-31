<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
    <div class="profile-card__header justify_center mb-28">
        <h2 class="profile-card__title profile-card__title_lg">
            Information
        </h2>
    </div>
    <div class="profile-card__header justify_center" data-v-330a567d="">
        <h2 class="profile-card__title" data-v-330a567d="">
            <picture data-v-330a567d="">
                <source srcset="/assets/img/title-icon/title_icon_5.svg" type="image/webp" data-v-330a567d="">
                <img class="profile-card__title-icon" src="/assets/img/title-icon/title_icon_5.svg" alt="img"
                     data-v-330a567d=""></picture>
            {{ message }}
        </h2>
    </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppInfo",
    computed: {
        message(){
            return this.$store.state.popmessage
        }
    }
}
</script>

<style scoped>

</style>
