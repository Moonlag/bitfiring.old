<template>
  <div @click.stop class="popup fancybox-content" style="display: inline-block">
    <div class="profile-card profile-card_popup-main profile-card">
      <slot></slot>

      <div class="profile-card__header justify_center mb-28">
        <h2 class="profile-card__title profile-card__title_lg">
          {{$t('pop_sign_in_title')}}
        </h2>
      </div>

      <div v-if="v$.user.$model.serverErrors" class="error_container">
        <div v-for="(error, idx) in v$.user.$model.serverErrors">
          <p v-if="idx === 'email'">{{ error.join() }}</p>
        </div>
      </div>

      <form class="form" id="login_form" action="/auth" method="post">
        <div class="row mb-24">
          <div class="form__item form__item-main col">
            <input v-model="user.email" type="text" :placeholder="$t('pop_sign_in_email')" id="email" name="email"
                   :class="[{ 'error': submitted && v$.user.email.$error}, { 'valid': submitted && !v$.user.email.$error},]"
                   @input="clearServerError(v$.user.$model, 'email')"
                   required
            >
            <div class="form__item-badge">
              <svg>
                <use href="/assets/img/icons/icons.svg#badge_email"></use>
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
            <Password :password="user.password"
                      @update:password="user.password = $event"
                      :error="submitted && v$.user.password.$error"
                      :valid="submitted && !v$.user.password.$error"
                      :label="$t('pop_sign_in_password')"
                      @input="clearServerError(v$.user.$model, 'password')"
            />
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
            <div class="form__item-border form__item-border_error"></div>
          </div>
        </div>
        <div class="d-flex mb-24">
          <div class="col"></div>
          <a @click="$store.commit('open_pop', 3)" href="javascript:;" data-fancybox data-src="#forgot"
             class="text text_primary text_unlerline text-height-24">
              {{$t('pop_sign_in_forgot')}}
          </a>
        </div>
        <div class="flex_center mb-30">
          <button @click.prevent="onSubmit" class="btn btn-primary btn-block btn-xl" :disabled="btn_disabled">
            {{ btn_disabled ? $t('pop_sign_in_wait') : $t('pop_sign_in_button') }}
          </button>
        </div>
        <div class="popup__bottom text text_primary text-height-24">
           {{$t('pop_sign_in_not_registered')}}
          <a @click="$store.commit('open_pop', 1)" class="text text_primary text-height-24 text_unlerline"
             href="javascript:;" data-fancybox
             data-src="#registration_1">{{$t('pop_sign_in_create_account')}}</a>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {required, minLength, email} from '@vuelidate/validators'
import useVuelidate from "@vuelidate/core";
import {merge} from "lodash";
import Password from '../../../components/form/Password';

export default {
  name: "AppLogin",
  data() {
    return {
      v$: useVuelidate(),
      user: {
        email: '',
        password: '',
      },
      serverValidation: {},
      btn_disabled: false,
      clientValidation: {
        user: {
          email: {required, email},
          password: {
            required, minLength: minLength(3),
          },
        }
      },
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
    async onSubmit(e) {
      this.submitted = true;
      this.btn_disabled = true;
      this.v$.$touch();
      this.clearServerErrors();

      this.$store.dispatch('user/login', {
        ...this.user, viewport: {
          w: window.innerWidth, h: window.innerHeight
        }
      }).then(async response => {
        console.log(response)
        if (response.banned) {
          this.close_pop()
          this.$store.commit('open_pop', 9)
          return
        }

        if (response.success && !response.banned) {
          this.close_pop()
          this.$socket.emit('logout', {success: 1})
          await this.$store.commit('user/wallets/setWallets', response.user.wallets)
          await this.$store.commit('user/wallets/setBonuses', response.user.bonuses)
        }

        this.btn_disabled = false;
      })
          .catch((e) => {
        console.log(e)
        let error_keys = ['email', 'password'];
        let message = 'Email and or password are incorrect';
        const serverMessages = {
          serverErrors: {email: [], password: []}
        };

        serverMessages.serverErrors['email'].push(message)


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
        error_keys.forEach(el => {
          errors[el] = {
            serverError: serverError(el, false)
          }
        })
        this.serverValidation.user = errors

        this.btn_disabled = false;
      })
    },
    removeProp(obj, propName) {
      for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
          if (p === propName) {
            delete obj[p];
          } else if (typeof obj[p] === "object") {
            removeProp(obj[p], propName);
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
  components: {
    Password
  }
}
</script>

<style scoped>

</style>
