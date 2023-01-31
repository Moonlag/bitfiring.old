<template>
        <header class="header container">
            <div class="header__caption flex_center">
                <div class="header__container">
                    <img src="/assets/img/leader_board_single/head/title.webp" alt="title">
                    <h1 class="header__title">
                        {{ player.name }}
                    </h1>
                    <span class="header__title shadow">
                        {{ player.name }}
                    </span>
                </div>
            </div>
        </header>

        <div class="container leaderboard">
            <div class="leaderboard--block">
                <div class="leaderboard--head">
                    <div class="leaderboard--head_loyalty">
                        <img src="/assets/img/leader_board_single/loyalty_level.webp" alt="loyalty level">
                        <h5 class="leaderboard--head_loyalty--subtitle">Loyalty level</h5>
                        <h2 class="leaderboard--head_loyalty--title">{{ player.rank_id }}</h2>
                    </div>
                    <div class="leaderboard--head_total">
                        <div class="leaderboard--head_total--card">
                            <h5 class="card-title">Total Win</h5>

                            <p class="card-description"><AnimatedBalance :value="player.win" :fix="0" code="USDT"/></p>
                        </div>
                        <div class="leaderboard--head_total--card">
                            <h5 class="card-title">Bonuses</h5>
                            <p class="card-description"><AnimatedBalance :value="player.bonuses" :fix="0" code="USDT"/></p>
                        </div>
                        <div class="leaderboard--head_total--card">
                            <h5 class="card-title">Total Deposit</h5>
                            <p class="card-description"><AnimatedBalance :value="player.deposits" :fix="0" code="USDT"/></p>
                        </div>
                        <div class="leaderboard--head_total--card">
                            <h5 class="card-title">Freespins</h5>
                            <p class="card-description"><AnimatedBalance :value="player.freespins" :fix="0"/></p>
                        </div>
                    </div>
                </div>

                <div class="leaderboard--body">
                    <div class="leaderboard--body_card">
                        <h5 class="body_card-subtitle">Top profitable slots</h5>
                        <div class="body_card-profitable">
                            <div class="profitable--row" v-for="game in player.games" key="i">
                                <div class="profitable--item">
                                    <img width="100" height="60"
                                         :src="`${$cdn}/public/${game.img}`" alt="">
                                </div>
                                <div class="profitable--item">
                                    <div class="profitable--item_title">{{ game.name }}</div>
                                    <div class="profitable--item_wagered">
                                        <span class="wagered__subtitle">Wagered</span>
                                        <span class="wagered__amount"><AnimatedBalance :value="game.wagered" :fix="0" code="USDT"/></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="leaderboard--body_card">
                        <h5 class="body_card-subtitle">Latest achievements</h5>
                        <div class="body_card-achievement">
                            <img src="/assets/img/leader_board/achivment/4.webp" alt="achivmenet" v-for="i in 0"
                                 key="i">
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup>
import {ref, onMounted} from "vue";

import AnimatedBalance from "../components/animate/AnimatedBalance.vue";

import {useRoute} from "vue-router";

const player = ref()

const router = useRoute()

const {data} = await axios.post(`https://bitfiring.com/api/leaderboard/player/${router.params.id}`)
player.value = data.data
</script>

<style lang="scss" scoped>
.header {
    position: relative;

    @media (max-width: 767.98px) {
        margin-bottom: 38px;
    }

    @media (max-width: 480.98px) {
        margin-bottom: 24px;
    }

    &__container {
        position: relative;

        & .header__title {

            &.shadow {
                text-shadow: 3px 0px 0px #B53A06;
                background: transparent;
                z-index: 0;
            }

            position: absolute;
            z-index: 5;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);

            background: linear-gradient(360deg, #FFD903 16.67%, #FFD100 27.28%, #FFF329 37.13%, #FFF798 44.51%, #FFF329 62.5%, #FFC90E 81.11%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;

            color: #FFD903;
            font-weight: 900;
            font-size: 24px;
            line-height: 24px;
            text-transform: lowercase;
        }
    }
}

.leaderboard {
    &--block {
        width: 100%;
        background: rgba(153, 152, 203, 0.05);
        backdrop-filter: blur(10px);
        /* Note: backdrop-filter has minimal browser support */

        border-radius: 4px;

        border: 1px solid transparent;
        border-image: linear-gradient(rgba(235, 56, 0, 1), rgba(249, 151, 5, 1));
        border-image-slice: 4;

        padding: 40px;
        @media (max-width: 991px) {
            padding: 24px;
        }

        @media (max-width: 767.98px) {
            padding: 24px 15px;
        }
    }

    &--head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;

        @media (max-width: 991px) {
            margin-bottom: 16px;
        }

        @media (max-width: 767.98px) {
            flex-direction: column;
        }

        &_loyalty {
            position: relative;
            margin-right: 40px;
            max-width: 290px;
            width: 100%;

            @media (max-width: 991px) {
                margin-right: 16px;
            }

            @media (max-width: 767.98px) {
                margin-right: 0;
                margin-bottom: 16px
            }

            &--subtitle,
            &--title {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
            }

            &--subtitle {
                top: 64px;
                color: white;
                font-weight: 700;
                font-size: 14px;
                line-height: 16px;
                text-transform: uppercase;
            }

            &--title {
                top: 88px;
                background: linear-gradient(360deg, #FFD903 16.67%, #FFD100 27.28%, #FFF329 37.13%, #FFF798 44.51%, #FFF329 62.5%, #FFC90E 81.11%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;

                color: #FFD903;
                font-weight: 900;
                font-size: 64px;
                line-height: 64px;
            }
        }

        &_total {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 16px;
            max-width: 710px;
            width: 100%;

            @media (max-width: 991px) {
                grid-gap: 8px;
            }

            &--card {
                padding: 22px 0;
                text-align: center;

                width: 100%;

                height: 92px;

                background: rgba(153, 152, 203, 0.05);
                border: 1px solid rgba(153, 152, 203, 0.16);

                border-radius: 4px;

                & .card-title {
                    font-style: normal;
                    font-weight: 500;
                    font-size: 14px;
                    line-height: 16px;

                    color: #9998CB;
                    text-transform: uppercase;
                    margin-bottom: 8px;
                }

                & .card-description {
                    font-style: normal;
                    font-weight: 700;
                    font-size: 24px;
                    line-height: 24px;
                    font-feature-settings: 'pnum' on, 'lnum' on;

                    color: white;

                    @media (max-width: 767.98px) {
                        font-size: 20px;
                    }
                }
            }
        }
    }

    &--body {
        display: flex;
        justify-content: space-between;
        @media (max-width: 767.98px) {
            flex-direction: column;
        }

        &_card {
            width: 100%;
            max-width: 508px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;

            background: rgba(153, 152, 203, 0.05);
            border: 1px solid rgba(153, 152, 203, 0.16);

            border-radius: 4px;
            overflow: hidden;

            @media (max-width: 1180px) {
                &:not(:last-child) {
                    margin-right: 16px;
                }
            }

            @media (max-width: 767.98px) {
                &:not(:last-child) {
                    margin-right: 0;
                    order: 2;
                }

                &:last-child {
                    margin-bottom: 16px;
                }
            }

            @media (max-width: 480.98px) {
                padding: 24px 8px;
            }


            & .body_card-profitable {
                width: 100%;

                .profitable {

                    &--row {
                        display: flex;

                        &:not(:last-child) {
                            margin-bottom: 8px;
                        }
                    }

                    &--item {
                        &:not(:last-child) {
                            margin-right: 16px;
                        }

                        &:last-child {
                            width: 100%;
                            display: flex;
                            justify-content: space-between;
                            @media (max-width: 991px) {
                                flex-direction: column;
                            }

                            @media (max-width: 767.98px) {
                                flex-direction: row;
                            }

                            @media (max-width: 480.98px){
                                flex-direction: column;
                            }
                        }

                        img {
                            width: 100px;
                            height: 60px;
                            object-fit: cover;
                            border-radius: 4px;
                        }

                        .profitable--item_title {
                            display: flex;
                            height: 100%;
                            align-items: center;

                            font-weight: 500;
                            font-size: 16px;
                            line-height: 16px;
                            margin-bottom: 4px;

                            color: #9998CB;
                            @media (max-width: 991px) {
                                font-size: 14px;
                            }
                        }

                        .profitable--item_wagered {
                            display: flex;
                            flex-direction: column;
                            align-items: flex-end;
                            justify-content: center;
                            height: 100%;
                            @media (max-width: 991px) {
                                align-items: flex-start;
                            }

                            @media (max-width: 767.98px) {
                                align-items: flex-end;
                            }

                            @media (max-width: 480.98px){
                                align-items: flex-start;
                            }

                            & .wagered__subtitle {
                                font-weight: 500;
                                font-size: 16px;
                                line-height: 16px;
                                margin-bottom: 4px;
                                /* identical to box height, or 100% */

                                text-align: right;
                                color: #9998CB;
                                @media (max-width: 991px) {
                                    font-size: 14px;
                                }
                            }

                            & .wagered__amount {
                                font-weight: 700;
                                font-size: 16px;
                                line-height: 24px;
                                /* identical to box height, or 150% */

                                text-align: right;
                                /* white */

                                color: #FFFFFF;
                                @media (max-width: 991px) {
                                    font-size: 14px;
                                }
                            }
                        }
                    }

                }

            }

            & .body_card-subtitle {
                text-align: center;
                font-weight: 700;
                font-size: 16px;
                line-height: 20px;
                text-transform: uppercase;
                margin-bottom: 16px;

                color: #FFFFFF;

                @media (max-width: 767.98px) {
                    line-height: 16px;
                    font-size: 14px;
                }
            }

            & .body_card-achievement {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
                grid-column-gap: 8px;
                grid-row-gap: 16px;

                & img {
                    align-self: center;
                    justify-self: center
                }

                @media (max-width: 1180px) {
                    grid-template-columns: 1fr 1fr 1fr 1fr;

                    & img:nth-last-child(n+9) {
                        display: none;
                    }
                }

                @media (max-width: 991px) {
                    & img {
                        width: 64px;
                        height: 64px;
                    }
                }

                @media (max-width: 767.98px) {
                    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
                    grid-row-gap: 8px;
                    & img:nth-last-child(n+9) {
                        display: block;
                    }
                }

                @media (max-width: 480.98px){
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                    & img {
                        width: 56px;
                        height: 56px;
                    }

                    & img:nth-last-child(n+9) {
                        display: none;
                    }
                }
            }
        }
    }

}

</style>
