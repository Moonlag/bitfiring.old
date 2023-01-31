<template>
    <div class="tournament-preview tournament-preview_mb">
        <div class="tournament-preview__thumbnail">
            <picture>
                <source
                    :srcset="`${image}.webp`"
                    type="image/webp"/>
                <img
                    :src="`${image}.webp`"
                    alt="img"
                    class="tournament-preview__img"
                />
            </picture>
        </div>
        <div class="tournament-preview__caption">
            <h2 class="tournament-preview__title">
                {{ title }}
            </h2>
            <div class="tournament-preview__dash">
                <div class="tournament-preview__prize tournament-prize">
                    <div class="tournament-prize__icon">
                        <picture
                        >
                            <source
                                srcset="assets/img/tournament/tournament-preview__icon.svg"
                                type="image/webp"/>
                            <img src="assets/img/tournament/tournament-preview__icon.svg" alt="img"
                            /></picture>
                    </div>
                    <div class="tournament-prize__info">
                        <div class="tournament-prize__title">Prize Pull</div>
                        <div class="tournament-prize__bonuses">{{ prize }}</div>
                    </div>
                </div>
            </div>
            <div class="tournament-preview__expire tournament-expire">
                <div class="tournament-expire__item">
                    <div class="tournament-expire__counter">
                        <div class="ratio__box flex_center">{{ days }}</div>
                        <svg
                            class="tournament-expire__circle tournament-expire__circle_expired"
                            viewBox="0 0 72 72"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <circle cx="36" cy="36" r="35" stroke="url(#oval_grad_4)"></circle>
                            <defs>
                                <linearGradient
                                    id="oval_grad_4"
                                    x1="-33.7567"
                                    y1="34.838"
                                    x2="35.838"
                                    y2="104.433"
                                    gradientUnits="userSpaceOnUse"
                                >
                                    <stop stop-color="#FF6A28"></stop>
                                    <stop offset="0.99971" stop-color="#FE2F57"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="tournament-expire__caption">days</div>
                </div>
                <div class="tournament-expire__item">
                    <div class="tournament-expire__wrapper">
                        <div class="tournament-expire__counter">
                            <div class="ratio__box flex_center">{{ formatTime(hours) }}</div>
                            <svg
                                class="tournament-expire__circle"
                                viewBox="0 0 72 72"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <circle cx="36" cy="36" r="35" stroke="url(#oval_grad_4)"></circle>
                                <defs>
                                    <linearGradient
                                        id="oval_grad_4"
                                        x1="-33.7567"
                                        y1="34.838"
                                        x2="35.838"
                                        y2="104.433"
                                        gradientUnits="userSpaceOnUse"
                                    >
                                        <stop stop-color="#FF6A28"></stop>
                                        <stop offset="0.99971" stop-color="#FE2F57"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <div class="tournament-expire__caption">hours</div>
                </div>
                <div class="tournament-expire__item">
                    <div class="tournament-expire__counter">
                        <div class="ratio__box flex_center">{{ formatTime(minutes) }}</div>
                        <svg
                            class="tournament-expire__circle"
                            viewBox="0 0 72 72"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <circle cx="36" cy="36" r="35" stroke="url(#oval_grad_4)"></circle>
                            <defs>
                                <linearGradient
                                    id="oval_grad_4"
                                    x1="-33.7567"
                                    y1="34.838"
                                    x2="35.838"
                                    y2="104.433"
                                    gradientUnits="userSpaceOnUse"
                                >
                                    <stop stop-color="#FF6A28"></stop>
                                    <stop offset="0.99971" stop-color="#FE2F57"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="tournament-expire__caption">minutes</div>
                </div>
                <div class="tournament-expire__item">
                    <div class="tournament-expire__counter">
                        <div class="ratio__box flex_center">{{ formatTime(seconds) }}</div>
                        <svg
                            class="tournament-expire__circle"
                            viewBox="0 0 72 72"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <circle cx="36" cy="36" r="35" stroke="url(#oval_grad_4)"></circle>
                            <defs>
                                <linearGradient
                                    id="oval_grad_4"
                                    x1="-33.7567"
                                    y1="34.838"
                                    x2="35.838"
                                    y2="104.433"
                                    gradientUnits="userSpaceOnUse"
                                >
                                    <stop stop-color="#FF6A28"></stop>
                                    <stop offset="0.99971" stop-color="#FE2F57"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="tournament-expire__caption">seconds</div>
                </div>
            </div>
            <div class="tournament-preview__btn">
                <Button class="btn btn_gradient btn-block btn-xl">Free Register Now</Button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "TournamentComponent",
    props: ['title', 'image', 'prize', 'deadline'],
    data() {
        return {
            currentTime: null,
            speed: 1000
        };
    },
    computed: {
        seconds() {
            return Math.floor((this.currentTime / 1000) % 60);
        },
        minutes() {
            return Math.floor((this.currentTime / 1000 / 60) % 60);
        },
        hours() {
            return Math.floor((this.currentTime / (1000 * 60 * 60)) % 24);
        },
        days() {
            return Math.floor(this.currentTime / (1000 * 60 * 60 * 24));
        }
    },
    methods: {
        countdown() {
            this.currentTime = Date.parse(this.deadline) - Date.parse(new Date());
            if (this.currentTime > 0) {
                setTimeout(this.countdown, this.speed);
            } else {
                this.currentTime = null;
            }
        },
        formatTime(value) {
            if (value < 10) {
                return "0" + value;
            }
            return value;
        }
    },
    mounted() {
        setTimeout(this.countdown, 10);
    },
}
</script>

<style scoped>

</style>
