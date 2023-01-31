<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header mt-28 mb-28">
                <i18n-t keypath="pop_cancel_withdrawal" tag="h5">
                    <template v-slot:link>
                        <router-link @click="$store.commit('close_pop')" :to="{name: 'promo-bonuses'}"
                                     class="text text_primary text-height-24 text_unlerline">{{$t('pop_cancel_withdrawal_link')}}</router-link>
                    </template>
                </i18n-t>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppWithdrawalCancel"
}
</script>

<style scoped>

h5 {
    font-family: Raleway;
    font-style: normal;
    font-weight: 500;
    font-size: 16px;
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
