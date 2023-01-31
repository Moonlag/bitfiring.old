<template>
    <div class="popup fancybox-content" id="popup_1">
        <div class="profile-card profile-card_popup-profile">
            <div class="profile-card__header justify_between">
                <h2 class="profile-card__title">
                    New Wager Limit
                </h2>

                <picture @click="close_pop">
                    <source srcset="/assets/img/common/close-popup.svg" type="image/webp">
                    <img class="profile-card__header-icon popup__close-btn" src="/assets/img/common/close-popup.svg"
                         alt="close"></picture>
            </div>

            <p class="profile-card__text text_primary mb-12">
                Timesheet
            </p>

            <form class="form">
                <div class="row mb-16">
                    <div class="col">
                        <select class="select-block__wide form-block " name="form[]" required aria-label="Status">
                            <option value="0" selected="selected">Daily</option>
                            <option value="2">item 2</option>
                            <option value="3">item 3</option>
                            <option value="4">item 4</option>
                            <option value="5">item 5</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-16">
                    <div class="form__item form-item_left form__badge col-auto">
                        USD
                    </div>

                    <div class="form__item form__item_align-right form-item_center col">
                        <input type="text" placeholder="Sum" id="sum_214" name="sum_214" data-msg="required"
                               aria-label="USDT" value="0" required>
                    </div>

                    <div class="form__item form-item_right form__badge form__badge_clear form__badge_main col-auto">
                        daily
                    </div>
                </div>

                <div class="row mb-24">
                    <div class="form__item form-item_left form__badge col-auto">
                        EUR
                    </div>

                    <div class="form__item form__item_align-right form-item_center col">
                        <input type="text" placeholder="Sum" id="sum_213" name="sum_213" data-msg="required"
                               aria-label="USDT" value="0" required>
                    </div>

                    <div class="form__item form-item_right form__badge form__badge_clear form__badge_main col-auto">
                        daily
                    </div>
                </div>

                <p class="profile-card__text mb-24">
                    The restriction takes effect instantly. You can reduce your limit at any time, this change will take
                    effect immediately. You can increase the limit, however, in order for
                    this change to take effect, you need 24 hours. Cancellation of the deposit limit takes 24 hours.
                    After the limit is exceeded, you will receive an email notification.
                </p>

                <button class="btn btn_transparent btn_profile ripple btn_w100" type="submit" data-effect="mfp-zoom-in">
                    Set Limit
                </button>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppPopup_1",
    methods: {
        close_pop(){
            this.$store.commit('close_pop')
        }
    }
}
</script>

<style scoped>

</style>
