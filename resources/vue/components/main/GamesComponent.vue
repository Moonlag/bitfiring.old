<template>
    <div class="games">
        <h4 v-if="!games.length && !loading" class="center_message">{{ message }}</h4>
        <div class="games-grid mb-16" id="feed_first">
            <slot name="winner"></slot>
            <transition-group @enter="onEnter" @before-enter="onBeforeEnter" @leave="onLeave">
                <router-link v-for="game in games" :key="game"
                             :to="{name: 'play', params: {provider: game.provider, slug: game.uri, locale}}"
                             class="games-grid__item game-preview game-preview_left game_call">
                    <div class="game-preview__img">
                        <div class="ratio__box game-grid__animated">
                            <picture>
                                <source :srcset="`${$cdn}/public/${game.img}`" type="image/webp">
                                <img :src="`${$cdn}/public/${game.img}`" alt="img">
                            </picture>
                        </div>
                    </div>
                </router-link>
            </transition-group>
        </div>

        <div class="flex_center">
            <button v-if="games.length && total > games.length" @click="onLoad" class="show-more show-more_gap">
                <div class="show-more__icon">
                    <svg :class="{'load-more-animation': loading}">
                        <use href="/assets/img/icons/icons.svg#show-more"></use>
                    </svg>
                </div>
                <div class="show-more__title">
                    {{$t('main_load_more')}}
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
import {watch, onMounted} from "vue"
import {gsap, Linear, Circ} from 'gsap';

let delay = 1;

const props = defineProps({
    games: {
        type: Array,
        required: true
    },
    message: {
        type: String,
        default: 'No games found'
    },
    total: {
        type: Number,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    },
    first_load: {
        type: Boolean,
        default: false
    },
    locale: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['goGame', 'onLoad'])

watch(() => props.loading, (newVal, oldVal) => {
    if (oldVal) {
        delay = 1
    }
})

function onLoad() {
    if (props.loading) {
        return
    }
    delay = 1
    emit('onLoad')
}

function onBeforeEnter(el) {
    el.style.opacity = 0
}

function onEnter(el) {
    if (delay > 4) {
        delay = 1
    }
    gsap.to(el, {
        duration: 0.5,
        delay: delay * 0.1,
        opacity: 1,
        y: 0,
        stagger: {each: 0.15, grid: [1, 3]},
        overwrite: true,
        ease: Linear.easeIn
    })
    delay++
}

function onLeave(el) {
    gsap.set(el, {opacity: 0, overwrite: true})
}

// onMounted(() => {
//     const games = document.querySelectorAll('.games-grid__item');
//     games.forEach((el) => {
//         el.style.opacity = 0
//         if (delay > 4) {
//             delay = 1
//         }
//         gsap.to(el, {
//             duration: 0.5,
//             delay: delay * 0.1,
//             opacity: 1,
//             y: 0,
//             stagger: {each: 0.15, grid: [1, 3]},
//             overwrite: true,
//             ease: Linear.easeIn
//         })
//         delay++
//     })
// })
</script>

<style scoped>

.games-grid {
    min-height: 100px;
}

.load-more-animation {
    animation: loader-turn 1s linear infinite;
}

@keyframes loader-turn {
    25% {
        transform: rotate(180deg)
    }
    100% {
        transform: rotate(720deg)
    }
}

.load {
    height: 148px;
    display: flex;
    justify-content: center;
}

.load svg {
    width: 100px;
    height: 100px;
    display: inline-block;
}

.wave {
    width: 5px;
    height: 25px;
    background: linear-gradient(
        45.8deg, #C42FED, #C42FED, #639EFF, #639EFF);
    margin: 10px;
    animation: wave 1s linear infinite;
    border-radius: 20px;
}

.wave:nth-child(2) {
    animation-delay: 0.1s;
}

.wave:nth-child(3) {
    animation-delay: 0.2s;
}

.wave:nth-child(4) {
    animation-delay: 0.3s;
}

.wave:nth-child(5) {
    animation-delay: 0.4s;
}

.wave:nth-child(6) {
    animation-delay: 0.5s;
}

.wave:nth-child(7) {
    animation-delay: 0.6s;
}

.wave:nth-child(8) {
    animation-delay: 0.7s;
}

.wave:nth-child(9) {
    animation-delay: 0.8s;
}

.wave:nth-child(10) {
    animation-delay: 0.9s;
}

@keyframes wave {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1);
    }
    100% {
        transform: scale(0);
    }
}
</style>
