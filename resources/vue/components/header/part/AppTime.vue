<template>
    <time class="slot__time" datetime="13:49:59">{{ hours }}:{{ minutes }}:{{ seconds }} </time>
</template>

<script>
export default {
    name: "AppTime",
    data(){
        return {
            currentTime: Date.parse(new Date())
        }
    },
    computed: {
        seconds() {
            return this.formatTime(Math.floor((this.currentTime / 1000) % 60));
        },
        minutes() {
            return this.formatTime(Math.floor((this.currentTime / 1000 / 60) % 60));
        },
        hours() {
            return this.formatTime(Math.floor((this.currentTime / (1000 * 60 * 60)) % 24));
        },
    },
    mounted() {
        setTimeout(this.countdown, 1);
    },
    methods: {
        countdown() {
            this.currentTime = Date.parse(new Date());
            setTimeout(this.countdown, 1000);
        },
        formatTime(value) {
            if (value < 10) {
                return "0" + value;
            }
            return value;
        }
    }
}
</script>

<style scoped>

</style>
