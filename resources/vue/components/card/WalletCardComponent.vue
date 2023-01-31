<template>
  <div v-for="wallet in wallets" :key="wallet.id"
       class="profile-block__item p-12 p-lg-8 col-4 col-lg-6 col-sm-12">
    <div class="profile-card">
      <div class="profile-card__header justify_between">
        <div class="profile-card__title">
          <label class="profile-card__check">
            <input
                :disabled="wallet.primary"
                @click.prevent="active_primary(wallet.id); wallet.primary = $event.target.value"
                :value="wallet.primary"
                :name="`wallet-${wallet.id}`"
                type="checkbox"
                :checked="wallet.primary"
                required>
            <span class="checkbox__box">                                                        <svg>                                                            <use
                :href="`${cdn}/assets/img/common/check_sprite.svg#check_1`"></use>                                                        </svg>                                                    </span>
          </label> {{ wallet.code }} Wallet <span v-show="wallet.primary" class="profile-card__label">                                                    <span
            class="title-label">                                                        PRIMARY                                                    </span> </span>
        </div>
      </div>
      <div class="row profile-card__description">
        <div class="col">
          <p class="profile-card__text"> Total Balance </p>
          <h3 class="profile-card__title profile-card__title_lg profile-card__balance">{{ wallet.code }}
            <animated-balance :fix="wallet.code === 'USDT' ? 2 : 8" :value="wallet.balance"></animated-balance>
          </h3>
        </div>
      </div>
      <div v-if="wallet.real_balance" class="row profile-card__description">
        <div  class="col">
          <p class="profile-card__text"> Real Balance </p>
          <h3 class="profile-card__title profile-card__title_lg profile-card__balance">USDT
            <animated-balance :fix="wallet.code === 'USDT' ? 2 : 8" :value="wallet.real_balance"></animated-balance>
          </h3>
        </div>
      </div>
      <div v-if="wallet.bonus_balance" class="row profile-card__description">
        <div  class="col">
          <p class="profile-card__text"> Bonus Balance </p>
          <h3 class="profile-card__title profile-card__title_lg profile-card__balance">USDT
            <animated-balance :fix="wallet.code === 'USDT' ? 2 : 8" :value="wallet.bonus_balance"></animated-balance>
          </h3>
        </div>
      </div>
      <div class="row profile-card__action">
        <button @click="goDeposit(wallet.id)" class="btn btn-xl btn_gradient btn-block" type="submit">Deposit</button>
      </div>
    </div>
  </div>
</template>

<script>
import AnimatedBalance from "../animate/AnimatedBalance";

export default {
  name: "WalletCardComponent",
  props: ['wallets'],
  emits: ['onDeposit'],
  methods: {
    active_primary(id) {
      if (!this.wallets.find(el => el.id === id).primary) {
        this.$api.wallet.setPrimary({id: id}).then(
            response => {
              if (response.data.success) {
                this.wallets.forEach(el => {
                  el.primary = Boolean(el.id === id);
                })
                this.$store.commit('user/updatePrimaryWallet', id)
              }
            }
        )
      }
    },
    goDeposit(id) {
      this.$emit('onDeposit', id)
    },
  },
  components: {
    AnimatedBalance
  }
}
</script>

<style scoped>
.profile-card{
  display: flex;
}

.profile-card__description {
  margin-top: 0;
}

.profile-card__action{
  margin-top: auto;
}
</style>
