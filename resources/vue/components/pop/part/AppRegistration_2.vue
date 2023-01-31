<template>


    <div class="profile-card__header justify_center mb-28">
        <h2 class="profile-card__title profile-card__title_lg">
            Registration
        </h2>
    </div>

    <div class="error_container">
    </div>

    <form class="form" action="/join_second" method="post" id="create_account_final">
        <div class="form__row row mb-24">
            <div class="form__item-wrap col-6 col-sm-12 mb-sm-24">
                <div class="form__item form__item-main ">
                    <input type="text" placeholder="First Name" id="firstname" name="firstname"
                           data-msg="required" aria-label="First Name" required>

                    <div class="form__item-badge">
                        <svg>
                            <use href="/assets/img/icons/icons.svg#badge_user"></use>
                        </svg>
                    </div>

                    <div class="form__item-sign form__item-sign_error">
                        <picture>
                            <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                            <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                    </div>

                    <div class="form__item-sign form__item-sign_success">
                        <picture>
                            <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                            <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                    </div>

                    <div class="form__item-border "></div>
                </div>
            </div>

            <div class="form__item-wrap col-6 col-sm-12">
                <div class="form__item form__item-main ">
                    <input type="text" placeholder="Last Name" id="lastname" name="lastname" data-msg="required"
                           aria-label="Last Name" required>

                    <div class="form__item-badge">
                        <svg>
                            <use href="/assets/img/icons/icons.svg#badge_user"></use>
                        </svg>
                    </div>

                    <div class="form__item-sign form__item-sign_error">
                        <picture>
                            <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                            <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                    </div>

                    <div class="form__item-sign form__item-sign_success">
                        <picture>
                            <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                            <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                    </div>

                    <div class="form__item-border "></div>
                </div>
            </div>
        </div>

        <div class="form__row row mb-24">
            <div class="form__item-wrap col-4 col-sm-12 mb-sm-24">
                <div class="form__item form__item-main ">
                    <input type="text" placeholder="DD" id="day" name="day" data-msg="required" aria-label="Day"
                           required>

                    <div class="form__item-badge">
                        <svg>
                            <use href="/assets/img/icons/icons.svg#badge_date"></use>
                        </svg>
                    </div>

                    <div class="form__item-sign form__item-sign_error">
                        <picture>
                            <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                            <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                    </div>

                    <div class="form__item-sign form__item-sign_success">
                        <picture>
                            <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                            <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                    </div>

                    <div class="form__item-border form__item-border_error"></div>
                </div>
            </div>

            <div class="form__item-wrap col-4 col-sm-12 mb-sm-24">
                <div class="form__item form__item-main ">
                    <input type="text" placeholder="MM" id="month" name="month" data-msg="required"
                           aria-label="Month" required>

                    <div class="form__item-badge">
                        <svg>
                            <use href="/assets/img/icons/icons.svg#badge_date"></use>
                        </svg>
                    </div>

                    <div class="form__item-sign form__item-sign_error">
                        <picture>
                            <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                            <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                    </div>

                    <div class="form__item-sign form__item-sign_success">
                        <picture>
                            <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                            <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                    </div>

                    <div class="form__item-border form__item-border_error"></div>
                </div>
            </div>

            <div class="form__item-wrap col-4 col-sm-12">
                <div class="form__item form__item-main ">
                    <input type="text" placeholder="YYYY" id="year" name="year" data-msg="required"
                           aria-label="Year" required>

                    <div class="form__item-badge">
                        <svg>
                            <use href="/assets/img/icons/icons.svg#badge_date"></use>
                        </svg>
                    </div>

                    <div class="form__item-sign form__item-sign_error">
                        <picture>
                            <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                            <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                    </div>

                    <div class="form__item-sign form__item-sign_success">
                        <picture>
                            <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                            <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                    </div>

                    <div class="form__item-border form__item-border_error"></div>
                </div>
            </div>
        </div>

        <div class="row mb-24">
            <div class="form__item form__item-main col">
                <input type="text" placeholder="City" id="city" name="city" data-msg="required"
                       data-rule-minlength="3" data-msg-minlength="At least 3 numbers"
                       aria-label="City" required>

                <div class="form__item-badge">
                    <svg>
                        <use href="/assets/img/icons/icons.svg#badge_city"></use>
                    </svg>
                </div>

                <div class="form__item-sign form__item-sign_error">
                    <picture>
                        <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                        <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                </div>

                <div class="form__item-sign form__item-sign_success">
                    <picture>
                        <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                        <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                </div>

                <div class="form__item-border form__item-border_error"></div>
            </div>
        </div>

        <div class="row mb-24">
            <div class="form__item form__item-main col">
                <input type="text" placeholder="Address" id="address" name="address" data-msg="required"
                       aria-label="currency" required>

                <div class="form__item-badge">
                    <svg>
                        <use href="/assets/img/icons/icons.svg#badge_location"></use>
                    </svg>
                </div>

                <div class="form__item-sign form__item-sign_error">
                    <picture>
                        <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                        <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                </div>

                <div class="form__item-sign form__item-sign_success">
                    <picture>
                        <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                        <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                </div>

                <div class="form__item-border form__item-border_error"></div>
            </div>
        </div>

        <div class="row mb-24">
            <div class="form__item form__item-main col">
                <input type="text" placeholder="Postal Code" id="postalcode" name="postalcode"
                       data-msg="required" aria-label="Postal Code" required>

                <div class="form__item-badge">
                    <svg>
                        <use href="/assets/img/icons/icons.svg#badge_post"></use>
                    </svg>
                </div>

                <div class="form__item-sign form__item-sign_error">
                    <picture>
                        <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                        <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
                </div>

                <div class="form__item-sign form__item-sign_success">
                    <picture>
                        <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                        <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
                </div>

                <div class="form__item-border form__item-border_error"></div>
            </div>
        </div>

        <div class="row mb-24">
            <label class="checkbox">
                <input type="checkbox" name="email_accept">
                <span class="checkbox__box">
                        <svg>
                            <use href="/assets/img/common/check_sprite.svg#check_1"></use>
                        </svg>
                    </span>
                <span class="text_primary col">
						Receive promos by email
					</span>
            </label>

        </div>

        <div class="row mb-24">
            <label class="checkbox">
                <input type="checkbox" name="sms_accept">
                <span class="checkbox__box">
                        <svg>
                            <use href="/assets/img/common/check_sprite.svg#check_1"></use>
                        </svg>
                    </span>
                <span class="text_primary col">
						Receive SMS Promos
					</span>
            </label>

        </div>

        <div class="row mb-24">
            <label class="checkbox">
                <input type="checkbox" checked="checked" name="policy_accept">
                <span class="checkbox__box">
                        <svg>
                            <use href="/assets/img/common/check_sprite.svg#check_1"></use>
                        </svg>
                    </span>
                <span class="text_primary col">
						I’m 18 years old and I accept the Terms and Conditions and Privacy Policy
					</span>
            </label>

        </div>
        <div class="flex_center">
            <div @click="$store.commit('open_pop', 1)" class="button">
                <div class="layer1-shadow">
                    <div class="layer1-border-out"></div>
                </div>

                <div class="layer2-wrap">
                    <div class="layer2-border-inside"></div>
                </div>
                <div class="layer3-shadow">
                    <svg class="layer3-background" width="241" height="72" viewBox="0 0 241 72" fill="none"
                         xmlns="http://www.w3.org/2000/svg" role="image" aria-label="button" preserveAspectRatio="none">
                        <path
                            d="M228.261 1.5C228.082 1.19059 227.752 1 227.395 1H77.2092H2C1.44772 1 1 1.44772 1 2V44.5767V49.8991C1 50.0747 1.0462 50.2471 1.13397 50.3991L12.7392 70.5C12.9178 70.8094 13.248 71 13.6052 71H163.791H239C239.552 71 240 70.5523 240 70V27.4236V22.1011C240 21.9256 239.954 21.7531 239.866 21.6011L228.261 1.5Z"
                            fill="#0385CE" stroke="url(#paint0_linear)" stroke-width="2"/>
                        <defs>
                            <linearGradient id="paint0_linear" x1="241" y1="41.5" x2="-1.69029e-07" y2="41.5"
                                            gradientUnits="userSpaceOnUse">
                                <stop stop-color="#C5EAFF" stop-opacity="0"/>
                                <stop offset="0.479167" stop-color="#F3FBFF"/>
                                <stop offset="1" stop-color="#C5EAFF" stop-opacity="0.13"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="layer4-text">
                    <p>Registration</p>
                </div>
            </div>
        </div>

        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <input type="hidden" id="email_t" name="email" value=""/>
        <input type="hidden" id="password_t" name="password" value=""/>
        <input type="hidden" id="password_confirm_t" name="password_confirm" value=""/>
        <input type="hidden" id="currency_t" name="currency" value=""/>
        <input type="hidden" id="country_t" name="country" value=""/>

    </form>

</template>

<script>
export default {
    name: "AppRegistration_2",
    methods: {
        close_pop() {
            this.$store.commit('close_pop')
        }
    }
}
</script>

<style scoped>

</style>
