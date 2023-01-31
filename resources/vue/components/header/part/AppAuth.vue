<template>
  <div>
    <button @click="open_pop(1)" class="btn btn_main" href="javascript:;">{{$t('header_create_account')}}</button>
  </div>
  <div @click="open_pop(2)" class="navbar__user user-nav">
    <svg class="user-nav__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
      <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <a class="user-nav__text" data-fancybox data-src="#login">
        {{ $t('header_sign_in') }}
    </a>
  </div>

</template>

<script setup>
import {useI18n} from "vue-i18n";
const emit = defineEmits(['open_pop'])

function open_pop(id) {
    return emit('open_pop', id)
}
</script>

<style lang="scss" scoped>

.btn {
  & span {
    @media all and (max-width: 767.98px) {
      display: none;
    }
  }
}

.user-nav__icon {
  @media all and (max-width: 767.98px) {
    display: none;
  }
}


</style>
