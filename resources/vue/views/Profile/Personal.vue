<template>
    <h1 class="profile-main__heading "> {{ $t('profile_personal_title') }} </h1>

    <div class="tabs profile-tabs">
        <div class="profile-tabs__head">
            <router-link :to="{name: 'wallet', params: {locale}}" active-class="tab__active"
                         class="tab__head profile-tabs__header col-auto"> {{ $t('profile_personal_balance') }}
            </router-link>
            <router-link :to="{name: 'deposit', params: {locale}}" active-class="tab__active"
                         class="tab__head profile-tabs__header col-auto"> {{ $t('profile_personal_deposit') }}
            </router-link>
            <router-link :to="{name: 'withdrawal', params: {locale}}" active-class="tab__active"
                         class="tab__head profile-tabs__header col-auto"> {{ $t('profile_personal_withdrawal') }}
            </router-link>
            <router-link :to="{name: 'transaction', params: {locale}}" active-class="tab__active"
                         class="tab__head profile-tabs__header col-auto"> {{ $t('profile_personal_transaction') }}
            </router-link>
        </div>
    </div>

<!--    :enter-active-class="route.meta.enterClass"-->
<!--    :leave-active-class="route.meta.leaveClass"-->
    <router-view class="overflow-hidden" v-slot="{ Component, route }">
        <transition mode="out-in">
            <component :is="Component"></component>
        </transition>
    </router-view>

    <!--    <balance-component v-if="tab === 1" :wallets="wallets" @onDeposit="onDeposit"></balance-component>-->
    <!--    <deposit-component v-if="tab === 2" :wallets="wallets" :deposit_wallet="deposit_wallet"></deposit-component>-->
    <!--    <withdrawal-component v-if="tab === 3" :wallets="wallets"></withdrawal-component>-->
    <!--    <transaction-history-component v-if="tab === 4"></transaction-history-component>-->

</template>

<script setup>
import {useI18n} from "vue-i18n";

const {locale} = useI18n()
</script>

<style scoped>

</style>
