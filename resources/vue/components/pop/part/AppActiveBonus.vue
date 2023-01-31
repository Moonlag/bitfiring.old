<template>
    <div @click.stop class="popup fancybox-content" style="display: inline-block">
        <div class="profile-card profile-card_popup-main profile-card">
            <slot></slot>
            <div class="profile-card__header justify_between mb-28">
                <h5>
                    Do you really want to active Bonus?
                </h5>
            </div>

            <form @submit.prevent="onSubmit" class="form">
                <div class="justify_between">
                    <button class="btn btn-primary btn-block btn-xl" type="submit">
                        Yes, active Bonus
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
          return this.$store.state.activeBonus
      }
    },
    methods: {
        async onSubmit(){
            this.pop_arg.resolve(true);
        }
    }
}
</script>

<style scoped>
h5 {
    font-family: Raleway;
    font-style: normal;
    font-weight: 500;
    font-size: 28px;
    line-height: 28px;
    /* or 150% */

    text-align: center;
    color: #FFFFFF;
}
</style>
