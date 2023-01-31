<template>
    <div class="language">
        <SelectComponent @select:value="changeLocale" :value="currentLocale" :options="languages"/>
    </div>
</template>

<script setup>
import $ from "jquery";
import SelectComponent from "./SelectComponent.vue";
import {SUPPORT_LOCALES} from '../../plugins/i18nPlugin'
import {computed} from "vue";
import {useStore} from "vuex";
import {useRouter} from "vue-router";

const store = useStore()
const router = useRouter()

const currentLocale = computed({
    get() {
        return store.state.lang.locale;
    },
    set(value) {
        store.commit("lang/SET_LOCALE", value);
    }
});

const languages = SUPPORT_LOCALES.map((el) => {
    return {
        id: el,
        text: el.toUpperCase(),
        icon: `/assets/img/language/flag-round--${el}.svg`,
    }
});

function changeLocale(lang) {
    currentLocale.value = lang
    router.push({
        name: router.currentRoute.value.name,
        params: {locale: lang === 'en' ? '' : lang}
    })
}

</script>

<style scoped lang="scss">
.language {
    min-width: 45px;
}

.img-flag {
    width: 24px;
    height: 16px;
}
</style>
