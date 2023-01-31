<script setup>
import {ref, reactive} from "vue";
import axios from "axios";
import AnimatedBalance from "../components/animate/AnimatedBalance.vue";

const activeTab = ref(0)
const leaderboard = reactive({
    0: [],
    1: [],
    2: [],
    3: [],
})
const isLoading = ref(true)

const tabs = ref([
    'Wager',
    'Total win',
    'Bonuses',
    'Freespins',
])

const onTab = async (index) => {
    activeTab.value = index
    await onData()
}

const onData = async () => {
    isLoading.value = true
    const {data} = await axios.post('https://bitfiring.com/api/leaderboard/store', {type: activeTab.value})
    leaderboard[activeTab.value] = data.data.sort((firstItem, secondItem) => secondItem.amount - firstItem.amount)
    isLoading.value = false
}

await onData();
</script>

<template>
    <header class="header container">
        <div class="header__caption flex_center">
            <h1 class="header__title">
                Leaderboard
            </h1>
        </div>
    </header>

    <div class="container leaderboard">
        <div class="leaderboard__banner">
            <div class="leaderboard__banner--icon">
                <img :src="`${$cdn}/assets/img/leader_board/banner/btc.webp`" alt="icon">
            </div>
            <div class="leaderboard__banner--title">
                <img :src="`${$cdn}/assets/img/leader_board/banner/title.webp`" alt="icon">
            </div>
        </div>

        <div class="leaderboard__content">
            <ul class="leaderboard__content--tab">
                <li class="leaderboard__content--tab-item" v-for="(tab, index) in tabs" :key="tab"
                    :class="{'active': index === activeTab}" @click="onTab(index)">{{ tab }}
                </li>
            </ul>

            <div class="leaderboard__content--table">
                <router-link v-if="!isLoading" class="leaderboard__content--table-row"
                             v-for="(row, i) in leaderboard[activeTab]"
                             :to="{name: 'leaderboard.id', params: {id: row.id}}" :key="row.id">

                    <div class="leaderboard__content--table-item">
                        <div class="leaderboard__content--table-title">
                            {{ i + 1 }}
                        </div>
                    </div>

                    <div class="leaderboard__content--table-item">
                        <div class="leaderboard__content--table-achievement">
                            <img width="32" height="32" :src="`/assets/img/leader_board/achivment/${row.rank_id}.webp`"
                                 alt="trophy">
                        </div>
                        <div class="leaderboard__content--table-title">
                            {{ row.name }}
                        </div>
                    </div>

                    <div class="leaderboard__content--table-item">
                        <div class="leaderboard__content--table-icon">
                            <img width="24" height="24" src="/assets/img/leader_board/decor/trophy.webp" alt="trophy">
                        </div>
                        <div class="leaderboard__content--table-amount">
                            <AnimatedBalance :value="row.amount" :fix="0" :code="activeTab !== 3 ? 'USDT' : ''"/>
                        </div>
                    </div>

                </router-link>
                <div v-else class="leaderboard__content--table-row loading" v-for="i in 15">
                    <div class="leaderboard__content--table-item">
                        <div class="loading--content"></div>
                    </div>
                    <div class="leaderboard__content--table-item">
                        <div class="loading--content"></div>
                    </div>
                    <div class="leaderboard__content--table-item">
                        <div class="loading--content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.header {
    & {
        padding: 128px 0 10px;

        @media (max-width: 767.98px) {
            padding: 88px 0 0;
        }
    }

    & .header__title {
        color: #ff8700;
        @media (max-width: 991.98px) {
            & {
                font-size: 48px;
            }
        }

        @media (max-width: 767.98px) {
            & {
                font-size: 28px;
            }
        }
    }
}

.leaderboard {
    position: relative;
    display: flex;

    @media (max-width: 991px) {
        flex-direction: column;
    }

    &__banner {
        width: 100%;
        max-width: 268px;
        height: 664px;
        position: relative;
        z-index: 10;

        border: 1px solid transparent;
        border-image: linear-gradient(rgba(235, 56, 0, 1), rgba(249, 151, 5, 1));
        border-image-slice: 4;
        background: linear-gradient(104.93deg, rgba(30, 5, 55, 0.8) 8.35%, rgba(71, 6, 82, 0.8) 93.42%), url(/assets/img/popular-slider/popular-bg_1.webp);
        background-position: center;
        background-size: 500%;
        background-repeat: no-repeat;
        margin-right: 16px;

        @media (max-width: 991px) {
            max-width: 100%;
            width: 100%;
            height: 230px;
            display: flex;
            background-size: 100%;
            margin-bottom: 24px;
        }

        @media (max-width: 767.98px) {
            height: 206px;
        }

        @media (max-width: 480.98px) {
            height: 150px;
        }

        &--icon {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 50px;

            & img {
                @media (max-width: 991px) {
                    margin-top: -50px;
                    margin-left: 60px;
                    width: 200px;
                    height: 200px;
                }

                @media (max-width: 767.98px) {
                    width: 160px;
                    height: 160px;
                    margin-left: 20px;
                }

                @media (max-width: 480.98px) {
                    margin-left: 0;
                    width: 100px;
                    height: 100px;
                }
            }
        }

        &--title {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: -5px;

            width: 100%;

            & img {
                @media (max-width: 991px) {
                    width: 328px;
                    height: 220px;
                    padding-right: 10px;
                }

                @media (max-width: 767.98px) {
                    width: 262px;
                    height: 176px;
                }

                @media (max-width: 480.98px) {
                    width: 196px;
                    height: 130px;
                }

            }

        }

    }

    &__content {
        width: 100%;
        height: 100%;
        position: relative;

        &--tab {
            display: flex;
            justify-content: space-between;
            list-style: none;
            margin: 0;
            padding: 0;
            margin-bottom: 24px;
            max-width: 100%;
            @media (max-width: 767.98px) {
                overflow-x: auto;

                &::-webkit-scrollbar {
                    width: 0;
                    height: 0;
                    background: transparent;
                }
            }

            &-item {
                cursor: pointer;

                display: flex;
                justify-content: center;
                align-items: center;
                max-width: 203px;
                width: 100%;
                height: 40px;

                border: 1px solid transparent;
                border-image: linear-gradient(rgba(235, 56, 0, 1), rgba(249, 151, 5, 1));
                border-image-slice: 4;

                color: white;
                font-weight: 400;
                font-size: 18px;
                line-height: 24px;
                padding: 0 8px;

                white-space: nowrap;

                text-transform: uppercase;

                &.active {
                    font-weight: 600;

                    background: linear-gradient(315deg, #EB3800 0%, #F99705 100%);
                    border-image: none;
                    border-image-slice: 0;
                    border: 0;
                    border-radius: 4px;
                }

                &:not(:last-child) {
                    margin-right: 16px;
                }

                @media (max-width: 767.98px) {
                    font-size: 14px;
                    line-height: 16px;
                    height: 30px;
                    &:not(:last-child) {
                        margin-right: 8px;
                    }
                }
            }
        }

        &--table {
            width: 100%;
            height: 100%;
            max-height: 601px;
            overflow-y: scroll;
            padding-right: 8px;

            &::-webkit-scrollbar {
                width: 4px;
            }

            &::-webkit-scrollbar-track {
                background: rgba(153, 152, 203, 0.2);
                border-radius: 100px;
            }

            &::-webkit-scrollbar-thumb {
                background: #9998CB;
                border-radius: 100px;
            }

            &-row {
                border-top: 1px solid rgba(153, 152, 203, 0.16);
                border-left: 1px solid rgba(153, 152, 203, 0.16);
                border-right: 1px solid rgba(153, 152, 203, 0.16);
                height: 100%;
                min-height: 40px;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                padding: 0 16px;

                &:first-child {
                    border-radius: 4px 4px 0px 0px;
                }

                &:last-child {
                    border-radius: 0px 0px 4px 4px;
                    border-bottom: 1px solid rgba(153, 152, 203, 0.16);
                }

                &.loading {

                    & .loading--content {
                        height: .5rem;
                        width: 100%;
                        border-radius: .25rem;
                        background-color: #9998CB;
                        opacity: 0.5;
                        border: 0 solid #e5e7eb;
                    }
                }
            }

            &-item {
                display: flex;
                align-items: center;
                min-width: 40px;

                &:not(:last-child) {
                    margin-right: 16px;
                    @media (max-width: 767.98px) {
                        margin-right: 8px;
                        min-width: 24px;
                    }
                }

                &:last-child {
                    margin-left: auto;
                }
            }

            &-title {
                color: #ABABAB;
                font-family: 'Raleway';
                font-style: normal;
                font-weight: 400;
                font-size: 16px;
                line-height: 16px;
                @media (max-width: 767.98px) {
                    font-size: 14px;
                }
            }

            &-amount {
                color: white;
                display: inline-flex;

                font-family: 'Raleway';
                font-style: normal;
                font-weight: 700;
                font-size: 16px;
                line-height: 16px;
                @media (max-width: 767.98px) {
                    font-size: 14px;
                }
            }

            &-icon,
            &-achievement {
                margin-right: 10px;
                @media (max-width: 767.98px) {
                    margin-right: 5px;
                }
            }

            &-icon {
                @media (max-width: 767.98px) {
                    & img {
                        width: 16px;
                        height: 16px;
                    }
                }
            }

            &-achievement {
                @media (max-width: 767.98px) {
                    & img {
                        width: 28px;
                        height: 28px;
                    }
                }
            }
        }
    }


}


</style>
