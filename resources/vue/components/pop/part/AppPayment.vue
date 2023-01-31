<template>
    <div class="popup fancybox-content" id="payment">
        <div class="profile-card profile-card_popup-profile">
            <div class="profile-card__header justify_between">
                <h2 class="profile-card__title">
                    <picture @click="close_pop">
                        <source srcset="/assets/img/title-icon/title_icon_3.svg" type="image/webp">
                        <img class="profile-card__title-icon" src="/assets/img/title-icon/title_icon_3.svg" alt="img">
                    </picture>

                    Payment
                </h2>

                <picture>
                    <source srcset="/assets/img/common/close-popup.svg" type="image/webp">
                    <img class="profile-card__header-icon popup__close-btn" src="/assets/img/common/close-popup.svg"
                         alt="close"></picture>
            </div>

            <div class="profile-card__description">
                <p class="profile-card__text">
                    Screenshot or photo from online bank, bank statement, Skrill page, etc. showing the deposit. Must
                    display the company name. Must display the amount paid.
                </p>
            </div>

            <a class="btn btn_w100 ripple  btn_profile btn_light" href="#">
                Got It
            </a>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppPayment",
    methods: {
        close_pop(){
            this.$store.commit('close_pop')
        }
    }
}
</script>

<style scoped>

</style>
