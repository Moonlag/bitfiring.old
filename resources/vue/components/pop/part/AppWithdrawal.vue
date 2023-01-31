<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header mt-28 mb-28">
                <h5>
                    {{$t('pop_approve_withdrawal', {fee: args.ps.fee, name: args.ps.name, amount: amount})}}
                </h5>
            </div>
            <form class="form">
                <div class="justify_between">
                    <button @click.prevent="$store.commit('close_pop')"
                            class="btn btn_transparent btn_profile btn-block ripple mr-20">
                        No
                    </button>
                    <button v-if="amount > 0" @click.prevent="onSubmit" class="btn btn-primary btn-block btn-xl"
                            type="submit">
                        Yes
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppWithdrawal",
    computed: {
        args() {
            return this.$store.state.withdrawal || null
        },
        amount() {
            return this.args.amount - this.args.ps.fee || 0
        }
    },
    methods: {
        onSubmit() {
            if (this.args) {
                this.args.resolve(true)
            }
        }
    }
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
    font-size: 18px;
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
