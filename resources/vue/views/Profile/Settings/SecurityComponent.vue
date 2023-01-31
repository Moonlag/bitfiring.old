<template>
    <div class="tab__body profile-tabs__body ">
        <section class="profile-block">
            <div class="profile-block__body profile-block__row row">
                <div class="profile-block__item p-12 p-lg-8 col-6 col-sm-12">
                    <div class="profile-card">
                        <div class="profile-card__description">
                            <div class="profile-card__header justify_between">
                                <h2 class="profile-card__title">
                                    <picture>
                                        <source srcset="/assets/img/title-icon/title_icon_5.svg"
                                                type="image/webp">
                                        <img class="profile-card__title-icon"
                                             src="/assets/img/title-icon/title_icon_5.svg" alt="img">
                                    </picture>
                                    Change your Password
                                </h2>
                                <picture>
                                    <source srcset="/assets/img/common/info.svg" type="image/webp">
                                    <img class="profile-card__header-icon" src="/assets/img/common/info.svg"
                                         alt="info">
                                </picture>
                            </div>
                            <p class="profile-card__text">
                                It's necessary to maintain your password and update it more than 1 times per
                                year. Take into account that information.
                            </p>
                        </div>
                        <a @click.prevent="open_pop(4)" class="btn btn-xl btn_gradient" type="submit" data-fancybox
                           data-src="#change_pass">
                            Change my Password
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- SESSION HISTORY -->
        <section class="profile-block">
            <div class="profile-block__header">
                <h2 class="profile-block__title">
                    Last Account Actions
                </h2>
            </div>
            <div class="profile-block__body">
                <div class="profile-table">

                    <div class="profile-table__row profile-table__head">
                        <div class="profile-table__title profile-table__item">
                            Your Location
                        </div>
                        <div class="profile-table__title profile-table__item">
                            Date
                        </div>
                        <div class="profile-table__title profile-table__item profile-table__item_2x">
                            Your Device
                        </div>
                        <div class="profile-table__title profile-table__item profile-table__item_05x">
                            Status
                        </div>
                    </div>

                    <div v-for="(column, idx) in session" class="profile-table__row">
                        <div class="profile-table__item">
                            <div class="profile-table__title profile-table__md">
                                Your Location
                            </div>
                            <div class="profile-table__text">
                                {{column.ip}}, {{column.country}}
                            </div>
                        </div>
                        <div  class="profile-table__item">
                            <div class="profile-table__title profile-table__md"> Date</div>
                            <time class="profile-table__text" datetime="2020-01-05 09:25">{{column.created_at}}</time>
                        </div>
                        <div class="profile-table__item profile-table__item_2x">
                            <div class="profile-table__title profile-table__md"> Your Device</div>
                            <div class="profile-table__text"> {{column.device}}</div>
                        </div>
                        <div class="profile-table__item profile-table__item_05x">
                            <div class="profile-table__title profile-table__md"> Status</div>
                            <div class="profile-table__text">
                                <div
                                    :class="['mark-dot', idx === 0 ? 'mark-dot_success' : 'mark-dot_error']"
                                ></div>
                                {{idx === 0 ? 'Active' : 'Expired'}}
                                <div class="profile-table__info profile-table__info_right">
                                    <picture>
                                        <source srcset="/assets/img/common/info.svg" type="image/webp">
                                        <img src="/assets/img/common/info.svg" alt="img">
                                    </picture>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- SESSION HISTORY -->
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "SecurityComponent",
    created() {
        this.get_session()
    },
    data(){
      return {
          session: [],
      }
    },
    methods: {
        get_session(){
            axios.post('/a/profile/session').then(response => {
                this.session = response.data.session
            })
        },
        open_pop(id) {
            this.$store.commit('open_pop', id)
        },
    },
}
</script>

<style scoped>

</style>
