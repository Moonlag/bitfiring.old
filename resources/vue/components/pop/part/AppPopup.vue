<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header justify_between mb-28">
                <h2 class="profile-card__title profile-card__title_lg">
                    Are you sure?
                </h2>
            </div>

            <form @submit.prevent="onSubmit" class="form">
                <div class="justify_between">
                    <button @click.prevent="$store.commit('close_pop')" class="btn btn_transparent btn_profile btn-block ripple mr-20">
                        Close
                    </button>
                    <button class="btn btn-primary btn-block btn-xl" type="submit">
                        I Agree
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppPopup",
    computed: {
        pop_arg(){
          return this.$store.state.pop_arg
      }
    },
    methods: {
        close_pop(){
            this.$emit('close_pop');
        },
        onSubmit(){
            this.$api.transaction.cancelWithdrawal(this.pop_arg).then(response => {
                if(response.data.success){
                    this.$store.dispatch('data/transaction_history')
                    this.$store.commit('close_pop')
                    this.$toast.success(`Withdrawal canceled`, {
                        position: "top-right",
                        duration: false
                    });
                }
            })
        }
    }
}
</script>

<style scoped>

</style>
