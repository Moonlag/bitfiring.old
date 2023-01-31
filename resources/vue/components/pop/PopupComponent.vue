<template>
    <fancybox-container v-if="$store.state.pop">

        <component :is="`app-${$store.state.pop}`">
            <picture @click="close_pop">
                <source srcset="/assets/img/common/close-popup.svg" type="image/webp">
                <img class="popup__close" src="/assets/img/common/close-popup.svg" alt="close">
            </picture>
        </component>

    </fancybox-container>
</template>

<script>
import AppLogin from "./part/AppLogin";
import AppForgot from "./part/AppForgot";
import AppPayment from "./part/AppPayment";
import AppPopup from "./part/AppPopup";
import AppPopup_1 from "./part/AppPopup_1";
import AppRegistration from "./part/AppRegistration";
import AppRegistration_2 from "./part/AppRegistration_2";
import AppChangePassword from "./part/AppChangePassword";
import AppInfo from "./part/AppInfo";
import FancyboxContainer from "./FancyboxContainer";
import AppSearch from "./part/AppSearch";
import AppVerify from "./part/AppVerify";
import AppConvert from "./part/AppConvert";
import AppDepositReject from "./part/AppDepositReject";
import AppCancelBonus from "./part/AppCancelBonus";
import AppActiveBonus from "./part/AppActiveBonus";
import AppWithdrawal from "./part/AppWithdrawal";
import AppWithdrawalCancel from "./part/AppWithdrawalCancel";
import AppWithdrawalReject from "./part/AppWithdrawalReject";
import AppBonusWager from "./part/AppBonusWager";
import AppBannedUser from "./part/AppBannedUser";
import AppGameReject from "./part/AppGameReject";

export default {
    name: "PopupComponent",
    data() {
        return {
            outside: false
        }
    },
    methods: {
        close_pop() {
            this.$store.commit('close_pop')
        },
    },
    components: {
        AppLogin,
        FancyboxContainer,
        AppForgot,
        AppPayment,
        AppPopup,
        AppPopup_1,
        AppRegistration,
        AppRegistration_2,
        AppChangePassword,
        AppInfo,
        AppSearch,
        AppVerify,
        AppConvert,
        AppDepositReject,
        AppCancelBonus,
        AppWithdrawal,
        AppWithdrawalCancel,
        AppWithdrawalReject,
        AppBonusWager,
        AppBannedUser,
        AppActiveBonus,
        AppGameReject
    }
}
</script>

<style scoped lang="scss">
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.popup__close {
    position: absolute;
    top: 5px;
    right: 5px;
    z-index: 5;

    @media all and (max-width: 767.98px) {
        & {
            top: 0;
            right: 0;
        }
    }
}

.create {
    .popup__close {
        top: 25px;
        right: 25px;
    }
}


</style>
