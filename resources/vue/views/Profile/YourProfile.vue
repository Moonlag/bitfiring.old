<template>
    <!-- PROFILE TITLE -->
    <h1 class="profile-main__heading">{{$t('profile_profile_title')}}</h1>
    <!-- /PROFILE TITLE -->
    <!-- YOUR PROFILE -->
    <div class="tabs profile-tabs">
        <div class="profile-tabs__head">
            <router-link :to="{name: 'setting-general', params: {locale}}" active-class="tab__active" class="tab__head profile-tabs__header">
                {{$t('profile_profile_general')}}
            </router-link>
            <router-link :to="{name: 'setting-security', params: {locale}}" active-class="tab__active" class="tab__head profile-tabs__header">
                {{$t('profile_profile_security')}}
            </router-link>
        </div>
    </div>

    <router-view class="overflow-hidden" v-slot="{ Component, route }">
        <transition
            mode="out-in">
            <component :is="Component"></component>
        </transition>
    </router-view>
    <!-- YOUR PROFILE -->

</template>

<script setup>
import {useI18n} from "vue-i18n";

const {locale} = useI18n()
</script>

<style scoped>

</style>
