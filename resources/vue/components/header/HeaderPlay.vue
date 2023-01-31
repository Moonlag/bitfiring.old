<template>
    <nav class="navbar slot__nav flex_center navbar__scroll slot_play">
        <router-link class="navbar__logo navbar__logo_slot" to="/">
            <picture>
                <source :srcset="`${$cdn}/assets/img/navbar/logo.svg`" type="image/webp">
                <img class="img_fluid" :src="`${$cdn}/assets/img/navbar/logo.svg`" alt="logo" width="202" height="46"/></picture>
        </router-link>
        <div class="header__play">
            <div class="header__subtitle">GET UP TO <span>3000 USDT</span> WELCOME BONUS</div>
            <button v-if="!loggedIn" @click="$store.commit('open_pop', 1)" class="btn btn_main">{{$t('create_account')}}</button>
          <router-link v-else :to="{name: 'deposit'}" class="btn btn_main">Deposit</router-link>
        </div>
        <div class="slot__dash">
            <form :class="{'slot__search-toggle':onSearch}" class="slot__search">
                <div class="slot__search_inner search-slot">
                    <search-component></search-component>
                </div>
            </form>
            <button @click="onSearch = !onSearch" class="slot__control slot__search-switch">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.719 15.92l5.916 5.94c.5.503.484 1.304-.036 1.787a1.327 1.327 0 01-.907.353c-.36 0-.695-.138-.944-.388l-5.961-5.985a10.176 10.176 0 01-5.75 1.753C4.503 19.38 0 15.033 0 9.69 0 4.347 4.503 0 10.037 0c5.534 0 10.036 4.347 10.036 9.69a9.46 9.46 0 01-2.354 6.23zm-.264-6.23c0-3.95-3.328-7.162-7.418-7.162S2.618 5.74 2.618 9.69c0 3.95 3.328 7.162 7.419 7.162 4.09 0 7.418-3.213 7.418-7.162z"/>
              </svg>
            </button>
            <button @click="fullscreen" class="slot__control" id="fullscreen">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <g fill-rule="evenodd" clip-rule="evenodd">
                  <path d="M6.935 8.152a.654.654 0 01-.467-.194l-.45-.45-3.884-3.896.002 2.818a.678.678 0 01-.676.678H.74a.674.674 0 01-.676-.678V.696A.68.68 0 01.266.214.683.683 0 01.757 0h5.598c.373 0 .677.304.677.679v.723c0 .17-.075.342-.204.471-.129.13-.3.204-.47.204l-2.807-.004 4.383 4.395c.004.01.01.019.016.029l.01.014.01.013a.674.674 0 01-.042.915l-.51.51a.68.68 0 01-.483.203zM17.531 23.95a.67.67 0 01-.478-.2.674.674 0 01-.198-.478l-.002-.726c0-.354.322-.677.675-.677l2.81.001-4.319-4.33a.695.695 0 01-.006-.969l.51-.512a.685.685 0 01.919-.034l.011.01.012.008a.349.349 0 00.033.02l4.375 4.387-.004-2.816c0-.354.32-.676.673-.676h.722c.373 0 .677.305.677.68l.003 5.617c0 .18-.07.354-.19.475l-.026.026a.674.674 0 01-.48.196l-5.717-.002zM.695 23.956a.656.656 0 01-.479-.202l-.007-.007A.673.673 0 010 23.259v-5.614c0-.18.07-.35.198-.479a.67.67 0 01.479-.2l.721.001c.18 0 .35.071.478.2a.67.67 0 01.197.476l-.004 2.816 4.364-4.376a.569.569 0 00.03-.018l.012-.008.011-.01a.697.697 0 01.45-.164c.18 0 .348.069.472.193l.51.512a.668.668 0 01.192.486.713.713 0 01-.204.492l-.058.059-4.244 4.255 2.81-.002c.179 0 .348.07.475.198a.672.672 0 01.198.478v.725a.677.677 0 01-.675.677H.695zM16.999 8.155a.667.667 0 01-.477-.193l-.51-.513a.688.688 0 01-.032-.922l.01-.012.008-.012a.456.456 0 00.018-.03L20.4 2.078l-2.809.004a.674.674 0 01-.674-.677V.68c0-.18.07-.35.199-.479a.67.67 0 01.478-.199h5.598a.66.66 0 01.48.203l.006.007c.135.13.209.303.209.487v5.732a.678.678 0 01-.676.679h-.722a.666.666 0 01-.477-.2.66.66 0 01-.198-.477l.003-2.816-4.324 4.336a.693.693 0 01-.494.202zM13.833 10.13a2.636 2.636 0 00-3.73 0 2.653 2.653 0 000 3.74 2.635 2.635 0 003.73 0 2.654 2.654 0 000-3.74z"/>
                </g>
              </svg>
            </button>
            <button @click="goHome" class="slot__control" id="close_slot">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M21.262 23.667c.214.214.499.331.803.331.305 0 .59-.117.804-.331l.8-.8c.213-.213.33-.498.33-.803 0-.303-.117-.589-.33-.803l-8.221-8.22a3.58 3.58 0 01-.9 1.506 3.581 3.581 0 01-1.506.9l8.22 8.22zm-10.304-8.22a3.58 3.58 0 01-1.504-.9 3.582 3.582 0 01-.901-1.506l-8.22 8.22a1.13 1.13 0 00-.332.803c0 .305.118.59.331.804l.8.8c.213.213.498.33.803.33.304 0 .59-.117.803-.331l8.22-8.22zm-2.405-4.49c.165-.55.466-1.07.9-1.504a3.58 3.58 0 011.507-.9L2.738.33A1.125 1.125 0 001.935 0c-.305 0-.59.118-.803.331l-.8.8c-.214.214-.332.5-.332.803 0 .304.118.589.332.803l8.22 8.22zm4.488-2.404L21.262.33c.214-.213.499-.331.803-.331.305 0 .59.118.804.331l.8.8c.213.213.331.498.331.803 0 .304-.118.59-.332.803l-8.22 8.221a3.58 3.58 0 00-.9-1.505 3.583 3.583 0 00-1.507-.9z"/>
              </svg>
            </button>
        </div>
    </nav>
</template>

<script>
import SearchComponent from "../form/SearchComponent";

export default {
    name: "HeaderPlay",
    emits: ['fullscreen'],
    data() {
        return {
            onSearch: false
        }
    },
    computed: {
        loggedIn() {
            return this.$store.state.user.status.loggedIn;
        },
    },
    methods: {
        fullscreen() {
            this.$emit('fullscreen')
        },
        goHome() {
            this.$router.push('/')
        },
        open_pop(id) {
            this.$store.commit('open_pop', id)
        },
    },
    components: {
        SearchComponent
    },
}
</script>

<style scoped>
.header__play {
    display: flex;
    align-items: center;
    justify-content: center;
}

.header__subtitle {
    font-family: "Raleway", sans-serif;
    font-weight: 500;
    font-size: 1rem;
    color: white;
    text-align: center;
    padding: 0 16px;
}

.header__subtitle span {
    font-family: "Raleway", sans-serif;
    color: #DC5DFF;
    font-weight: 500;
}
</style>
