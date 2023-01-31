<template>
    <div class="container" id="popular_block">
        <div class="popular-slider popular-slider_bottom">
            <carousel :breakpoints="breakpoints" :settings="settings">
                <slide v-for="(slide, idx) in $tm('block.home_slider')" :key="slide.id" @mouseenter="upAnimation"
                       @mouseleave="downAnimation">
                    <div @click="onSubmit($rt(slide.url))" class="carousel__item popular-slider__item game_call">
                        <div class="popular-slider__inner popular-1" :class="`popular-${idx+1 !== 3 ? idx+1 : 1}`">
                            <div class="popular-slider__text" v-html="$rt(slide.text)">
                            </div>
                        </div>
                        <picture>
                            <source :srcset="`${$cdn}/assets/img/popular-slider/popular-img_${idx+1}.webp`"
                                    type="image/webp">
                            <img :src="`${$cdn}/assets/img/popular-slider/popular-img_${idx+1}.webp`" alt="img">
                        </picture>
                    </div>
                </slide>

                <template #addons>
                    <Pagination/>
                </template>
            </carousel>
        </div>
    </div>
</template>

<script setup>
import {computed} from "vue"
import {gsap, Linear, Circ, Back} from 'gsap';
import {CSSPlugin} from 'gsap/CSSPlugin'
import {useI18n} from 'vue-i18n'

gsap.registerPlugin(CSSPlugin);

import 'vue3-carousel/dist/carousel.css';
import {Carousel, Slide, Pagination, Navigation} from 'vue3-carousel';

const lol = computed(() => tm('block.home_slider'))
const emit = defineEmits(['onPopular', 'onRedirect'])

const settings = {
    itemsToShow: 1,
    snapAlign: 'end',
    pauseAutoplayOnHover: true,
    autoplay: false,
    transition: 500,
    wrapAround: true
}

const breakpoints = {
    // 700px and up
    768: {
        itemsToShow: 2,
        snapAlign: 'start',
        wrapAround: false,
        autoplay: false
    },
    993: {
        itemsToShow: 2,
        snapAlign: 'center',
        wrapAround: false,
        autoplay: false
    },
    1180: {
        itemsToShow: 3,
        snapAlign: 'center',
        autoplay: false,
        wrapAround: false,
        touchDrag: false,
        mouseDrag: false,
    },
}

function onSubmit(url) {
    if (url) {
        emit('onRedirect', url)
    } else {
        emit('onPopular', 'jackpot')
    }
}


function upAnimation(event) {
    gsap.to(event.target.querySelector('.carousel__item img'), {
        duration: 1.5,
        delay: 0.1,
        y: -10,
        x: -10,
        scale: 1.1,
        stagger: 5.3,
        ease: Back.easeInOut
    });
}

function downAnimation(event) {
    gsap.to(event.target.querySelector('.carousel__item img'), {
        x: 0,
        y: 0,
        duration: 1.5,
        scale: 1,
        ease: Back.easeInOut,
        delay: 0.1,
        stagger: 0.3,
    });
}
</script>

<style scoped>
.carousel {
    width: 100%;
}

@media (min-width: 1180px) {
    .carousel__pagination {
        display: none;
    }
}

@media (max-width: 380px) {
    .carousel__slide {
        transform: scale(0.85)
    }
}
</style>
