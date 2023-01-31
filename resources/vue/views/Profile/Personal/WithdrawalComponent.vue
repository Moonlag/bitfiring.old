<template>
  <div class="tab__body profile-tabs__body">
    <section class="profile-block wallet-dash">
      <div class="row">
        <!-- Wallet aside -->
        <div class="wallet-dash__aside">
          <div class="wallet-dash__block wallet-dash__block_brodered">
            <!-- Wallet ballance -->
            <div class="wallet-dash__ballance wallet-ballance withdraw_form">
              <div v-if="paymentSystem.length" class="row wallet-ballance__row">
                <div class="wallet-ballance__text"> Current Balance</div>
                <div class="wallet-ballance__value">
                                    <span> <animated-balance :fix="current_balance.code === 'USDT' ? 2 : 8"
                                                             :value="current_balance.balance ?? 0"
                                                             :animated="false"></animated-balance> <span>
                                        {{
                                        current_balance.code
                                      }}</span></span>
                </div>
              </div>
              <div class="row wallet-ballance__row">
                <div class="wallet-ballance__text"> Locked by bonus</div>
                <div class="wallet-ballance__value">
                  <animated-balance fix="2"
                                    :value="withdraw_locked" :animated="false"></animated-balance>
                  USDT
                </div>
              </div>
              <div class="row wallet-ballance__row">
                <div class="wallet-ballance__text"> Withdrawable</div>
                <div class="wallet-ballance__value">
                                    <span> <animated-balance :fix="2"
                                                             :value="withdraw_balance"></animated-balance></span> <span>
                                    USDT
                                </span>
                </div>
              </div>
            </div>
            <!-- /Wallet ballance -->
          </div>
          <payment-system-component :pending="false"
                                    :type="2"
                                    :payment-system="paymentSystemFilter"
                                    @onPaymentSystem="onActiveClass"
                                    :data="withdrawal"></payment-system-component>
        </div>
        <!-- /Wallet aside -->
        <!-- Wallet info  -->
        <div class="wallet-info">
          <!-- Wallet form -->
          <form @submit.prevent="onSubmit" class="wallet-info__form wallet-form form"
                id="withdraw_form" method="post"
                action="/a/withdraw">

            <div class="error_container"></div>
            <input type="hidden" name="payment_system_id" value="1"/>
            <input type="hidden" name="currency_id" value="1"/>
            <input type="hidden" name="wallet_id" :value="withdrawal.wallet.id"/>

            <div class="form__item-wrap form__item-wrap_mb">
              <div class="row form__row wallet-form__radios withdraw_form">
                <label class="wallet-form__radio col deposit_sum_radio ">
                  <input @change="changeAmount" v-model="withdrawal.amount" type="radio"
                         checked="checked"
                         name="withdraw_sum" value="25">
                  <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code">25 USDT</span>
											</span>
										</span>
                </label>
                <label class="wallet-form__radio col deposit_sum_radio">
                  <input @change="changeAmount" v-model="withdrawal.amount" type="radio"
                         name="withdraw_sum" value="50">
                  <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code">50 USDT</span>
											</span>
										</span>
                </label>
                <label class="wallet-form__radio col deposit_sum_radio">
                  <input @change="changeAmount" v-model="withdrawal.amount" type="radio"
                         name="withdraw_sum" value="100">
                  <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code">100 USDT</span>
											</span>
										</span>
                </label>
                <label class="wallet-form__radio col deposit_sum_radio">
                  <input @change="changeAmount" v-model="withdrawal.amount" type="radio"
                         name="withdraw_sum" value="200">
                  <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code">200 USDT</span>
											</span>
										</span>
                </label>
                <label class="wallet-form__radio col deposit_sum_radio">
                  <input @change="changeAmount" v-model="withdrawal.amount" type="radio"
                         name="withdraw_sum" value="500">
                  <span class="checkbox__box">
											<span class="checkbox__caption">
												<span class="currency_code">500 USDT</span>
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
                        class="form__item  form-item_left form__badge form__badge_primary col-auto">
                      USDT
                    </div>
                    <input @input="changeAmount" v-model="withdrawal.amount" type="number" min="0"
                           step="0.01"
                           placeholder="Custom amount"
                           id="amount"
                           name="amount"
                           aria-label="amount">
                  </div>
                </div>
                <div class="deposit_change">
                  <span>=</span>
                </div>
                <div class="col col-sm-12">
                  <div class="form__item form-item_right d-flex">
                    <div
                        class="form__item form-item_left form__badge form__badge_primary col-auto">
                      {{ currenPs.code ?? '' }}
                    </div>
                    <input @input="changeTotal" v-model="withdrawal.total" type="number"
                           placeholder="Custom amount"
                           min="0"
                           :step="currenPs.id !== 12 && currenPs.id !== 13 ? 0.00000001 : 0.01"
                           id="cypto-form"
                           name="amount"
                           aria-label="amount">
                  </div>
                </div>
              </div>
            </div>
            <div v-if="childrenPs" class="form__row mb-32 mb-md-24">
              <div class="form__item-wrap col">
                <div class="d-flex">
                  <div class="form__item form-item_left col">
                    <ps-select @select:value="ps_select = $event" :vmodel="ps_select"
                               :options="childrenPs"/>
                  </div>
                </div>
              </div>
            </div>
            <div class="form__row form__item-wrap">
              <div class="form__item form-item_right d-flex">
                <div
                    class="form__item form-item_left form__badge form__badge_primary col-auto">
                  Address
                </div>
                <input v-model="withdrawal.address" type="text"
                       name="crypto"
                       aria-label="amount">

              </div>
            </div>
            <div class="form__item-wrap form__row">
              <div class="row justify_center">
                <button class="btn btn_lg btn_gradient" href="#" :disabled="btn_disabled">
                  {{ btn_disabled ? 'Please wait..' : 'Withdraw' }}
                </button>
              </div>
            </div>
          </form>
          <!-- /Wallet form -->
        </div>
        <!-- /Wallet info  -->
      </div>
      <div v-if="withdrawals.length" class="profile-table">
        <div class="profile-table__row profile-table__head">
          <div class="profile-table__title profile-table__item"> Time</div>
          <div class="profile-table__title profile-table__item"> Type</div>
          <div class="profile-table__title profile-table__item profile-table__item_2x"> Payment Method
          </div>
          <div class="profile-table__title profile-table__item"> Status</div>
          <div class="profile-table__title profile-table__item profile-table__item_05x"> Sum</div>
          <div class="profile-table__title profile-table__item profile-table__item_01x"></div>
        </div>
        <div v-for="row in withdrawals" :key="row.id" class="profile-table__row">
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md"> Time</div>
            <time class="profile-table__text" datetime="2020-01-05 09:25">{{ row.created_at }}
            </time>
          </div>
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md">Type</div>
            <div class="profile-table__text">Withdraw</div>
          </div>
          <div class="profile-table__item profile-table__item_2x">
            <div class="profile-table__title profile-table__md"> Payment Method</div>
            <div class="profile-table__text">{{ row.ps }}</div>
          </div>
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md"> Status</div>
            <div class="profile-table__text">
              <div>
                <div
                    :class="[{'mark-dot_error': row.status === 4 || row.status === 3}, {'mark-dot_success': row.status === 1}, {'mark-dot_warning': row.status === 2 || row.status === 5}]"
                    class="mark-dot">
                </div>
                {{ $options.status[row.status] }}
              </div>

            </div>
          </div>
          <div class="profile-table__item profile-table__item_05x">
            <div class="profile-table__title profile-table__md"> Sum</div>
            <div class="profile-table__text">
              <animated-balance :fix="row.currency_id === 14 ? 2 : 8" :value="row.amount"
                                :animated="false"/>
            </div>
          </div>
          <div class="profile-table__item profile-table__item_01x">
            <div class="profile-table__title profile-table__md"></div>
            <div style="min-width: 90px" class="profile-table__text">
              <a v-if="row.status === 2" class="btn btn_gradient" @click.prevent="reject_with({id: row.id})"
                 href="javascript:void(0);">
                Cancel
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import WalletComponent from "../../../components/form/WalletComponent";
import PaymentSystemComponent from "../../../components/ps/PaymentSystemComponent";
import AnimatedBalance from "../../../components/animate/AnimatedBalance";
import PsSelect from "../../../components/form/PsSelect";
import axios from "axios";

export default {
  name: "WithdrawalComponent",
  status: {
    1: 'Approved',
    2: 'Pending',
    3: 'Rejected',
    4: 'Error',
    5: 'In Progress',
  },
  async created() {
    await this.getCurrentPs();

    if (this.paymentSystemFilter.length) {
      let active = this.paymentSystemFilter[0]
      if(active){
        this.withdrawal.payment_system_id = active.id || 0
        this.withdrawal.wallet = this.wallets.find(el => el.currency_id === 14)
        // this.changeTotal();
        this.changeAmount();
        console.log(this.withdrawal)
      }
    }else {
      this.withdrawal.wallet = this.primary_wallet
      this.withdrawal.payment_system_id = this.paymentSystem.find(el => el.currency_id === this.primary_wallet.currency_id).id
    }
  },
  async beforeRouteEnter(to, from, next) {
    let {data} = await axios.post("/a/user/withdraws")
    next(vm => vm.setData(null, data));
  },
  data() {
    return {
      current_ps: [],
      btn_disabled: false,
      ps_select: null,
      withdrawals: [],
      withdrawal: {
        wallet: '',
        amount: 25,
        total: 25,
        payment_system_id: 0,
        address: ''
      },
      onDisabled: false
    }
  },
  computed: {
    currenPs() {
      return this.paymentSystem.find(el => el.id === this.withdrawal.payment_system_id) || [];
    },
    childrenPs() {
      if (this.currenPs.id !== 12) {
        return null
      }
      return this.currenPs.children.map(el => {
        el.text = el.name;
        return el
      })
    },
    wallets() {
      return this.$store.getters['user/wallets/wallets'] || []
    },
    paymentSystem() {
      return this.$store.getters['data/payment_system'] || []
    },
    paymentSystemFilter() {
      return this.paymentSystem.filter(el => this.current_ps.some(ps => el.currency_id === ps))
    },
    current_balance() {
      return this.wallets.find(el => el.currency_id === 14)
    },
    primary_wallet() {
      return this.$store.getters['user/wallets/primary_wallet'] || []
    },
    locked_balance() {
      return this.$store.getters['user/wallets/locked_balance'];
    },
    withdraw_balance() {
      return this.$store.getters['user/wallets/withdrawable_balance'];
    },
    withdraw_locked() {
      return (Number(this.locked_balance)).toFixed(8) || 0
    },
  },
  methods: {
    async reject_with({id}) {
      let submit = await this.$store.dispatch('rejectWithdraw')
      if (submit) {
        this.$api.transaction.cancelWithdrawal({id: id}).then(response => {
          if (response.data.success) {
            this.withdrawals.find(el => el.id === id).status = 3
            this.$store.commit('close_pop')
            this.$toast.success(`Withdrawal canceled`, {
              position: "top-right",
              duration: false
            });
          }
        })
      }
    },
    onActiveClass(el) {
      if (el.active) {
        this.withdrawal.wallet = this.wallets.find(wl => wl.currency_id === el.currency_id) ?? ''
        this.withdrawal.payment_system_id = el.id
        this.withdrawal.total = this.withdrawal.wallet.withdrawable
        this.changeTotal();
        this.changeAmount();
      }
    },
    changeTotal() {
      if (this.currenPs) {
        this.withdrawal.amount = (Number(this.withdrawal.total) / Number(this.currenPs.rate)).toFixed(2)
      }
    },
    changeAmount() {
      if (this.currenPs) {
        if (this.currenPs.id === 12 || this.currenPs.id === 13) {
          this.withdrawal.total = (Number(this.withdrawal.amount) * Number(this.currenPs.rate)).toFixed(2)
          return
        }
        this.withdrawal.total = (Number(this.withdrawal.amount) * Number(this.currenPs.rate)).toFixed(8)
      }
    },
    async onSubmit() {
      if (!this.ps_select && this.withdrawal.payment_system_id === 12) {
        this.$toast.error(`Select Withdrawal network`, {
          position: "top-right",
        });
        return
      }

      if (!this.withdrawal.address) {
        this.$toast.error(`Address empty`, {
          position: "top-right",
        });
        return
      }

      if (!this.withdrawal.amount && this.withdrawal.amount <= 0 && !this.withdrawal.total && this.withdrawal.total <= 0) {
        this.$store.commit('open_withdrawal_cancel')
        return
      }

      // if (Number(this.currenPs.minimum) * Number(this.currenPs.rate) > this.withdrawal.total) {
      //     this.$toast.error(`Amount is too small`, {
      //         position: "top-right",
      //     });
      //     return
      // }
      let withdrawal = JSON.parse(JSON.stringify(this.withdrawal))
      withdrawal.payment_system_id = this.ps_select || withdrawal.payment_system_id

      await this.$store.dispatch('approveWithdraw', {amount: this.withdrawal.total, ps: withdrawal.payment_system_id})
      this.$store.commit('close_pop')

      this.btn_disabled = true

      this.$api.transaction.withdrawal(withdrawal).then(
          async response => {
            if (response.data.success) {
              this.withdrawal.amount = 25;
              this.withdrawal.address = '';
              this.changeAmount();
              this.btn_disabled = false
              let {data} = await axios.post("/a/user/withdraws")
              await this.setData(null, data)
            }
          }
      ).catch(
          error => {
            if (error.response.data.errors) {
              error.response.data.errors.forEach(el => {
                this.$toast.error(el, {
                  position: "top-right",
                });
                this.btn_disabled = false
              })
            }
          }
      )
    },
    async setData(err, data) {
      if (data.success) {
        this.withdrawals = data.withdrawals
      }
    },
    getCurrentPs(){
      return new Promise(async (resolve) => {
        const {data} = await axios.post('/a/current_get_ps')
        if(data && data?.payment_system.length){
          this.current_ps = data?.payment_system
        }else {
          this.current_ps = [14];
        }
        resolve(this.current_ps)
      })
    }
  },
  components: {
    WalletComponent,
    PaymentSystemComponent,
    AnimatedBalance,
    PsSelect
  }
}
</script>

<style scoped>
.ballance__text {
  margin-top: 5px;

  color: #B18CE2;
  font-family: "Raleway", sans-serif;
  font-size: 12px;
  font-weight: 400;
  line-height: 24px;
}
</style>
