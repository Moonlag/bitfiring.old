<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header mt-28 mb-28">
                <h2 class="profile-card__title profile-card__title_lg mb-10">
                    Payment is recieved!
                </h2>
                <h5>
                    We recommend to convert your cryptocurrency <br> to USDT. This will allow you to play more <br>
                    conveniently
                    and
                    provide more games.
                </h5>
            </div>
            <div class="profile-card_block">
                <button class="btn btn_main btn-block btn-xl mr-8" @click="onExchange"
                        :disabled="btn_disabled">
                    {{ btn_disabled ? 'Please wait..' : 'Exchange' }}
                </button>
                <button class="btn btn-transparent btn-block btn-xl ml-8" @click="onClose">Do not change
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "AppConvert",
    data() {
        return {
            btn_disabled: false
        }
    },
    computed: {
        deposit() {
            return this.$store.state.args_swap || null
        }
    },
    methods: {
        async onExchange() {
            if (this.btn_disabled) {
                return
            }

            this.btn_disabled = true

            try {
                let query = {
                    from: Number(this.deposit.currency_id),
                    to: 14,
                    from_amount: Number(this.deposit.total),
                    to_amount: Number(this.deposit.amount)
                };

                if (this.deposit.bonuses.fiat && this.deposit.bonuses.crypto) {
                    query.from_amount += this.deposit.bonuses.crypto;
                    query.to_amount += this.deposit.bonuses.fiat;
                }

                let {data} = await axios.post('/a/swap', query)
                if (data.success) {
                    this.$toast.success(`Success`, {
                        position: "top-right",
                    });
                    this.$store.commit('close_swap')
                }
            } catch (e) {
                this.$toast.error(`Unknown Error`, {
                    position: "top-right",
                });

            }
            this.btn_disabled = false
        },
        onClose() {
            this.$store.commit('close_swap')
        }
    },
}
</script>

<style scoped>

.profile-card_block {
    display: flex;
    justify-content: space-between;
}

h5 {
    font-family: Raleway;
    font-style: normal;
    font-weight: 500;
    font-size: 20px;
    line-height: 28px;
    /* or 150% */

    text-align: center;
    color: #FFFFFF;
}

.mt-28 {
    margin-top: 28px;
}

.profile-card__header {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

</style>
