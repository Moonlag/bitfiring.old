<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header justify_center mb-28">
                <h2 class="profile-card__title profile-card__title_lg">
                    Forgot password?
                </h2>
            </div>
            <div v-if="!msg">
                <p class="text text_p mb-20">
                    Fill in your e-mail address and we will send you instructions on how to reset your password via
                    e-mail.
                </p>
                <div v-if="v$.user.$model.serverErrors" class="error_container">
                    <p v-for="error in v$.user.$model.serverErrors">
                        {{ error.join() }}
                    </p>
                </div>
                <form @submit.prevent="onSubmit" class="form">
                    <div class="row mb-24">
                        <div class="form__item form__item-main col">
                            <input
                                v-model="user.email"
                                :class="[{ 'error': submitted && v$.user.email.$error}, { 'valid': submitted && !v$.user.email.$error},]"
                                type="text" placeholder="Your Email" id="email_214" name="email_214" data-msg="required"
                                @input="clearServerError(v$.user.$model, 'email')"
                                aria-label="password" required>
                            <div class="form__item-badge">
                                <svg>
                                    <use href="/assets/img/icons/icons.svg#badge_email"></use>
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
                    <div class="flex_center mb-30">
                        <button class="btn btn-primary btn-block btn-xl">{{ button_form }}</button>
                    </div>
                    <div class="popup__bottom text text_primary text-height-24 text_center">
                        Return to sign in form? <a @click="$store.commit('open_pop', 2)"
                                                   class="text text_primary text-height-24 text_unlerline"
                                                   href="javascript:;" data-fancybox
                                                   data-src="#login">Login</a></div>
                </form>
            </div>
            <div v-else>
                <h4 class="center_message">{{ msg }}</h4>
            </div>
        </div>
    </div>
</template>

<script>
import useVuelidate from "@vuelidate/core";
import {required, minLength, email} from '@vuelidate/validators'

import {merge} from "lodash";

export default {
    name: "AppForgot",
    data() {
        return {
            v$: useVuelidate(),
            user: {
                email: ''
            },
            msg: '',
            button_form: 'Next Step',
            clientValidation: {
                user: {
                    email: {required, email},
                }
            },
            serverValidation: {},
            btn_disabled: false,
            submitted: false,
        }
    },
    computed: {
        rules() {
            return merge({}, this.serverValidation, this.clientValidation);
        }
    },
    validations() {
        return this.rules;
    },
    methods: {
        close_pop() {
            this.$store.commit('close_pop')
        },
        onSubmit() {
            this.submitted = true;
            this.btn_disabled = true;
            this.v$.$touch();

            this.clearServerErrors();

            this.button_form = 'Please wait...'
            this.$store.dispatch('user/forgotPassword', this.user.email).then(
                response => {
                    this.msg = 'We have emailed your password reset link!'
                }
            ).catch(
                error => {
                    if (error.response.data) {
                        const serverMessages = {
                            serverErrors: {email: []}
                        };

                        const serverError = function (fieldName) {
                            return (value, vm) => {
                                return !(
                                    vm.hasOwnProperty("serverErrors") &&
                                    vm.serverErrors.hasOwnProperty(fieldName)
                                );
                            };
                        };

                        let errors = {}

                        for (let key in error.response.data.errors) {
                            serverMessages.serverErrors[key].push(error.response.data.errors[key])
                            errors[key] = {
                                serverError: serverError(key, false)
                            }
                        }

                        merge(this.user, serverMessages);

                        this.serverValidation.user = errors
                        this.button_form = 'Next Step'
                    }
                }
            )
        },
        removeProp(obj, propName) {
            for (var p in obj) {
                if (obj.hasOwnProperty(p)) {
                    if (p === propName) {
                        delete obj[p];
                    } else if (typeof obj[p] === "object") {
                        this.removeProp(obj[p], propName);
                    }
                }
            }
            return obj;
        },
        clearServerErrors: function () {
            this.serverValidation = {};
            this.removeProp(this.user, "serverErrors");
        },
        clearServerError: function (model, fieldName) {
            if (this.serverValidation.hasOwnProperty('user')) {
                if (this.serverValidation.user.hasOwnProperty(fieldName)) {
                    delete this.serverValidation.user[fieldName];
                }
            }
            if (model.hasOwnProperty("serverErrors")) {
                if (model.serverErrors.hasOwnProperty(fieldName)) {
                    delete model.serverErrors[fieldName];
                }
            }
        }
    }
}
</script>

<style scoped>

</style>
