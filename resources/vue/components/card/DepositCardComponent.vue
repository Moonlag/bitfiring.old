<template>
  <form v-if="!pending" @submit.prevent="newDeposit" class="wallet-info__form wallet-form form"
        id="deposit_form" method="post">

    <div class="error_container"></div>

    <input type="hidden" id="deposit_option" name="payment_system_id" value="1"/>
    <input type="hidden" id="wallet_currency" name="currency_id" value="1"/>
    <input type="hidden" id="wallet_id" name="wallet_id"/>
    <div class="form__item-wrap form__item-wrap_mb">
      <div class="row form__row wallet-form__radios deposit_form">
        <label class="wallet-form__radio col deposit_sum_radio">
          <input @change="changeAmount" v-model="data.amount" type="radio" checked="checked"
                 name="deposit_sum"
                 value="25.00">
          <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code"></span>25 USDT
											</span>
										</span>
        </label>
        <label class="wallet-form__radio col deposit_sum_radio">
          <input @change="changeAmount" v-model="data.amount" type="radio" name="deposit_sum" value="50.00">
          <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code"></span>50 USDT
											</span>
										</span>
        </label>
        <label class="wallet-form__radio col deposit_sum_radio">
          <input @change="changeAmount" v-model="data.amount" type="radio" name="deposit_sum" value="100.00">
          <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code"></span>100 USDT
											</span>
										</span>
        </label>
        <label class="wallet-form__radio col deposit_sum_radio">
          <input @change="changeAmount" v-model="data.amount" type="radio" name="deposit_sum" value="200.00">
          <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code"></span>200 USDT
											</span>
										</span>
        </label>
        <label class="wallet-form__radio col deposit_sum_radio">
          <input @change="changeAmount" v-model="data.amount" type="radio" name="deposit_sum" value="500.00">
          <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code"></span>500 USDT
											</span>
										</span>
        </label>
      </div>
    </div>
    <div class="form__row form__item-wrap">
      <div class="row">
        <div class="col col-sm-12 form__item-deposit">
          <div class="form__item form-item_right d-flex">
            <div
                class="form__item form-item_left form__badge form__badge_primary col-auto">
              {{ ps.code ?? '' }}
            </div>
            <input @input="changeTotal" v-model="data.total" type="number" placeholder="Custom amount"
                   min="0" :step="ps.id !== 12 && ps.id !== 13 ? 0.00000001 : 0.01" id="cypto-form"
                   name="amount"
                   aria-label="amount">
          </div>
        </div>
        <div class="deposit_change">
          <span>=</span>
        </div>
        <div class="col col-sm-12">
          <div class="form__item form-item_right d-flex">
            <div class="form__item form-item_left form__badge form__badge_primary col-auto">
              USDT
            </div>
            <input @input="changeAmount" v-model="data.amount" type="number" min="0" step="0.01"
                   placeholder="Custom amount"
                   id="amount"
                   name="amount"
                   aria-label="amount">
          </div>
        </div>
      </div>
    </div>
    <div v-if="children_ps" class="form__row mb-32 mb-md-24">
      <div class="form__item-wrap col">
        <div class="d-flex">
          <div class="form__item form-item_left col">
            <ps-select @select:value="onSelect" :vmodel="ps_select" :options="children_ps"/>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-32 mb-md-16 mb-sm-24 bonuses_wrap">
      <div v-for="bonus in bonuses" class="col-6 col-sm-12 p-8">
        <div class="profile-card bonuses profile-card_wallet">
          <div class="profile-card__title mb-4">
            <label class="profile-card__check">
              <input
                  type="radio"
                  name="checkbox"
                  v-model="bonus.active"
                  :checked="bonus.active"
                  @change="activeBonuses(bonus.id)"
              > <span
                class="radio__box"></span>
            </label>
            <h2 class="bonuses-title">{{ bonus.title }}</h2>
            <svg v-if="bonus.id === 0" width="80" height="92" viewBox="0 0 80 92" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
              <path
                  d="M40 14C33.671 14 27.4841 15.8768 22.2218 19.393C16.9594 22.9092 12.8579 27.9069 10.4359 33.7541C8.01387 39.6014 7.38016 46.0355 8.61489 52.2429C9.84961 58.4503 12.8973 64.1521 17.3726 68.6274C21.8479 73.1027 27.5497 76.1504 33.7571 77.3851C39.9645 78.6198 46.3986 77.9861 52.2459 75.5641C58.0931 73.1421 63.0908 69.0406 66.607 63.7782C70.1232 58.5159 72 52.329 72 46C72 37.5131 68.6286 29.3737 62.6274 23.3726C56.6263 17.3714 48.4869 14 40 14V14ZM40 18.2667C46.6155 18.278 53.0091 20.6539 58.0267 24.9653L18.9653 64.0267C15.5216 60.0005 13.3032 55.0722 12.5725 49.8249C11.8418 44.5775 12.6295 39.2306 14.8422 34.4168C17.055 29.6031 20.6003 25.5238 25.0587 22.6617C29.5171 19.7996 34.702 18.2745 40 18.2667V18.2667ZM40 73.7333C33.3845 73.7219 26.991 71.3461 21.9733 67.0347L61.0347 27.9733C64.4784 31.9995 66.6968 36.9277 67.4275 42.1751C68.1582 47.4225 67.3705 52.7694 65.1578 57.5832C62.945 62.3969 59.3997 66.4762 54.9413 69.3383C50.4829 72.2004 45.298 73.7255 40 73.7333Z"
                  fill="#383448"/>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="80" height="92" viewBox="0 0 80 92" fill="none">
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M40 16.56C23.7407 16.56 10.56 29.7407 10.56 46C10.56 62.2593 23.7407 75.44 40 75.44C56.2593 75.44 69.44 62.2593 69.44 46C69.44 29.7407 56.2593 16.56 40 16.56ZM8 46C8 28.3269 22.3269 14 40 14C57.6731 14 72 28.3269 72 46C72 63.6731 57.6731 78 40 78C22.3269 78 8 63.6731 8 46Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M40 29.36C30.81 29.36 23.36 36.81 23.36 46C23.36 55.19 30.81 62.64 40 62.64C49.19 62.64 56.64 55.19 56.64 46C56.64 36.81 49.19 29.36 40 29.36ZM20.8 46C20.8 35.3961 29.3961 26.8 40 26.8C50.6039 26.8 59.2 35.3961 59.2 46C59.2 56.6039 50.6039 65.2 40 65.2C29.3961 65.2 20.8 56.6039 20.8 46Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M40 14C40.7069 14 41.28 14.5731 41.28 15.28V28.08C41.28 28.7869 40.7069 29.36 40 29.36C39.2931 29.36 38.72 28.7869 38.72 28.08V15.28C38.72 14.5731 39.2931 14 40 14Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M17.3727 23.3727C17.8725 22.8728 18.683 22.8728 19.1829 23.3727L28.2338 32.4235C28.7336 32.9234 28.7336 33.7339 28.2338 34.2337C27.7339 34.7336 26.9234 34.7336 26.4236 34.2337L17.3727 25.1829C16.8728 24.683 16.8728 23.8725 17.3727 23.3727Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M8 46C8 45.2931 8.57308 44.72 9.28 44.72H22.08C22.7869 44.72 23.36 45.2931 23.36 46C23.36 46.7069 22.7869 47.28 22.08 47.28H9.28C8.57308 47.28 8 46.7069 8 46Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M28.2338 57.7663C28.7336 58.2661 28.7336 59.0766 28.2337 59.5765L19.1829 68.6273C18.683 69.1272 17.8725 69.1272 17.3727 68.6273C16.8728 68.1275 16.8728 67.317 17.3727 66.8171L26.4236 57.7663C26.9234 57.2664 27.7339 57.2664 28.2338 57.7663Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M40 62.64C40.7069 62.64 41.28 63.2131 41.28 63.92V76.72C41.28 77.4269 40.7069 78 40 78C39.2931 78 38.72 77.4269 38.72 76.72V63.92C38.72 63.2131 39.2931 62.64 40 62.64Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M51.7663 57.7663C52.2661 57.2664 53.0766 57.2664 53.5764 57.7663L62.6273 66.8171C63.1272 67.317 63.1272 68.1275 62.6273 68.6273C62.1275 69.1272 61.317 69.1272 60.8171 68.6273L51.7663 59.5765C51.2664 59.0766 51.2664 58.2661 51.7663 57.7663Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M56.64 46C56.64 45.2931 57.2131 44.72 57.92 44.72H70.72C71.4269 44.72 72 45.2931 72 46C72 46.7069 71.4269 47.28 70.72 47.28H57.92C57.2131 47.28 56.64 46.7069 56.64 46Z"
                    fill="#383448"/>
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M62.6273 23.3727C63.1272 23.8725 63.1272 24.683 62.6273 25.1829L53.5765 34.2337C53.0766 34.7336 52.2661 34.7336 51.7663 34.2337C51.2664 33.7339 51.2664 32.9234 51.7663 32.4235L60.8171 23.3727C61.317 22.8728 62.1275 22.8728 62.6273 23.3727Z"
                    fill="#383448"/>
            </svg>
          </div>
          <div class="profile-card__description">
            <h4>{{ bonus.description }}</h4>
            <p v-if="bonus.wager" class="profile-card__text"> Wager: {{ bonus.wager }}</p>
            <p v-if="bonus.freespin" class="bonuses-fs">
                  {{ bonus.freespin.title }}
              </p>
          </div>
        </div>
      </div>
    </div>
    <div class="form__item-wrap form__row">
      <div class="row justify_center">
        <button v-if="data.wallet" class="btn btn_lg btn_main"
                href="javascript:;" :disabled="btn_disabled"> {{ btn_disabled ? 'Please wait..' : 'Deposit' }}
        </button>
        <button @click.prevent="addWallet" v-else class="btn btn_lg btn_main"
                href="javascript:;">Deposit
        </button>
      </div>
    </div>
  </form>
</template>

<script>

import PsSelect from "../form/PsSelect";

export default {
  name: "DepositCardComponent",
  props: ['data', 'ps', 'bonuses', 'btn_disabled', 'ps_select'],
  emits: ['addWallet', 'newDeposit', 'changeTotal', 'changeAmount', 'activeBonuses', 'activePs'],
  computed: {
    children_ps() {
      if (this.ps.id !== 12) {
        return null
      }
      return this.ps.children.map(el => {
        el.text = el.name;
        return el
      })
    }
  },
  methods: {
    addWallet() {
      this.$emit('addWallet')
    },
    newDeposit() {
      this.$emit('newDeposit')
    },
    changeTotal() {
      this.$emit('changeTotal')
    },
    changeAmount() {
      this.$emit('changeAmount', this.data.amount)
    },
    activeBonuses(id) {
      this.$emit('activeBonuses', id)
    },
    onSelect($event) {
      this.$emit('activePs', Number($event))
    }
  },
  components: {
    PsSelect
  }
}
</script>

<style scoped lang="scss">
.bonuses_wrap {
  margin: -8px;
}

.profile-card__title h2 {
  z-index: 5;
}

.profile-card__title svg {
  z-index: 1;
}

.profile-card__description{
  position: relative;
  z-index: 5;
}

.bonuses {
  position: relative;

  &-title{
    color: #ff8700;
  }

  &-fs {
    padding-top: 3px;
    font-family: Raleway, sans-serif;
    font-style: normal;
    font-weight: 800;
    font-size: 10px;
    text-transform: uppercase;
    line-height: 12px;

    color: #f89405;
  }
}
</style>
