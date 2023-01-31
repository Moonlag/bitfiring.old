<template>
  <div class="tab__body profile-tabs__body">
    <section class="profile-block wallet-dash">
      <div class="row">
        <!-- Wallet aside -->
        <div class="wallet-dash__aside">
          <div class="wallet-dash__block wallet-dash__block_brodered">
            <!-- Wallet ballance -->
            <div class="wallet-dash__ballance wallet-ballance deposit_form">
              <div class="row wallet-ballance__row">
                <div class="wallet-ballance__text"> Real balance</div>
                <div class="wallet-ballance__value"> <span> <animated-balance :fix="2"
                                                                              :value="real_balance"></animated-balance> <span
                > USDT
                                </span> </span></div>
              </div>
              <div class="row wallet-ballance__row">
                <div class="wallet-ballance__text"> Bonus balance</div>
                <div class="wallet-ballance__value"><span
                    id="wallet_code">
                                </span>
                  <animated-balance :fix="2"
                                    :value="locked_balance"></animated-balance>
                  <span
                      id="wallet_balance">
                                   USDT
                                    </span></div>
              </div>
            </div>
            <!-- /Wallet ballance -->
          </div>
          <payment-system-component :pending="pending" :type="1" :payment-system="paymentSystem"
                                    @onPaymentSystem="onPaymentSystem"
                                    :data="deposit"></payment-system-component>
        </div>
        <div class="wallet-info">

            <deposit-card-component v-if="!pending && deposit.payment_system_id !== 15" :data="deposit" @addWallet="addWallet"
                                    @newDeposit="newDeposit" @changeTotal="changeTotal"
                                    @changeAmount="changeAmount" :ps="currenPs" :bonuses="bonuses"
                                    @activeBonuses="activeBonuses" :ps_select="ps_select"
                                    :btn_disabled="btn_disabled" @activePs="activePs"></deposit-card-component>
            <ChangellyWidget :src="changelly" v-else-if="!pending && deposit.payment_system_id === 15"/>
            <new-deposit-card-component v-else :data="deposit" :ps="currenPs"
                                        @cancelDeposit="cancelDeposit"></new-deposit-card-component>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import WalletComponent from "../../../components/form/WalletComponent";
import PaymentSystemComponent from "../../../components/ps/PaymentSystemComponent";
import NewDepositCardComponent from "../../../components/card/NewDepositCardComponent";
import DepositCardComponent from "../../../components/card/DepositCardComponent";
import AnimatedBalance from "../../../components/animate/AnimatedBalance";
import ChangellyWidget from "../../../components/ps/ChangellyWidget.vue";

export default {
  name: "DepositComponent",
  components: {
    WalletComponent,
    PaymentSystemComponent,
    NewDepositCardComponent,
    DepositCardComponent,
    AnimatedBalance,
    ChangellyWidget
  },
  props: ['deposit_wallet'],
  async created() {
    await this.setBonuses()
    let wallet = '';
    if (this.deposit_wallet) {
      wallet = this.wallets.find(el => el.id === this.deposit_wallet)
    } else {
      wallet = this.wallets.find(el => el.primary === 1);
    }
    if (wallet) {
      this.deposit.wallet = wallet
      if (this.paymentSystem) {
        let active_ps = this.paymentSystem.filter(el => el.active === 1).find(el => el.currency_id === this.deposit.wallet.currency_id);
        if (active_ps) {
          this.deposit.payment_system_id = active_ps.id
        }
      }
    }

    this.changeAmount();

    this.$socket.on('new deposit', (msg) => {
      this.$api.transaction.statusDeposit().then(
          response => {
            let result = response.data
            if (result.success && result.address) {
              this.pending = true
              this.deposit.html = result.qrcode
              this.deposit.address = result.address
              this.deposit.payment_id = result.payment
              this.deposit.payment_system_id = result.payment_system_id
              this.btn_disabled = true
              this.$socket.on('channel deposit', ({success}) => {
                if (success) {
                  this.pending = false
                  this.btn_disabled = false
                  this.$socket.off("channel deposit");
                  this.setBonuses()
                }
              })
            }
          })
    })

    this.$socket.on('cancel', () => {
      this.html = ''
      this.address = ''
      this.pending = false
      this.btn_disabled = false
    })

    await this.$api.transaction.statusDeposit().then(
        response => {
          let result = response.data
          if (result.success && result.address) {
            this.pending = true
            this.deposit.html = result.qrcode
            this.deposit.address = result.address
            this.deposit.payment_id = result.payment
            this.deposit.payment_system_id = result.payment_system_id
            this.btn_disabled = true
            this.$socket.on('channel deposit', ({success}) => {
              if (success) {
                this.pending = false
                this.btn_disabled = false
                this.$socket.off("channel deposit");
                this.setBonuses()
              }
            })
          }
        })

  },
  data() {
    return {
      pending: false,
      connection: false,
      changelly: '',
      bonuses: [],
      min_def: 25,
      max_def: 5000,
      btn_disabled: false,
      ps_select: null,
      deposit: {
        wallet: '',
        amount: '25.00',
        payment_system_id: '',
        total: '',
        html: '',
        address: '',
        payment_id: '',
        bonuses: null,
        cc: false
      },
    }
  },
  computed: {
    currency() {
      return this.$store.state.data.currency
    },
    currentWallet() {
      return this.wallets.find(el => el.id === this.deposit.wallet.id) ?? []
    },
    currenPs() {
      return this.deposit.payment_system_id ? this.paymentSystem.find(el => el.id === this.deposit.payment_system_id) : [];
    },
    paymentSystem() {
      return this.$store.getters['data/payment_system'] || []
    },
    user() {
      return this.$store.state.user.user
    },
    wallets() {
      return this.$store.getters['user/wallets/wallets'] || []
    },
    locked_balance() {
      return this.$store.getters['user/wallets/locked_balance'] || []
    },
    current_balance() {
      return this.$store.getters['user/wallets/current_balance'] || []
    },
    real_balance() {
      return ((Number(this.current_balance) - Number(this.locked_balance)) > 0 ? (Number(this.current_balance) - Number(this.locked_balance)) : 0.00) || 0.00;
    }
  },
  methods: {
    async addWallet() {
      await this.newDeposit()
    },
    async newDeposit() {

      if (Number(this.deposit.amount) < this.min_def) {
        return this.$toast.error(`Minimum deposit amount is ${this.min_def}`, {
          position:
              "top-right",
        });
      }

      if (Number(this.deposit.amount) > this.max_def) {
        return this.$toast.error(`Maximum deposit amount is ${this.max_def}`, {
          position:
              "top-right",
        });
      }

      if (this.deposit.amount && this.deposit.total) {

        let bonuses = this.bonuses.find(el => el.active);
        if (bonuses.id !== 0) {
          this.deposit.bonuses = bonuses;
        }

        let deposit = JSON.parse(JSON.stringify(this.deposit))
        if (deposit.payment_system_id === 12 && !this.ps_select) {
          this.$toast.error(`Select Deposit Network`, {
            position:
                "top-right",
          });
          return
        }
        deposit.payment_system_id = this.ps_select || deposit.payment_system_id;
        this.btn_disabled = true
        await this.$api.transaction.deposit(deposit).then(
            response => {
              if (response.data.success) {
                this.deposit.html = response.data.qrcode
                this.deposit.address = response.data.address.wallet
                this.deposit.total = response.data.amount
                this.deposit.payment_id = response.data.payment
                this.pending = true
                this.$socket.emit('new deposit')
                this.$socket.on('channel deposit', ({success}) => {
                  if (success) {
                    this.pending = false
                    this.btn_disabled = false
                    this.$socket.off("channel deposit");
                    this.setBonuses()
                    this.deposit.bonuses = null
                  }
                })
              }
              this.btn_disabled = false
            }
        )
      }
    },
    async cancelDeposit() {
      if (this.deposit.payment_id) {
        await this.$api.transaction.cancelDeposit(this.deposit).then(
            response => {
              if (response.data.success) {
                this.html = ''
                this.address = ''
                this.pending = false
                this.btn_disabled = false
                this.$socket.emit('cancel')
              }
            }
        )
      }
    },
    async onPaymentSystem(el) {
      if (el.id === 15) {
        await this.onChangelly();
        this.deposit.payment_system_id = el.id
        return
      }

      if (el.active && !this.pending) {
        this.deposit.wallet = this.wallets.find(wl => wl.currency_id === el.currency_id) ?? ''
        this.deposit.payment_system_id = el.id
        this.changeAmount()
      }
    },
    async onChangelly() {
      let deposit = JSON.parse(JSON.stringify(this.deposit))
      deposit.payment_system_id = 12;
      deposit.cc = true;
      await this.$api.transaction.deposit(deposit).then((response) =>

          this.changelly = `https://widget.changelly.com?from=usd&to=usdtrx&amount=150&address=${response.data.address.wallet}&fromDefault=usd&toDefault=usdtrx&merchant_id=SGp0SLGi7GPqNtTl&payment_id=&v=3&type=no-rev-share&color=5f41ff&headerId=1&logo=hide&buyButtonTextId=1`
      )
    },
    changeTotal() {
      if (this.currenPs) {
        this.deposit.amount = (Number(this.deposit.total) / Number(this.currenPs.rate)).toFixed(2)
      }
    },
    changeAmount() {
      if (this.currenPs) {
        if (this.currenPs.id === 12 || this.currenPs.id === 13) {
          this.deposit.total = (Number(this.deposit.amount) * Number(this.currenPs.rate)).toFixed(2)
          return
        }
        this.deposit.total = (Number(this.deposit.amount) * Number(this.currenPs.rate)).toFixed(8)
      }
    },
    async setBonuses() {
      const bonuses = [];

      bonuses.push({
        id: 0,
        title: "No bonus",
        description: "I don’t want to receive bonuses",
        active: 1
      })


      let {data} = await this.$api.bonuses.user();

      if (data.bonuses) {
        bonuses.unshift(...data.bonuses);

        bonuses.map((el, idx) => {
          el.active = !!this.$route.params.bonuses ? el.id === Number(this.$route.params.bonuses) : idx === 0;
          return el;
        });
      }

      this.bonuses = bonuses
    },
    activeBonuses(id) {
      this.bonuses = this.bonuses.map(el => {
        el.active = el.id === id
        return el
      })
      console.log(this.bonuses)
    },
    activePs(id) {
      this.ps_select = id
    }
  },
  beforeUnmount() {
    this.$socket.off("new deposit");
    this.$socket.off("cancel");
    this.$socket.off("channel deposit");
  },
}
</script>

<style scoped>
.bounce-enter-active {
  animation: bounce-in 0.3s reverse;
}

.bounce-leave-active {

  animation: bounce-in 0.3s;
}

@keyframes bounce-in {
  0% {
    opacity: 1;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(0);
  }
}
</style>
