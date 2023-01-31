<template>
    <transition name="fade">
    <div v-if="i_saw_cookie === 0" class="coockies">
        <div class="cookies__text">
            We use cookies to improve your experience. By using our website you are accepting our
            <a href="/policy">
                Cookie Policy
            </a>
        </div>

        <button @click.prevent="setPoliceCookies" class="coockies__btn btn btn_transparent" id="i_saw_cookie">
            ACCEPT
        </button>

    </div>
    </transition>
</template>

<script>
export default {
    name: "CookieComponent",
    props: ['cookie'],
    data(){
      return {
          i_saw_cookie: 1
      }
    },
    methods: {
        setPoliceCookies(){
            this.$cookie.setCookie('i_saw_cookie', 1, {
                expire: '3y',
            });
            this.i_saw_cookie = 1;
        }
    },
    mounted() {
        this.i_saw_cookie = this.cookie
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

</style>
