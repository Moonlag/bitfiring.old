<template>
  <div class="wallet-dash__block">
    <div class="bank-cards" id="deposit_options">
      <div v-for="(ps, idx) in payments_system" :key="ps.id" @click="onPaymentSystem(ps)"
           :class="['bank-card bank-cards__item', ps.id === active ? 'bank-card_active' : '' , {'bank-card_disabled': pending}]">
        <div :class="['bank-card__inner', {'ps_recommended': ps.id === 12} ]">
          <div class="bank-card__img">
            <picture>
              <source :srcset="`${$cdn}/public/uploads/ps/${ps.image}.svg`" type="image/png">
              <img :src="`${$cdn}/public/uploads/ps/${ps.image}.svg`" width="56" height="56" alt="img">
            </picture>
          </div>
          <div v-if="ps.id !== 15" class="bank-card__title"> Min Dep {{ ps.code }} <br>
            {{ changeAmount(ps.rate, ps.minimum, ps.code) }}
          </div>
          <div v-else class="bank-card__title"> {{ ps.name }} 0.00</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "PaymentSystemComponent",
  props: ['paymentSystem', 'pending', 'data', 'type'],
  emits: ['onPaymentSystem'],
  resolve: [
    '194.53.196.75',
    '178.178.85.202',
    '80.246.31.212',
    '80.246.31.70',
    '31.129.87.21',
  ],
  computed: {
    active() {
      return this.data.payment_system_id
    },
    session() {
      return this.$store.state.user.session || null
    },
    user() {
      return this.$store.state.user.user;
    },
    isIp() {
      return this.$options.resolve.includes(this.session?.query) || false
    },
    payments_system() {
      return this.paymentSystem.filter((el) => {
        console.log(this.user)
        return  el.id !== 15 || this.$options.resolve.includes(this.session?.query) || this.user.id === 3
      }) || []
    }
  },
  methods: {
    onPaymentSystem(ps) {
      this.$emit('onPaymentSystem', ps)
    },
    changeAmount(rate, min, code) {
      return (Number(min) * Number(rate)).toFixed(code === 'USDT' ? 2 : 8)
    },
  },
}
</script>

<style scoped>
.ps_recommended {
  position: relative;
}

.ps_recommended::before {
  content: 'RECOMMENDED';
  font-family: Raleway, sans-serif;
  font-style: normal;
  font-weight: 800;
  font-size: 10px;
  line-height: 18px;
  color: white;

  position: absolute;
  top: 0;
  left: 0;
  text-align: center;
  width: 100%;
  height: 18px;
  background-color: #ff8700;
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
}
</style>
