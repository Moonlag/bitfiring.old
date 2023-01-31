<template>
  <div @click.stop class="popup fancybox-content create" style=" display: inline-block;">
    <slot></slot>
    <div class="popup-card">
      <div class="popup-card__main">
        <div class="popup-card__bonus">
          <div class="popup-card__bonus__header">
            <h2 class="popup-card__bonus__header-title">
                {{$t('pop_sign_up_title')}}
            </h2>
          </div>
          <div class="popup-card__bonus__body">
            <picture>
              <source :srcset="`/assets/img/popup/img.webp`" type="image/webp">
              <img :src="`/assets/img/popup/img.webp`" alt="img">
            </picture>
            <h3 class="popup-card__bonus__body-title" v-html="$t('pop_sign_up_welcome_bonus')">
            </h3>
          </div>
        </div>

      </div>
      <div class="popup-card__main">
        <div class="popup-card__header">
          <h2 class="popup-card__header-title">
              {{$t('pop_sign_up_sub_title') }}
          </h2>
          <p class="popup-card__header-description">
              {{$t('pop_sign_up_sub_description')}}
          </p>
        </div>

        <div v-if="v$.user.$model.serverErrors" class="error_container">
          <div v-for="error in v$.user.$model.serverErrors">
            <p v-if="error.join()">
              {{ error.join() }}
            </p>
          </div>

        </div>
        <form class="form" action="/join_first" method="post" ref="form">

          <div class="row mb-8">

            <div class="form__item form__item-main col">
              <input v-model="user.email" type="text" :placeholder="$t('pop_sign_up_email')" id="email" name="email"
                     :class="[{ 'error': submitted && v$.user.email.$error}, { 'valid': submitted && !v$.user.email.$error},]"
                     data-msg="required"
                     aria-label="password"
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
                  <img src="/assets/img/common/error-sign.svg" alt="error"></picture>
              </div>

              <div class="form__item-sign form__item-sign_success">
                <picture>
                  <source srcset="/assets/img/common/succes-sign.svg" type="image/webp">
                  <img src="/assets/img/common/succes-sign.svg" alt="success"></picture>
              </div>

              <div class="form__item-border"></div>
            </div>

          </div>
          <div class="row mb-8">
            <div class="form__item form__item-main col">
              <Password :password="user.password"
                        @update:password="user.password = $event"
                        :error="submitted && v$.user.password.$error"
                        :valid="submitted && !v$.user.password.$error"
                        :label="$t('pop_sign_up_password')"
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
        </form>
        <div class="popup-card__item-sign">
          <label class="popup-card__check">
            <input
                type="checkbox"
                v-model="user.condition"
                id="conditions"
                required>
            <span class="checkbox__box">                                                        <svg>                                                            <use
                href="/assets/img/common/check_sprite.svg#check_1"></use>                                                        </svg>                                                    </span>
          </label><label class="popup-card__conditions" for="conditions">
          I’m 18 years old and I accept <br/> the <a href="javascript:;"
                                                     @click="openLink('terms')"
                                                     class="text text_primary text-height-20 text_unlerline">Terms
          and Conditions</a> and <a href="javascript:;" @click="openLink('policy')"
                                    class="text text_primary text-height-20 text_unlerline">Privacy
          Policy</a></label>
        </div>
        <div class="popup-card__item">
          <label class="popup-card__check">
            <input
                type="checkbox"
                v-model="user.promo"
                id="promo"
                required>
            <span class="checkbox__box">                                                        <svg>                                                            <use
                href="/assets/img/common/check_sprite.svg#check_1"></use>                                                        </svg>                                                    </span>
          </label><label class="popup-card__conditions" for="promo">{{$t('pop_sign_up_promo')}}</label>
        </div>
        <div class="flex_center">
          <button @click.prevent="onSubmit" class="btn popup-card__btn" :disabled="btn_disabled">
            {{ btn_disabled ? $t('pop_sign_up_wait') : $t('pop_sign_up_button') }}
          </button>
        </div>
        <div class="popup__bottom text text_primary text-height-24">
            {{$t('pop_sign_up_already')}}
          <a @click="$store.commit('open_pop', 2)" class="text text_primary text-height-24 text_unlerline"
             href="javascript:;" data-fancybox
             data-src="#registration_1">{{$t('pop_sign_up_sign_in')}}</a>
        </div>
      </div>

    </div>
  </div>

</template>

<script>
import useVuelidate from '@vuelidate/core'
import {required, minLength, email} from '@vuelidate/validators'
import {merge} from "lodash"
import querystring from "querystring"
import Password from '../../../components/form/Password';

export default {
  name: "AppRegistration",
  created() {
    this.user.affiliate_aid = this.$cookie.getCookie('aid') || null;
    this.user.query_string = this.$cookie.getCookie('query_sting') || null;
    this.user.lucky = localStorage.getItem("sessionID") || false;
  },
  data() {
    return {
      title: 'Registration',
      v$: useVuelidate(),
      user: {
        email: '',
        password: '',
        affiliate_aid: '',
        query_string: '',
        condition: false,
        promo: false,
        lucky: false
      },
      btn_disabled: false,
      submitted: false,
      serverValidation: {},
      clientValidation: {
        user: {
          email: {required, email},
          password: {
            required, minLength: minLength(3),
          },
        },
      },
    }
  },
  computed: {
    rules() {
      return merge({}, this.serverValidation, this.clientValidation);
    },
    ip_info() {
      return this.$store.state.data.ip_info || null
    },
    home() {
      return this.$store.state.data.home || null
    },
  },
  validations() {
    return this.rules;
  },
  methods: {
    openLink(name) {
      let routeData = this.$router.resolve({name: 'static', params: {static: name}});
      window.open(routeData.href, '_blank');
    },
    async onSubmit(e) {
      console.log(this.user)
      this.submitted = true;
      this.btn_disabled = true;
      this.v$.$touch();

      this.clearServerErrors();

      const response = await this.$store.dispatch('user/register', {
        ...this.user, viewport: {
          w: window.innerWidth,
          h: window.innerHeight,
        },
        prize: this.home.prize || null
      }).then(response => {
        this.btn_disabled = false;
        console.log(response.data)
        if (response.data.success) {
          this.$store.commit('close_pop')
          this.$store.commit('user/loginSuccess', response.data)
          this.$store.commit('user/wallets/setWallets', response.data.wallets)
          this.$store.commit('user/wallets/setBonuses', [])
        }
        return response

      }).catch(e => {
        this.btn_disabled = false;
      })

      if (response.data.errors && response.data.error_keys) {

        const serverMessages = {
          serverErrors: {email: [], password: [], condition: []}
        };


        response.data.error_keys.forEach((el, idx) => {
          serverMessages.serverErrors[el].push(response.data.errors[idx])
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
        response.data.error_keys.forEach(el => {
          errors[el] = {
            serverError: serverError(el, false)
          }
        })
        this.serverValidation.user = errors
      }
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
  components: {
    Password
  }
}
</script>

<style scoped lang="scss">
$md3: 767.98px;

.btn-disabled {
  opacity: 0.6;
  cursor: default;
}

.popup {
  max-width: 830px;
  width: 100%;

  @media all and (max-width: $md3) {
    max-width: 100vh;
    padding: 0
  }
}

.popup-card {
  max-width: 800px;
  min-height: 603px;
  width: 100%;
  height: 100%;
  padding: 0;
  flex-direction: row;
  display: flex;

  @media all and (max-width: $md3) {
    flex-direction: column;
    max-width: 100vw;
    min-height: 100vh;
  }

  &:not(.profile-card_popup-main) {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.16);
  }

  box-sizing: border-box;
  border-radius: 4px;

  &__main {
    flex: 50%;
    backdrop-filter: blur(16px);
    background: linear-gradient(99.8deg, rgba(130, 13, 163, 0.85) 14.73%, rgba(68, 27, 155, 0.85) 88.93%);
    padding: 60px 40px;
    display: flex;
    justify-content: center;
    flex-direction: column;
  }

  &__main:first-child {
    padding: 0;
    @media all and (max-width: $md3) {
      max-height: 300px;
      flex: 30%;
    }
  }

  &__main:last-child {
    @media all and (max-width: $md3) {
      padding: 30px 20px;
      flex: 70%;
    }
  }

  &__header {
    display: flex;
    flex-direction: column;
    margin-bottom: 24px;

    &-title {
      color: #ffffff;
      font-family: 'Raleway', sans-serif;
      font-size: 31px;
      font-weight: 700;
      line-height: 1.33;
      text-align: center;
    }

    &-description {
      color: #ffffff;
      font-family: 'Raleway', sans-serif;
      font-weight: 500;
      font-size: 15px;
      line-height: 24px;
      text-align: center;
    }
  }

  &__btn {
    width: 100%;
    background: linear-gradient(135deg, #FF6A28 0.06%, #FE2F57 99.45%);
    border-radius: 4px;

    font-family: 'Raleway', sans-serif;
    font-style: normal;
    font-weight: 700;
    font-size: 14px;
    line-height: 24px;
    padding: 12px 0;
  }

  &__check {
    cursor: pointer;
    display: flex;
    align-items: center;

    & input {
      position: absolute;
      opacity: 0;
      cursor: pointer;
      height: 0;
      width: 0;
      z-index: -1;
      display: block;

      &:checked ~ .checkbox__box {
        background-color: #DC5EFF;
        pointer-events: none;
        border-color: #DC5EFF;

        svg {
          fill: #fff;
        }
      }
    }

    & .checkbox__box {
      display: flex;
      align-items: center;
      justify-content: center;

      transition: .3s;
      min-height: 24px;
      min-width: 24px;
      height: 24px;
      width: 24px;
      background-color: transparent;
      border: 2px solid #fff;
      border-radius: 3px;
      margin-right: 14px;

      svg {
        transition: .3s;
        width: 13px;
        height: 13px;
        fill: transparent;
      }
    }

  }

  &__conditions {
    font-family: 'Raleway', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 13px;
    line-height: 16px;
    color: white;
  }

  &__item-sign {
    display: flex;
    margin-top: 60px;
    margin-bottom: 24px;
    @media all and (max-width: $md3) {
      margin-bottom: 8px
    }
  }

  &__item {
    display: flex;
    margin-bottom: 24px;
    @media all and (max-width: $md3) {
      margin-bottom: 8px
    }
  }

}

.popup-card__bonus {
  padding: 40px 20px;
  width: 100%;
  height: 100%;

  @media all and (max-width: $md3) {
    padding: 20px
  }

  &__header {
    display: flex;
    justify-content: center;

    &-title {
      font-family: 'Raleway';
      font-style: normal;
      font-weight: 900;
      font-size: 80px;
      line-height: 130%;
      /* identical to box height, or 104px */

      text-align: center;
      background: linear-gradient(315deg, #EB3800 0%, #F99705 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      color: #EB3800;
      display: table;

      @media all and (max-width: $md3) {
        font-size: 40px
      }
    }
  }

  &__body {
    background-image: url("../assets/img/popup/congrats.webp");
    background-position: center;
    background-repeat: no-repeat;
    background-size: 97%;
    padding-top: 40px;
    height: 87%;
    display: flex;
    flex-direction: column;
    align-items: center;

    @media all and (max-width: $md3) {
      padding-top: 20px;
      height: 83%;
    }

    picture {
      margin-bottom: 53px;
      @media all and (max-width: $md3) {
        img {
          width: 100px;
          height: 100px;
        }
        margin-bottom: 12px;
      }
    }

    &-title {
      font-family: 'Raleway';
      font-style: normal;
      font-weight: 700;
      font-size: 32px;
      line-height: 40px;
      /* or 125% */

      text-align: center;
      color: white;

      @media all and (max-width: $md3) {
        font-size: 26px;
        line-height: 32px;
      }

    }
  }
}

.popup__bottom {
  margin-top: 30px;
  text-align: center;
  @media all and (max-width: $md3) {
    margin-top: 10px
  }
}

.form {
  display: flex;
  flex-direction: column;

  input:disabled {
    background-color: white;
  }
}

.btn-pop {
  background: linear-gradient(135deg, #FF6A28 0.06%, #FE2F57 99.45%);
}
</style>
