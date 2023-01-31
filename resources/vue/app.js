import {createApp} from "vue";
import {VueCookieNext} from 'vue-cookie-next'
import VueFullscreen from 'vue-fullscreen'
import {setupRouter} from "./router";
import store from "./store/state";
import api from './plugins/api'
import socket from "./plugins/socket";
import deposit from "./plugins/socket-main";
import whell from "./plugins/socket-whell";
import mix from "./plugins/mix";
import App from "./App.vue";
import Toaster from "@meforma/vue-toaster";
import DefaultLayout from "./layouts/DefaultLayout";
import PlayLayout from "./layouts/PlayLayout";
import ProfileLayout from "./layouts/ProfileLayout";
import LeaderboardLayout from "./layouts/LeaderboardLayout";
import Select2 from 'vue3-select2-component';
import BubbleTopComponent from "./components/buble/BubbleTopComponent";
import {setupI18n} from './plugins/i18nPlugin'

const {translate, locale} = window.common_data.translate

const app = createApp(App);

const i18n = setupI18n({
    locale,
    fallbackLocale: 'en',
    compositionOnly: false,
    fullInstall: true,
    legacy: false,
    messages: {
        ...translate
    }
})


const router = setupRouter(i18n, store)

app.component('default-layout', DefaultLayout)
app.component('play-layout', PlayLayout)
app.component('profile-layout', ProfileLayout)
app.component('leaderboard-layout', LeaderboardLayout)
app.component('Select2', Select2)
app.component('BubbleTopComponent', BubbleTopComponent)
app.directive('click-outside', {
    beforeMount(el, binding, vnode) {
        el.clickOutsideEvent = function(event) {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event, el);
            }
        };
        document.body.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
    }
});

app.use(store);
app.use(router);
app.use(i18n);
app.use(api);
app.use(socket);
app.use(whell);
app.use(deposit);
app.use(Toaster);
app.use(VueCookieNext);
app.use(VueFullscreen);
app.use(mix);
app.mount("#app");
