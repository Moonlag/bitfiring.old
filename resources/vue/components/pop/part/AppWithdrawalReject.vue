<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header justify_between mb-28">
                <h2 class="profile-card__title">
                    {{$t('pop_cancel_withdrawal') }}
                </h2>
            </div>

            <form class="form">
                <div class="justify_between">
                    <button @click.prevent="$store.commit('close_pop')"
                            class="btn btn_transparent btn_profile btn-block ripple mr-20">
                        {{$t('pop_cancel_withdrawal_reject') }}
                    </button>
                    <button @click.prevent="onSubmit" class="btn btn-primary btn-block btn-xl" type="submit">
                        {{$t('pop_cancel_withdrawal_resolve') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppWithdrawalReject",
    computed: {
        pop_arg() {
            return this.$store.state.pop_arg
        }
    },
    methods: {
        onSubmit() {
            this.pop_arg.resolve(true)
        }
    }
}
</script>

<style scoped>
.profile-card__title {
    font-size: 27px;
    text-align: center;
}
</style>
