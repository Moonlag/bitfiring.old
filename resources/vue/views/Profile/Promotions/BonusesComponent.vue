<template>
  <div class="tab__body profile-tabs__body">
    <section class="profile-block">
      <div class="profile-block__header">
        <h2 class="profile-block__title">{{$t('profile_bonuses_title')}}</h2>
      </div>
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
                    <div class="form__item-wrap table-filter__select">
                      <status-component :status="[
                {id: 1, text: 'Not activated'},
                {id: 2, text: 'Active'},
                {id: 3, text: 'Wager done'},
                {id: 4, text: 'lost'},
              ]" @select2="status_filter"></status-component>
                    </div>
                    <div class="form__item-wrap table-filter__couple">
                      <DateRange @onChange="date_filter"></DateRange>
                    </div>
                  </div>
                </form>
        <div class="profile-table">
          <div class="profile-table__row profile-table__head">
            <div class="profile-table__title profile-table__item"> Issued</div>
            <div class="profile-table__title profile-table__item"> Valid Till</div>
            <div class="profile-table__title profile-table__item"> Name</div>
            <div class="profile-table__title profile-table__item"> To Wager</div>
            <div class="profile-table__title profile-table__item"> Wagered</div>
            <div class="profile-table__title profile-table__item"> Status</div>
            <div class="profile-table__title profile-table__item"> Action</div>
          </div>

          <div v-for="row in bonuses" :key="row" class="profile-table__row">
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md">
                Issued
              </div>
              <time class="profile-table__text" datetime="2020-01-05 09:25">
                {{ row.issued }}
              </time>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md">
                Valid Till
              </div>
              <time class="profile-table__text" datetime="2020-01-05 09:25">
                {{ row.active_until }}
              </time>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md">
                Name
              </div>
              <div class="profile-table__text">
                {{ row.title || `USDT ${row?.amount} BONUS` || '-' }}
              </div>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md">
                To Wager
              </div>
              <div class="profile-table__text">
                {{ row.to_wager }}
              </div>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md">
                Wagered
              </div>
              <div class="profile-table__text">
                {{ row.wagered }}
              </div>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md">
                Status
              </div>
              <div class="profile-table__text">
                <div
                    :class="[{'mark-dot_success': row.status === 2}, {'mark-dot_error': row.status === 3 || row.status === 4 || row.status === 5 || row.status === 6}, {'mark-dot_error': row.status === 1}]"
                    class="mark-dot"></div>
                {{ status[row.status] || '-' }}
              </div>
            </div>
            <div class="profile-table__item">
              <div class="profile-table__title profile-table__md">
                Action
              </div>
              <div v-if="row.status === 2" class="profile-table__text">
                <a class="danger" @click="cancel_bonus(row.id)" href="javascript:;">Cancel</a>
              </div>
              <div v-if="row.status === 1" class="profile-table__text">
                <a class="success" @click="active_bonus(row.id)" href="javascript:;">Active</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import {ref, computed, inject, onMounted, reactive} from 'vue';
import {useStore} from 'vuex';
import axios from "axios";

import CurrencyComponent from "../../../components/form/CurrencyComponent";
import StatusComponent from "../../../components/form/StatusComponent";
import DateRange from "../../../components/form/DateRange";

const store = useStore()
const Api = inject('api')
const bonuses = ref([]);
const filter = reactive({
  cat_type: 1,
  currency_id: '',
  status: '',
  created_at: [],
});

const status =
    {
      '1': 'Not activated',
      '2': 'Active',
      '3': 'Wager done',
      '4': 'lost',
      '5': 'cancel',
      '6': 'expired',
    };


onMounted(() => {
  apply_filter()
})

function currency_filter(id) {
  filter.currency_id = id
  apply_filter()
}

function status_filter(id) {
  filter.status = id
  apply_filter()
}

function date_filter(newValue) {
  filter.created_at = newValue
  apply_filter()
}

function apply_filter() {
  Api.bonuses.history(filter).then(
      response => {
        bonuses.value = response.data.bonuses
      }
  )
}

async function cancel_bonus(id) {
  let success = await store.dispatch('cancelBonus')
  if (success) {
    const {data} = await axios.post('/a/user/cancel_bonuses', {bonus_id: id})
    store.commit('close_pop');
    if (data.success) {
      apply_filter()
    }
  }
}

async function active_bonus(id){
  let success = await store.dispatch('activeBonus')
  if (success) {
    const {data} = await axios.post('/a/user/active_bonuses', {bonus_id: id})
    store.commit('close_pop');
    if (data.success) {
      apply_filter()
    }
  }

}
</script>

<style scoped>
.profile-table__text .danger {
  font-weight: 600;
  text-transform: uppercase;
  color: #e12636;
}
.profile-table__text .success {
  font-weight: 600;
  text-transform: uppercase;
  color: #74e126;
}
</style>
