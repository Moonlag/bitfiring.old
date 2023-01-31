<template>
    <div class="more">
        <div class="text-block" v-html="longText">
        </div>
        <button type="button" @click="onToggle" class="more--button link-nav__text">{{toggle ? $t('main_show_more') : $t('main_show_less')}}</button>
    </div>
</template>

<script setup>
import {ref, onMounted} from "vue"

const props = defineProps({
    html: {
        type: String
    }
})

const longText = ref()
let chunk_text = ``

onMounted(() => {
    chunk_text = props.html.split('***')
    readMore()
})

const toggle = ref(true)

function onToggle() {
    toggle.value = !toggle.value
    readMore()
}

function readMore(){
    if(!chunk_text.length){
        return
    }

    if(toggle.value){
        longText.value = chunk_text[0]
    }else{
        longText.value = chunk_text.join('')
    }

}

</script>

<style scoped lang="scss">
.more {
    display: flex;
    background: hsla(0, 0%, 100%, .05);
    border: 1px solid hsla(0, 0%, 100%, .16);
    border-radius: 4px;
    box-sizing: border-box;
    flex-direction: column;
    height: 100%;
    width: 100%;
    padding: 24px;
    position: relative;

    &--button {
        outline: none;
        border: none;
        color: white;
        text-transform: uppercase;
        margin: 0 auto;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.71;

        &:hover{
            color: #DC5EFF;
        }
    }
}
</style>
