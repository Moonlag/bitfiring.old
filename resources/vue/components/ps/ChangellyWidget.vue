<template>
    <div v-if="!show" class="load">
        <svg version="1.1" id="L4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
             x="0px" y="0px"
             viewBox="0 0 50 75" enable-background="new 0 0 0 0" xml:space="preserve">
  <circle fill="#fff" stroke="none" cx="6" cy="50" r="6">
    <animate
        attributeName="opacity"
        dur="1s"
        values="0;1;0"
        repeatCount="indefinite"
        begin="0.1"/>
  </circle>
            <circle fill="#fff" stroke="none" cx="26" cy="50" r="6">
    <animate
        attributeName="opacity"
        dur="1.5s"
        values="0;1;0"
        repeatCount="indefinite"
        begin="0.2"/>
  </circle>
            <circle fill="#fff" stroke="none" cx="46" cy="50" r="6">
    <animate
        attributeName="opacity"
        dur="1.5s"
        values="0;1;0"
        repeatCount="indefinite"
        begin="0.3"/>
  </circle>
</svg>
    </div>
    <div class="wallet-info__form wallet-form form" style="height: 384px">
        <iframe id="changelly" v-show="show" @load="onLoad" width="100%" height="100%" frameborder='none'
                :src="src">
            Can't load widget
        </iframe>
    </div>
</template>

<script setup>
import {ref, onMounted} from "vue";
import $ from "jquery";

const show = ref(false)
defineProps({
    src: {
        type: String
    }
})

function onLoad() {
    setTimeout(() => {
            show.value = true
        }, 300)

}

</script>

<style scoped>
.load {
    margin: auto;
    height: 148px;
    display: flex;
    justify-content: center;
}

.load svg {
    width: 100px;
    height: 100px;
    display: inline-block;
}
</style>
