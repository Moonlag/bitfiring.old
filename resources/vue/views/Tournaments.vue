<template>
    <main class="main top-bg">
        <bubble-top-component></bubble-top-component>
        <header class="header container">
            <div class="header__caption flex_center">
                <h1 class="header__title">
                    {{ post.headline }}
                </h1>
            </div>
        </header>
        <!-- TOURNAMENT PREVIEWS -->
        <div class="container">
            <!-- Tournament preview -->
            <div class="tournament-container">
                <tournament-component
                    v-for="tournament in tournaments" :key="tournament.id"
                    :title="tournament.title"
                    :image="tournament.image"
                    :prize="tournament.prize"
                    :deadline="tournament.deadline"></tournament-component>
            </div>
            <!-- Tournament preview -->
        </div>

        <!-- TOURNAMENT PREVIEWS -->
        <div v-html="post.description" class="text-block text-block_mb container"></div>
        <!-- TEXT BLOCK -->

    </main>
</template>

<script>
import TournamentComponent from "../components/main/TournamentComponent";
import axios from "axios";

export default {
    name: "Tournaments",
    beforeRouteEnter(to, from, next) {
        axios.post('/a/static', {slug: 'tournaments'}).then(response => {
            next(vm => vm.setData(null, response.data.post))
        })
    },
    data() {
        return {
            post: {},
            tournaments: [
                {
                    id: 1,
                    title: '$1500+1500 Free Spins in Autumn Tournament',
                    image: 'assets/img/tournament/tournament-preview_1',
                    prize: '1500 Bonuses',
                    deadline: '2021-12-07 21:15:15'
                },
                {
                    id: 2,
                    title: '$1200+1200 Free Spins in Autumn Tournament',
                    image: 'assets/img/tournament/tournament-preview_2',
                    prize: '1200 Bonuses',
                    deadline: '2021-10-07 00:30:27'
                },
                {
                    id: 3,
                    title: '$500 Free Spins in Autumn Tournament',
                    image: 'assets/img/tournament/tournament-preview_3',
                    prize: '500 Bonuses',
                    deadline: '2021-07-20 05:45:48'
                },
            ]
        }
    },
    methods: {
        setData(err, post) {
            if (err) {
                this.error = err.toString()
            } else {
                this.post = post
            }
        }
    },
    components: {
        TournamentComponent
    }
}
</script>

<style scoped>

</style>
