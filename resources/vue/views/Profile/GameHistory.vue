<template>
  <h1 class="profile-main__heading">
      {{$t('profile_game_title')}}
  </h1>

  <section class="profile-block">
    <div class="profile-block__body">
      <button class="accordeon__head btn btn_transparent btn_profile d-none d-sm-flex mb-16 btn_w100">
        <span class="mr-12">Filter </span>
        <picture>
          <source srcset="/assets/img/common/filter.svg" type="image/webp">
          <img class="btn__icon" src="/assets/img/common/filter.svg" alt="filter">
        </picture>
      </button>
      <form class="accordeon__body form profile-block__filter">
        <div class="form__row row table-filter">
          <div class="form__item-wrap table-filter__select">
            <currency-component @select2="currency_filter"></currency-component>
          </div>
          <div class="form__item-wrap table-filter__couple">
            <DateRange @onChange="date_filter"></DateRange>
          </div>
        </div>
      </form>
      <div class="profile-table mb-20">
        <div class="profile-table__row profile-table__head">
          <div class="profile-table__title profile-table__item"> Issued</div>
          <div class="profile-table__title profile-table__item"> Played At</div>
          <div class="profile-table__title profile-table__item"> Currency</div>
          <div class="profile-table__title profile-table__item"> Bet Sum (USDT)</div>
          <div class="profile-table__title profile-table__item"> Result</div>
          <div class="profile-table__title profile-table__item"> Balance</div>
        </div>
        <div v-for="session in game_sessions" class="profile-table__row">
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md"> Issued</div>
            <div class="profile-table__text">{{ session.game }}</div>
          </div>
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md"> Played At</div>
            <time class="profile-table__text" :datetime="session.created_at">{{ session.created_at }}</time>
          </div>
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md">Currency</div>
            <div class="profile-table__text">{{ session.currency }}</div>
          </div>
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md"> Bet Sum</div>
            <div class="profile-table__text">{{ session.bet_sum }}</div>
          </div>
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md"> Result</div>
            <div class="profile-table__text">
              <div
                  :class="['mark-dot', session.profit < 0 ? 'mark-dot_success' : 'mark-dot_error']"></div>
              <div class="profile-table__info profile-table__info_right">
                {{ (-1 * session.profit).toFixed(8) }}
              </div>
            </div>
          </div>
          <div class="profile-table__item">
            <div class="profile-table__title profile-table__md">Balance</div>
            <div class="profile-table__text">{{ session.balance }}</div>
          </div>
        </div>

      </div>
      <footer class="pb-3 w-100 v-md-center px-4 d-flex flex-wrap profile-table__pagination">
        <div class="col-auto me-auto">
          <small class="text-muted d-block">
            Displayed records: {{ table.from }}-{{ table.to }} of {{ table.total }}
          </small>
        </div>
        <div v-if="table.total > table.per_page" class="col-auto overflow-auto flex-shrink-1 mt-3 mt-sm-0">
          <ul class="pagination dataTables_paginate">
            <li v-for="link in paginate(table.links)" @click="onPage(link.page)"
                :class="['page-item',
                        Number(link.label) === table.current_page ? 'active' : '',
                        !link.page ? 'active' : '']"
            ><span class="page-link">{{ link.label }}</span></li>
          </ul>
        </div>
      </footer>
    </div>
  </section>
</template>

<script>
import axios from "axios";
import CurrencyComponent from "../../components/form/CurrencyComponent";
import StatusComponent from "../../components/form/StatusComponent";
import DateRange from "../../components/form/DateRange";

export default {
  name: "GameHistory",
  created() {
    axios.post('/a/user/game_sessions', {page: this.page}).then(response => this.onFilter(response))
  },
  data() {
    return {
      status: [
        {id: 1, text: 'Active'},
        {id: 2, text: 'Expired'},
      ],
      page: 1,
      filter: {
        currency_id: '',
        created_at: []
      },
      table: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        to: 0,
        from: 0,
        links: [],
      },
      game_sessions: [],
    }
  },
  computed: {
    currency() {
      return this.$store.state.data.currency
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
    onPage(page) {
      if (!page) {
        return
      }
      this.page = page
      axios.post('/a/user/game_sessions', {
        page: page,
        ...this.filter
      }).then(response => this.onFilter(response))
    },
    apply_filter() {
      axios.post('/a/user/game_sessions', {
        page: 1,
        ...this.filter
      }).then(response => this.onFilter(response))
    },
    onFilter(response) {
      let {data, total, current_page, per_page, last_page, links, from, to} = response.data
      this.table = {total, current_page, per_page, links}
      this.table.to = to
      this.table.last_page = last_page
      this.table.from = from
      this.game_sessions = data
    },
    created_at_filter() {
      if (this.filter.created_at.start.length && this.filter.created_at.end.length)
        this.apply_filter()
    },
    paginate(link) {
      link.forEach((el, idx) => {
        if (idx === 0) {
          el.label = '«'
          if (this.table.current_page > 1) {
            el['page'] = this.table.current_page - 1
          }
        }
        if (idx === link.length - 1) {
          el.label = '»'
          if (this.table.current_page < this.table.last_page) {
            el['page'] = this.table.current_page + 1
          }
        }
        if (idx !== 0 && idx !== link.length - 1) {
          el['page'] = parseInt(el.label)
        }
      })
      return link
    },
  },
  components: {
    CurrencyComponent,
    StatusComponent,
    DateRange
  }
}
</script>

<style scoped>
.pagination {
  display: flex;
}

.pagination li {
  padding: 5px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin-left: 5px;
  cursor: pointer;
}

.pagination li.active {
  background: #B18CE2;
  color: #1a202c;
  cursor: default;
}

</style>
