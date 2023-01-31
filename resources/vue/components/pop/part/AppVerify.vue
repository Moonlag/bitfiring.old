<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header justify_center mb-28">
                <h2 class="profile-card__title profile-card__title_lg">
                  Change your password
                </h2>
            </div>
            <div>
                <div v-if="v$.user.$model.serverErrors" class="error_container">
                    <div v-for="error in v$.user.$model.serverErrors">
                        <p v-if="error.join()">
                            {{ error.join() }}
                        </p>
                    </div>
                </div>
                <form @submit.prevent="onSubmit" class="form">
                    <div class="row mb-24">
                        <div class="form__item form__item-main col">
                            <input v-model="user.password"
                                   :class="[{ 'error': submitted && v$.user.password.$error}, { 'valid': submitted && !v$.user.password.$error},]"
                                   type="password"
                                   @input="clearServerError(v$.user.$model, 'password')"
                                   placeholder="New password"
                                   id="password"
                                   name="password"
                                   data-msg="required"
                                   data-rule-minlength="8"
                                   data-msg-minlength="At least 8 numbers" aria-label="Email" required>
                            <div class="form__item-badge">
                                <svg>
                                    <use href="/assets/img/icons/icons.svg#badge_lock"></use>
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
                            <input v-model="user.password_confirmation"
                                   type="password"
                                   placeholder="Password Confirmation"
                                   id="password_confirmation"
                                   name="password"
                                   data-msg="required"
                                   data-rule-minlength="8"
                                   data-msg-minlength="At least 8 numbers" aria-label="Email" required>

                            <div class="form__item-badge">
                                <svg>
                                    <use href="/assets/img/icons/icons.svg#badge_lock"></use>
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
                    <div class="flex_center mb-30">
                        <button class="btn btn-primary btn-block btn-xl">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import {email, minLength, required} from "@vuelidate/validators";
import {merge} from "lodash";
import useVuelidate from "@vuelidate/core";

export default {
    name: "AppForgot",
    created() {
        this.user = this.forgot
        console.log(this.user)
    },
    data() {
        return {
            v$: useVuelidate(),
            user: {
                email: '',
                token: '',
                password: '',
                password_confirmation: ''
            },
            token: '',
            clientValidation: {
                user: {
                    email: {required, email},
                    token: {required},
                    password: {
                        required, minLength: minLength(8), containsSpecial: function (value) {
                            return /^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/.test(value)
                        },
                    },
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
        },
        forgot() {
            return this.$store.state.forgot || []
        }
    },
    validations() {
        return this.rules;
    },
    methods: {
        close_pop() {
            this.$store.commit('close_pop')
        },
        async onSubmit() {
            this.submitted = true;
            this.btn_disabled = true;
            this.v$.$touch();

            this.clearServerErrors();

            await this.$store.dispatch('user/updatedPassword', this.user).then(
                response => {
                    console.log('then')
                    this.$store.commit('close_pop')
                    this.$router.push('/')
                }
            ).catch(
                error => {
                    console.log('catch')
                    if (error.response.data) {

                        const serverMessages = {
                            serverErrors: {email: [], password: [], token: []}
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
