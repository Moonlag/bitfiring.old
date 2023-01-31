<template>
  <footer class="bottom footer footer_404 ibg">
    <div class="container" v-html="$t('block.brands.text')">
    </div>

    <nav class="footer-nav container">
      <ul>
        <li>
          <router-link :to="{name: 'static', params: {static: 'terms', locale}}">{{$t('footer_terms_and_conditions')}}</router-link>
        </li>
        <li>
          <router-link :to="{name: 'static', params: {static: 'bonus-terms-conditions', locale}}">{{$t('footer_bonus_terms_conditions')}}</router-link>
        </li>
        <li>
          <router-link :to="{name: 'static', params: {static: 'policy', locale}}">{{$t('footer_privacy_policy')}}</router-link>
        </li>
        <li>
          <router-link :to="{name: 'static', params: {static: 'responsible-gaming', locale}}">{{$t('footer_responsible_gaming')}}</router-link>
        </li>
        <li>
          <router-link :to="{name: 'static', params: {static: 'support', locale}}">{{$t('footer_support')}}</router-link>
        </li>
        <li>
          <router-link :to="{name: 'static', params: {static: 'bonuses', locale}}">{{$t('footer_bonuses')}}</router-link>
        </li>
        <li>
          <router-link :to="{name: 'static', params: {static: 'partners', locale}}">{{$t('footer_partners')}}</router-link>
        </li>
        <li><a href="https://affiliates.bitfiring.com/" target="_blank">{{ $t('footer_affiliates') }}</a></li>
      </ul>
    </nav>

    <div class="footer__caution container">

      <p class="footer__text">
        Copyright © {{ copyright }} The bitfiring.com is owned and operated by Zaraa Ltd. All rights reserved. The registered
        address of Zaraa Ltd, with registered number 119216, is 2 Irish Town, Gibraltar, GX9 1AB, Gibraltar (license no.
        5398/ZIP8926-871). It is the player’s sole responsibility to inquire about the existing laws and regulations of
        the given jurisdiction for online gambling. For more information you can contact support@bitfiring.com or via
        live-chat.
      </p>

    </div>
    <span id="global_token"/>
  </footer>
</template>

<script setup>
import {useI18n} from 'vue-i18n'


const copyright = new Date().getFullYear()
const {locale} = useI18n({ useScope: 'global' })

</script>

<style scoped>

</style>
