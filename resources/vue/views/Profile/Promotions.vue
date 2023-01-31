<template>
    <h1 class="profile-main__heading">{{$t('profile_promotion_title')}}</h1>

    <div class="tabs profile-tabs">
        <div class="profile-tabs__head">
            <router-link :to="{name: 'promo-bonuses', params: {locale}}" active-class="tab__active"
                    class="tab__head profile-tabs__header col-auto">{{$t('profile_promotion_bonuses')}}
            </router-link>
            <router-link :to="{name: 'promo-freespins', params: {locale}}" active-class="tab__active"
                    class="tab__head profile-tabs__header col-auto">{{$t('profile_promotion_freespins')}}
            </router-link>
        </div>
    </div>

    <router-view class="overflow-hidden" v-slot="{ Component, route }">
        <transition
            mode="out-in">
            <component :is="Component"></component>
        </transition>
    </router-view>
</template>

<script setup>
import {useI18n} from "vue-i18n";

const {locale} = useI18n()
</script>

<style scoped>
.mark-dot.mark-dot_warning {
    background-color: #caa502;
}
</style>
