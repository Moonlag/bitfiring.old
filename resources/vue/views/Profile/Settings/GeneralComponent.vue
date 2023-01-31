<template>
    <div class="tab__body profile-tabs__body">
        <section class="profile-block">
            <div v-if="v$.user.$model.serverErrors" class="error_container">
                <div v-for="(error, idx) in v$.user.$model.serverErrors">
                    <p v-if="error.join()">{{ error.join() }}</p>
                </div>
            </div>
            <form @submit.prevent="update_profile" class="form profile-block__filter">
                <div class="form__row row">
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label" for="email">Email</label>
                        <div class="form__item">
                            <input v-model="user.email" type="text" placeholder="Enter your email"
                                   :class="[{ 'error': submitted && v$.user.email.$error}, { 'valid': submitted && !v$.user.email.$error},]"
                                   id="email" name="email"
                                   data-msg="required" required></div>
                    </div>
                    <div class="form__item-wrap col-6 col-sm-12">
                        <label class="form__label">Gender</label>
                        <div class="form__row row">
                            <div class="form__item-wrap col-12">
                                <profile-component @select2="setGender" :s2model="user.gender" placeholder="Gender"
                                                   :options="gender"></profile-component>
                            </div>
                        </div>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label" for="f_name">First Name</label>
                        <div class="form__item">
                            <input
                                v-model="user.firstname"
                                :class="[{ 'error': submitted && v$.user.firstname.$error}, { 'valid': submitted && !v$.user.firstname.$error},]"
                                type="text"
                                placeholder="Enter your first name" name="f_name"
                                id="f_name"
                                data-msg="required"
                            >
                            <div class="form__item-border"></div>
                        </div>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label" for="l_name">Last Name</label>
                        <div class="form__item">
                            <input
                                v-model="user.lastname"
                                :class="[{ 'error': submitted && v$.user.lastname.$error}, { 'valid': submitted && !v$.user.lastname.$error},]"
                                type="text" placeholder="Enter your last name" name="l_name"
                                id="l_name" data-msg="required">
                        </div>
                    </div>
                    <div class="form__item-wrap col-6 col-sm-12">
                        <div class="form__label">Date of Birth</div>
                        <div class="form__row">
                            <div class="form__item col-35">
                                <input v-model="dob.d" type="text" placeholder="Day" id="day" name="day"
                                       data-msg="required"
                                >
                            </div>
                            <div class="form__item col-35">
                                <input v-model="dob.m" type="text" placeholder="Month" id="month" name="month"
                                       data-msg="required"
                                >
                            </div>
                            <div class="form__item col-35">
                                <input v-model="dob.y" type="text" placeholder="Year" id="year" name="year"
                                       data-msg="required"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label">Countries</label>
                        <div class="form__row row">
                            <div class="form__item-wrap col-12">
                                <profile-component @select2="setCountry" :s2model="user.country_id" placeholder="Countries"
                                                   :options="countries"></profile-component>
                            </div>
                        </div>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label" for="city">City</label>
                        <div class="form__item">
                            <input v-model="user.city" type="text" placeholder="Enter your city"
                                   name="city" id="city"
                                   data-msg="required">
                        </div>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label" for="address">Address</label>
                        <div class="form__item">
                            <input v-model="user.address" type="text" placeholder="Enter your address"
                                   name="address"
                                   data-msg="required"
                            >
                        </div>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label" for="postal_code">Postal Code</label>
                        <div class="form__item">
                            <input v-model="user.postal_code" type="text"
                                   placeholder="Enter your Postal Code" id="postal_code"
                                   name="postal_code" data-msg="required"
                            >
                        </div>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="form__label" for="phone">Mobile Phone</label>
                        <div class="form__item">
                            <input v-model="user.phone" type="text" placeholder="Enter your Postal Code"
                                   id="phone" name="phone"
                                   data-msg="required"
                            >
                        </div>
                    </div>
                </div>
                <div class="form__row row">
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="checkbox">
                            <input v-model="user.promo_email" :value="user.promo_email" type="checkbox" name="checkbox">
                            <span class="checkbox__box">
									<svg>
										<use href="/assets/img/common/check_sprite.svg#check_1"></use>
									</svg>
								</span>
                            <span class="checkbox__caption">
									Receive promos by email
								</span>
                        </label>
                    </div>
                    <div class="form__item-wrap form__item-wrap_mb col-6 col-sm-12">
                        <label class="checkbox">
                            <input v-model="user.promo_sms" :value="user.promo_sms" type="checkbox" name="checkbox">
                            <span class="checkbox__box">
									<svg>
										<use href="/assets/img/common/check_sprite.svg#check_1"></use>
									</svg>
								</span>
                            <span class="checkbox__caption">
									Receive promos by SMS
								</span>
                        </label>
                    </div>
                </div>
                <button class="btn btn-xl btn_gradient" type="submit">Save all changes</button>
            </form>
        </section>
    </div>
</template>

<script>
import ProfileComponent from "../../../components/form/ProfileComponent";
import {required, minLength, email, maxValue, minValue} from '@vuelidate/validators'
import useVuelidate from "@vuelidate/core";
import {merge} from "lodash";

export default {
    name: "GeneralComponent",
    async created() {
        this.user.promo_sms = Boolean(this.user.promo_sms)
        this.user.promo_email = Boolean(this.user.promo_email)
        if (this.user.dob) {
            const Dob = new Date(this.user.dob)
            this.dob.d = Dob.getUTCDate();
            this.dob.m = (Dob.getUTCMonth() + 1);
            this.dob.y = Dob.getUTCFullYear();
        }
        await this.$store.dispatch('data/countries');
        this.countries = await this.$store.state.data.countries
    },
    data() {
        return {
            v$: useVuelidate(),
            dob: {
                d: '',
                m: '',
                y: '',
            },
            countries: [],
            submitted: false,
            gender: [
                {id: 0, text: 'Choosed'},
                {id: 1, text: 'Male'},
                {id: 2, text: 'Female'},
            ],
            serverValidation: {},
            clientValidation: {
                user: {
                    email: {required, email},
                    firstname: {required},
                    lastname: {required},
                },
              dob: {
                d: {maxValue: maxValue(31), minValue: minValue(1)},
                m: {maxValue: maxValue(12), minValue: minValue(1)},
                y: {maxValue: maxValue(2022), minValue: minValue(1900)},
              }
            },
        }
    },
    methods: {
        async update_profile() {
            this.v$.$touch();
            this.submitted = true;
            this.clearServerErrors();

            await this.$store.dispatch('user/changeUser', {user: this.user, dob: this.dob}).then(
                response => {
                    if (response.errors && response.error_keys) {
                        const serverMessages = {
                            serverErrors: {
                                "user.email": [],
                                'user.gender': [],
                                'user.firstname': [],
                                'user.lastname': [],
                                dob: [],
                                'dob.d': [],
                                'dob.m': [],
                                'dob.y': [],
                                'user.country_id': [],
                                'user.city': [],
                                'user.address': [],
                                'user.postal_code': [],
                                'user.phone': [],
                                'user.promo_email': [],
                                'user.promo_sms': [],
                            }
                        };

                        response.error_keys.forEach((el, idx) => {
                            serverMessages.serverErrors[el].push(response.errors[idx])
                        })

                        merge(this.user, serverMessages);

                        const serverError = function (fieldName) {
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
                        this.serverValidation.user = errors
                    }else {
                      this.$toast.success(`Profile updated`, {
                        position:
                            "top-right",
                      });
                    }
                }
            )
        },
        setGender(model) {
            this.user.gender = Number(model);
        },
        setCountry(model){
            this.user.country_id = Number(model);
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
    },
    computed: {
        rules() {
            return merge({}, this.serverValidation, this.clientValidation);
        },
        user(){
            return this.$store.state.user.user || []
        }
    },
    validations() {
        return this.rules;
    },
    components: {
        ProfileComponent
    }
}
</script>

<style scoped>

</style>
