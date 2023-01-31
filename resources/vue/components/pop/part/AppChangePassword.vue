<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
    <div class="profile-card__header justify_center mb-28">
        <h2 class="profile-card__title profile-card__title_lg">
            Change Password
        </h2>
    </div>

    <div v-if="v$.password.$model.serverErrors" class="error_container">
        <div v-for="error in v$.password.$model.serverErrors">
            <p v-if="error.join()">
                {{error.join()}}
            </p>
        </div>

    </div>

    <form @submit.prevent="change_password" class="form" id="change_pass_form" action="/change_pass" method="post">
        <div class="row mb-24">
            <div class="form__item form__item-main col">
                <input v-model="password.old_password"
                       :class="[{ 'error': submitted && v$.password.old_password.$error}, { 'valid': submitted && !v$.password.old_password.$error},]"
                       @input="clearServerError(v$.password.$model, 'old_password')"
                       type="password" placeholder="Old Password" id="old_password"
                       name="old_password" data-msg="required" data-rule-minlength="8"
                       data-msg-minlength="At least 8 numbers" aria-label="password" required>
                <div class="form__item-badge">
                    <svg>
                        <use href="/assets/img/icons/icons.svg#badge_lock"></use>
                    </svg>
                </div>
                <div class="form__item-sign form__item-sign_error">
                    <picture>
                        <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                        <img src="/assets/img/common/error-sign.svg" alt="error">
                    </picture>
                </div>
                <div class="form__item-sign form__item-sign_success">
                    <picture>
                        <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                        <img src="/assets/img/common/succes-sign.svg" alt="success">
                    </picture>
                </div>
                <div class="form__item-border "></div>
            </div>
        </div>
        <div class="row mb-24">
            <div class="form__item form__item-main col">
                <input v-model="password.new_password"
                       :class="[{ 'error': submitted && v$.password.new_password.$error}, { 'valid': submitted && !v$.password.new_password.$error},]"
                       @input="clearServerError(v$.password.$model, 'new_password')"
                       type="password" placeholder="New Password" id="new_password"
                       name="new_password" data-msg="required" data-rule-minlength="8"
                       data-msg-minlength="At least 8 numbers" aria-label="password" required>
                <div class="form__item-badge">
                    <svg>
                        <use href="/assets/img/icons/icons.svg#badge_lock"></use>
                    </svg>
                </div>
                <div class="form__item-sign form__item-sign_error">
                    <picture>
                        <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                        <img src="/assets/img/common/error-sign.svg" alt="error">
                    </picture>
                </div>
                <div class="form__item-sign form__item-sign_success">
                    <picture>
                        <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                        <img src="/assets/img/common/succes-sign.svg" alt="success">
                    </picture>
                </div>
                <div class="form__item-border "></div>
            </div>
        </div>
        <div class="row mb-24">
            <div class="form__item form__item-main col">
                <input v-model="password.repeat_password" type="password" placeholder="Repeat Password"
                       :class="[{ 'error': submitted && v$.password.repeat_password.$error}, { 'valid': submitted && !v$.password.repeat_password.$error},]"
                       @input="clearServerError(v$.password.$model, 'repeat_password')"
                       id="repeat_password" name="repeat_password" data-msg="required" data-rule-minlength="8"
                       data-msg-minlength="At least 8 numbers" aria-label="password" required>
                <div class="form__item-badge">
                    <svg>
                        <use href="/assets/img/icons/icons.svg#badge_lock"></use>
                    </svg>
                </div>
                <div class="form__item-sign form__item-sign_error">
                    <picture>
                        <source srcset="/assets/img/common/error-sign.svg" type="image/webp">
                        <img src="/assets/img/common/error-sign.svg" alt="error">
                    </picture>
                </div>
                <div class="form__item-sign form__item-sign_success">
                    <picture>
                        <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                        <img src="/assets/img/common/succes-sign.svg" alt="success">
                    </picture>
                </div>
                <div class="form__item-border "></div>
            </div>
        </div>
        <button class="btn btn-primary btn-block btn-xl">Change password</button>
    </form>
        </div>
    </div>
</template>

<script>
import useVuelidate from "@vuelidate/core";
import {required, minLength, email} from '@vuelidate/validators'
import {merge} from "lodash"

export default {
    name: "AppChangePassword",
    data() {
        return {
            v$: useVuelidate(),
            btn_disabled: false,
            submitted: false,
            password: {
                old_password: '',
                new_password: '',
                repeat_password: ''
            },
            serverValidation: {},
            clientValidation: {
                password: {
                    old_password: {required},
                    new_password: {
                        required, minLength: minLength(8), containsSpecial: function (value) {
                            return /^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/.test(value)
                        },
                    },
                    repeat_password: {required}
                }
            },
        }
    },
    validations() {
        return this.rules;
    },
    computed: {
        rules() {
            return merge({}, this.serverValidation, this.clientValidation);
        }
    },
    methods: {
        change_password() {
            this.submitted = true;
            this.btn_disabled = true;
            this.v$.$touch();
            this.$store.dispatch('user/changePassword', this.password).then(
                (response) => {
                    if(response.success){
                        this.password = {
                            old_password: '',
                            new_password: '',
                            repeat_password: ''
                        }
                        this.$store.dispatch('info', response.message);
                    }
                    if(response.errors && response.error_keys){
                        const serverMessages = {
                            serverErrors: {old_password: [], new_password: [], repeat_password: []}
                        };

                        response.error_keys.forEach((el, idx) => {
                            serverMessages.serverErrors[el].push(response.errors[idx])
                        })

                        merge(this.password, serverMessages);

                        const serverError = function(fieldName) {
                            return (value, vm) => {
                                return !(
                                    vm.hasOwnProperty("serverErrors") &&
                                    vm.serverErrors.hasOwnProperty(fieldName)
                                );
                            };
                        };

                        let errors = {}
                        response.error_keys.forEach(el => {
                            errors[el] = {
                                serverError: serverError(el, false)
                            }
                        })
                        this.serverValidation.password = errors
                    }
                })
        },
        removeProp(obj, propName){
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
            if(this.serverValidation.hasOwnProperty('user')){
                if(this.serverValidation.user.hasOwnProperty(fieldName)){
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
