<template>
    <div class="tab__body profile-tabs__body">
        <section class="profile-block">
            <div class="profile-block__body profile-block__row row">
                <wallet-card-component :wallets="wallets" @onDeposit="onDeposit"></wallet-card-component>
            </div>
        </section>
    </div>
</template>

<script>
import CurrencyComponent from "../../../components/form/CurrencyComponent";
import WalletCardComponent from "../../../components/card/WalletCardComponent";

export default {
    name: "BalanceComponent",
    emits: ['onDeposit'],
    computed: {
        wallets() {
            return this.$store.getters['user/wallets/wallets'].filter(el => el.balance > 0 || el.currency_id === 14) || []
        },
    },
    methods: {
        onDeposit(id){
            this.$router.push({name: 'deposit', params: {id}});
        },
    },
    components: {
        CurrencyComponent,
        WalletCardComponent,
    }
}
</script>

<style scoped>


</style>
