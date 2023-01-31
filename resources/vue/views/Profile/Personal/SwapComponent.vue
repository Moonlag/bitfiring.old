<template>
    <div class="tab__body profile-tabs__body">
        <section class="profile-block wallet-dash">
            <div class="row">
                <form @submit.prevent="onChange" class="wallet-info__form wallet-form form"
                      id="deposit_form" method="post">

                    <div class="form__row form__item-wrap">
                        <div class="row">
                            <div class="col col-sm-12 form__item-deposit">
                                <div class="swap_item_title">
                                    <p>You send</p>
                                    <p>Balance: {{ currenFrom.balance }}</p>
                                </div>
                                <div class="form__item swap_item form-item_right d-flex">
                                    <SwapSelect @select:value="onSelectMain" :vmodel="from" :options="currency"/>
                                    <input type="number" v-model="from_amount" @input="changeTo"
                                           placeholder="Min: 0.00017766"
                                           min="0" step="0.00000001" id="cypto-form"
                                           name="amount"
                                           aria-label="amount">
                                </div>
                                <div class="swap_item_total">
                                    <p>1 {{ currenFrom.code }} ≈ <animated-balance :fix="currenTo.code === 'USDT' ? 2 : 8" :value="currentRate"/> {{ currenTo.code }} </p>
                                </div>
                            </div>
                            <div class="swap-button">
                                <div class="deposit_change">
                                <span>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_766_43015)">
                                            <path d="M14.1666 0.833496L17.5 4.16683L14.1666 7.50016" stroke="white"
                                                  stroke-width="2"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                            <path
                                                d="M2.5 9.1665V7.49984C2.5 6.61578 2.85119 5.76794 3.47631 5.14281C4.10143 4.51769 4.94928 4.1665 5.83333 4.1665H17.5"
                                                stroke="white" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                            <path d="M5.83333 19.1667L2.5 15.8333L5.83333 12.5" stroke="white"
                                                  stroke-width="2"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                            <path
                                                d="M17.5 10.8335V12.5002C17.5 13.3842 17.1488 14.2321 16.5237 14.8572C15.8986 15.4823 15.0507 15.8335 14.1667 15.8335H2.5"
                                                stroke="white" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                            </g>
                                            <defs>
                                            <clipPath id="clip0_766_43015">
                                            <rect width="20" height="20" fill="white"/>
                                            </clipPath>
                                            </defs>
                                     </svg>
                                </span>
                                </div>
                            </div>
                            <div class="col col-sm-12 form__item-deposit">
                                <div class="swap_item_title">
                                    <p>Balance: {{currenTo.balance}}</p>
                                </div>
                                <div class="form__item form-item_right d-flex">
                                    <SwapSelect @select:value="to = Number($event)" :vmodel="to" :options="currencyTo"/>
                                    <input type="number" v-model="to_amount" @input="changeFrom" min="0"
                                           step="0.00000001"
                                           placeholder="Min: 0.00017766"
                                           id="amount"
                                           name="amount"
                                           aria-label="amount">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form__item-wrap form__row">
                        <div class="row justify_center">
                            <button class="btn btn_lg btn_main"
                                    href="javascript:;" :disabled="btn_disabled">
                                {{ btn_disabled ? 'Please wait..' : 'Swap Now' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>

<script>
import SwapSelect from "../../../components/form/SwapSelect";
import axios from "axios";
import AnimatedBalance from "../../../components/animate/AnimatedBalance";

export default {
    name: "SwapComponent",
    created() {
        this.from = this.currency[0].id || null
        this.to = this.wallets[0].currency_id || null
    },
    data() {
        return {
            btn_disabled: false,
            from: null,
            to: null,
            from_amount: 0,
            to_amount: 0
        }
    },
    computed: {
        currency() {
            return this.$store.state.data.currency.filter(el => el.excluded === 0)
        },
        currencyTo(){
            return this.currency.filter(el => el.id !== this.from) || []
        },
        currenFrom() {
            return this.wallets.find(el => el.currency_id === Number(this.from)) || {balance: '0.0000000'};
        },
        currenTo() {
            return this.wallets.find(el => el.currency_id === Number(this.to)) || {balance: '0.0000000'};
        },
        fromRate() {
            return (this.currency.find(el => el.id === Number(this.from)).rate * (1 / this.currency.find(el => el.id === Number(this.to)).rate));
        },
        toRate() {
            return (this.currency.find(el => el.id === Number(this.to)).rate * (1 / this.currency.find(el => el.id === Number(this.from)).rate));
        },
        currentRate() {
            return this.toRate > 1 ? this.toRate.toFixed(8) : this.toRate.toPrecision(1);
        },
        paymentSystem() {
            return this.$store.state.data.payment_system
        },
        wallets() {
            return this.$store.state.user.wallets.wallets.map(el => {
                el.text = el.code
                return el
            })
        },
    },
    methods: {
        changeFrom() {
            let from_amount = (Number(this.to_amount) * this.fromRate)
            this.from_amount = (from_amount + (from_amount * 0.1)).toFixed(8);
        },
        changeTo() {
            let to_amount = (Number(this.from_amount) * this.toRate)
            this.to_amount = (to_amount - (to_amount * 0.1)).toFixed(8);
        },
        async onChange() {
            if (this.btn_disabled) {
                return
            }
            this.btn_disabled = true
            const wallet_from = this.wallets.find(el => el.currency_id === Number(this.from))
            const wallet_to = this.wallets.find(el => el.currency_id === Number(this.to))

            if (!wallet_from) {
                this.$toast.error(`Wallet not found`, {
                    position: "top-right",
                });
                this.btn_disabled = false
                return;
            }

            if (Number(this.from_amount) <= 0 || Number(wallet_from.balance) < Number(this.from_amount)) {
                this.$toast.error(`Amount is too small`, {
                    position: "top-right",
                });
                this.btn_disabled = false
                return;
            }

            if (!wallet_to) {
                await this.$api.wallet.new({currency_id: Number(this.to), primary: 0}).then(
                    response => {
                        if (response.data.success) {
                            this.$store.commit('user/wallets/setWallets', response.data.wallets)
                        }
                    }
                )
            }
            try {

                let {data} = await axios.post('/a/swap', {
                    from: Number(this.from),
                    to: Number(this.to),
                    from_amount: Number(this.from_amount),
                    to_amount: Number(this.to_amount)
                })
                if (data.success) {
                    this.$toast.success(`Success`, {
                        position: "top-right",
                    });
                    this.from_amount = 0
                    this.to_amount = 0
                }
            } catch (e) {
                this.$toast.error(`Unknown Error`, {
                    position: "top-right",
                });

            }

            this.btn_disabled = false
        },
        onSelectMain($event){
            this.from = Number($event)
            this.to = this.currencyTo.find(el => el.id !== $event).id || null
        }
    },
    watch: {
        from: function (new_v, old) {
            if (old !== null) {
                this.changeFrom()
            }
        },
        to: function (new_v, old) {
            if (old !== null) {
                this.changeTo()
            }
        }
    },
    components: {
        AnimatedBalance,
        SwapSelect
    }
}
</script>

<style scoped>
.deposit_change {
    margin-top: auto;
}

.swap_item {
    margin-top: auto;
    justify-content: space-between;
}

.swap-button {
    margin: 0 8px;
}

.swap_item_title {
    position: absolute;
    top: -24px;
    left: 0;
    display: flex;
    justify-content: space-between;
    width: 100%;
}


.swap_item_title p {
    font-family: Raleway;
    font-style: normal;
    font-weight: normal;
    font-size: 14px;
    line-height: 14px;

    color: #FFFFFF;
}

.swap_item_total {
    position: absolute;
    bottom: -24px;
    left: 0;
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.swap_item_total p {
    font-family: Raleway;
    font-style: normal;
    font-weight: normal;
    font-size: 14px;
    line-height: 14px;

    color: #66617C;
}

.form__item-deposit {
    position: relative;
    margin-top: auto;
}

.form__item-wrap {
    padding-top: 25px;
}
</style>
