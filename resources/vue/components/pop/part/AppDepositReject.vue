<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header mt-28 mb-28">
                <h2 class="profile-card__title profile-card__title_lg mb-10">
                    {{$t('pop_expired_deposit_title')}}
                </h2>
                <i18n-t keypath="pop_reject_withdrawal" tag="h5">
                    <template v-slot:amount>
                        {{ $store.state.args_reject || 0 }}
                    </template>
                    <template v-slot:link>
                        <router-link @click="$store.commit('close_pop')" :to="{name: 'transaction'}"
                                     class="text text_primary text-height-24 text_unlerline">{{ 'pop_reject_withdrawal_link' }}</router-link>
                    </template>
                </i18n-t>
            </div>
            <div class="profile-card_block">
                <button class="btn btn-transparent btn-block btn-xl ml-8" @click="onClose">{{$t('pop_expired_deposit_resolve')}}</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppDepositReject",
    methods: {
        onClose() {
            this.$store.commit('close_pop')
        }
    }

}
</script>

<style scoped>
.profile-card_block {
    display: flex;
    justify-content: space-between;
}

h2 {
    text-align: center;
}

h5 {
    font-family: Raleway;
    font-style: normal;
    font-weight: 500;
    font-size: 20px;
    line-height: 28px;
    /* or 150% */

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
