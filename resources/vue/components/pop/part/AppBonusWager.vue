<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header mt-28 mb-28">
                <h5>
                    You can't play this game while bonus is active. Please try another game.
                </h5>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppBonusWager"
}
</script>

<style scoped>

h5 {
    font-family: Raleway;
    font-style: normal;
    font-weight: 500;
    font-size: 22px;
    line-height: 28px;
    /* or 150% */

    text-align: center;
    color: #FFFFFF;
}

.mt-28 {
    margin-top: 28px;
}

.profile-card__header {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
</style>
