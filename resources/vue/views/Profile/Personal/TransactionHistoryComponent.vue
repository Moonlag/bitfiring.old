<template>
  <div class="tab__body profile-tabs__body ">
    <!-- SESSION HISTORY -->
    <section class="profile-block">
      <div class="profile-block__body">
        <button class="accordeon__head btn btn_transparent btn_profile d-none d-sm-flex mb-16 btn_w100"><span
            class="mr-12">Filter </span>
          <picture>
            <source srcset="/assets/img/common/filter.svg" type="image/webp">
            <img class="btn__icon" src="/assets/img/common/filter.svg" alt="filter"></picture>
        </button>
        <form class="accordeon__body form profile-block__filter">
          <div class="form__row row table-filter">
            <div class="form__item-wrap table-filter__select">
              <currency-component @select2="currency_filter"></currency-component>
            </div>
            <div class="form__item-wrap table-filter__select">
              <status-component :status="status" @select2="status_filter"></status-component>
            </div>
            <div class="form__item-wrap table-filter__couple">
              <DateRange @onChange="date_filter"></DateRange>
            </div>
          </div>
        </form>
        <div class="profile-table">
          <div class="profile-table__row profile-table__head">
            <div class="profile-table__title profile-table__item"> Time</div>
            <div class="profile-table__title profile-table__item"> Type</div>
            <div class="profile-table__title profile-table__item profile-table__item_2x"> Payment Method
            </div>
            <div class="profile-table__title profile-table__item"> Status</div>
            <div class="profile-table__title profile-table__item profile-table__item_05x"> Sum</div>
            <div class="profile-table__title profile-table__item profile-table__item_05x"> USDT</div>
            <div class="profile-table__title profile-table__item profile-table__item_01x"></div>
          </div>
          <div v-for="transaction in transactions" :key="transaction.id" class="profile-table__row">
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md"> Time</div>
              <time class="profile-table__text" datetime="2020-01-05 09:25">{{ transaction.created_at }}
              </time>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md"> Type</div>
              <div class="profile-table__text">
                {{ $options.reference_type[transaction.reference_type_id][transaction.type_id] }}
              </div>
            </div>
            <div class="profile-table__item profile-table__item_2x">
              <div class="profile-table__title profile-table__md"> Payment Method</div>
              <div class="profile-table__text">
                {{ transaction.reference_type_id === 5 ? transaction.ps : transaction.code }}
              </div>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md"> Status</div>
              <div class="profile-table__text">
                <div>
                  <div
                      :class="[{'mark-dot_error': transaction.status === 4  && transaction.type_id !== 5 || transaction.status === 3 && transaction.type_id !== 5}, {'mark-dot_success': transaction.status === 1 || !transaction.status || transaction.type_id === 5}, {'mark-dot_warning': transaction.status === 2 && transaction.type_id !== 5 || transaction.status === 5 && transaction.type_id !== 5}]"
                      class="mark-dot"></div>
                </div>
                {{
                  (transaction.reference_type_id === 5 && transaction.type_id !== 5) ? $options.reference_status[transaction.reference_type_id][transaction.status] || '-' : 'Approved'
                }}
              </div>
            </div>
            <div class="profile-table__item profile-table__item_05x">
              <div class="profile-table__title profile-table__md"> Sum</div>
              <div class="profile-table__text">
                <animated-balance :fix="transaction.currency_id === 14 ? 2 : 8"
                                  :value="transaction.amount" :animated="false"/>
              </div>
            </div>
            <div class="profile-table__item profile-table__item_05x">
              <div class="profile-table__title profile-table__md"> USDT</div>
              <div class="profile-table__text">
                <animated-balance :fix="2"
                                  :value="setUSDT(transaction)" :animated="false"/>
              </div>
            </div>
            <div class="profile-table__item profile-table__item_01x">
              <div class="profile-table__title profile-table__md"></div>
              <div class="profile-table__text">
                <a @click.prevent="$store.commit('open_submit', {id: transaction.id})"
                   href="">
                  <picture v-if="transaction.status === 2 && transaction.type_id === 4">
                    <source srcset="/assets/img/common/close-popup.svg" type="image/webp">
                    <img class="popup__close" src="/assets/img/common/close-popup.svg" width="15"
                         height="15" alt="close">
                  </picture>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- SESSION HISTORY -->
  </div>
</template>

<script>
import CurrencyComponent from "../../../components/form/CurrencyComponent";
import StatusComponent from "../../../components/form/StatusComponent";
import AnimatedBalance from "../../../components/animate/AnimatedBalance";
import DateRange from "../../../components/form/DateRange";

export default {
  name: "TransactionHistoryComponent",
  reference_type: {
    1: {
      1: 'Add',
      2: 'Subtract',
      3: 'Deposits',
      4: 'Cashouts',
      5: 'Gifts',
      6: 'Chargebacks',
      7: 'Refunds',
      8: 'Reversals',
    },
    5: {
      1: 'Deposit',
      2: 'Withdraw',
      3: 'Refund',
      4: 'Chargebacks',
      5: 'Reversals',
    },
  },
  reference_status: {
    1: {
      1: 'Approved',
      2: 'Pending',
      3: 'Rejected',
      4: 'Error',
      5: 'In Progress',
    },
    5: {
      1: 'Approved',
      2: 'Pending',
      3: 'Rejected',
      4: 'Error',
      5: 'In Progress',
    },
  },
  async created() {
    await this.$store.dispatch('data/transaction_history')
  },
  data() {
    return {
      transactions: [],
      status: [
        {id: 1, text: 'Approved'},
        {id: 2, text: 'Pending'},
        {id: 3, text: 'Rejected'},
        {id: 4, text: 'Error'},
      ],
      filter: {
        currency_id: '',
        status: '',
        created_at: []
      },
    }
  },
  computed: {
    currency() {
      return this.$store.state.data.currency.filter(el => el.excluded === 0) || []
    },
    transactions() {
      return this.$store.state.data.transactions
    }
  },
  methods: {
    currency_filter(id) {
      this.filter.currency_id = id
      this.apply_filter()
    },
    status_filter(id) {
      this.filter.status = id
      this.apply_filter()

    },
    date_filter(newValue) {
      this.filter.created_at = newValue
      this.apply_filter()
    },
    apply_filter() {
      this.$store.dispatch('data/transaction_history', this.filter)
    },
    setStatus(status_id) {
      switch (status_id) {
        case 1:
          return 'Approved';
        case 2:
          return 'Pending';
        case 3:
          return 'Rejected';
        case 4:
          return 'Error';
        case 5:
          return 'In Progress';
        default:
          return '-'
      }
    },
    setType(type_id) {
      switch (type_id) {
        case 1:
          return 'Add';
        case 2:
          return 'Subtract';
        case 3:
          return 'Deposits';
        case 4:
          return 'Cashouts';
        case 5:
          return 'Gifts';
        case 6:
          return 'Chargebacks';
        case 7:
          return 'Refunds';
        case 8:
          return 'Reversals';
        default:
          return '-'
      }
    },
    setUSDT(payment){
      if(payment?.amount_usd) return payment.amount_usd

      const currency = this.currency.find((el) => el?.id === payment?.currency_id)

      return Number(payment?.amount) / Number(currency?.rate) || 0.00
    }
  },
  components: {
    CurrencyComponent,
    StatusComponent,
    AnimatedBalance,
    DateRange
  }
}
</script>

<style scoped>
</style>
