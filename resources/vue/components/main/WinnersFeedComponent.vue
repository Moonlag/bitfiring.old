<template>
    <div v-if="winners.length" class="grid-winners container">
        <carousel :settings="settings" :breakpoints="breakpoints">
            <slide v-for="winner in winners"
                   :key="winner.id">
                <div class="carousel__item">
                    <div class="games-grid__item winner-preview"
                         @click="goGame(winner.game.uri, winner.game.provider_id)">
                        <div class="winner-preview__avatar">
                            <div class="winner-preview__img">
                                <div class="ratio__box">
                                    <picture>
                                        <source :srcset="winner.game.img" type="image/webp"/>
                                        <img :src="winner.game.img" alt="img"/>
                                    </picture>
                                </div>
                            </div>
                        </div>
                        <div class="winner-preview__caption">
                            <div class="winner-preview__name">{{ winner.player || 'A Player' }} won</div>
                            <div class="winner-preview__price">
                                <animated-balance fix="2" :value="winner.amount" :animated="true" code="USDT"/>
                            </div>
                            <div class="winner-preview__game">
                                in
                                <a class="game_call"
                                   href="javascript:;">{{ winner.game.name }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </slide>
        </carousel>
    </div>
</template>

<script>
import axios from "axios";
import {Carousel, Slide} from 'vue3-carousel';

import AnimatedBalance from "../animate/AnimatedBalance";

export default {
    name: "WinnersFeedComponent",
    emits: ['goGame'],
    data() {
        return {
            winners: [],
            settings: {
                itemsToShow: 1,
                itemsToScroll: 1,
                autoplay: 5500,
                wrapAround: true,
                snapAlign: 'start'
            },
            breakpoints: {
                320: {
                    itemsToShow: 2,
                },
                // 700px and up
                776: {
                    itemsToShow: 3,
                },
                992: {
                    itemsToShow: 4,
                },
                1024: {
                    itemsToShow: 4,
                },
            }
        }
    },
    methods: {
        goGame(uri, provider_id) {
            this.$emit('goGame', uri, provider_id)
        },
    },
    async created() {
        let {data} = await axios.post('/api/winners');
        this.winners = data.data
    },
    components: {
        Carousel,
        Slide,
        AnimatedBalance
    }
}
</script>

<style scoped>
.games-grid__item {
    width: 100%;
}

.winner-preview{
    text-align: left;
}

.list-complete-item {
    transition: all 0.8s ease;
    display: inline-block;
    margin-right: 10px;
}

.list-complete-enter-from,
.list-complete-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

.list-complete-leave-active {
    position: absolute;
}

.carousel__item {
    padding: 0 5px;
    width: 100%;
}

.carousel {
  max-width: 1121px;
}

</style>
