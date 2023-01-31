<template>
    <input v-model="search.model" type="text" @input="mySearchEvent"/>
    <div v-if="result.length">
        <ul class="search-list">
            <li @click="goGame(game)" class="search-item" v-for="game in result" :key="game">
                    <picture>
                        <source :src="`/public/${game.img}`" type="image/webp">
                        <img :src="`/public/${game.img}`" alt="img">
                    </picture>
                    <p>{{ game.name }}</p>
            </li>
        </ul>
    </div>
    <button @click="search.model = []; result = []" class="search-slot__close" type="button">
        <picture>
            <source srcset="/assets/img/slot/slot_cross.svg" type="image/webp">
            <img src="/assets/img/slot/slot_cross.svg" alt="img"></picture>
    </button>
</template>

<script>
import axios from "axios";

export default {
    name: "SearchComponent",
    data() {
        return {
            result: [],
            search: {
                model: '',
                option: [],
            }
        }
    },
    methods: {
        async mySearchEvent(evt) {
            if (this.search.model.length > 2) {
                const {data} = await axios.post('/a/games/search', {search: this.search.model})
                this.result = data.games
            } else {
                this.result = [];
            }
        },
        goGame(game) {
            const provider = this.$store.state.data.providers.find(el => el.id === game.provider_id);
            this.$router.push({name: 'play', params: {provider: provider.code, slug: game.uri}})
            this.result = [];
            this.search.model = [];
        }
    }
}
</script>

<style scoped>

</style>
