<template>
     <textarea
                ref="element"
                type="text"
               :value="value"
               :id="name"
               :name="name"
               rows="1"
               class="chat-border chat-border-transparent chat-w-full focus:chat-outline-none chat-text-sm chat-flex chat-items-center chat-overflow-hidden"
               @input="$emit('update:value', $event.target.value); resize($event)"
               @keypress="onEnter($event)"
               :placeholder="placeholder">
                    </textarea>
</template>

<script setup>
import {ref} from "vue";

const props = defineProps({
    name: {
        type: String,
        required: true
    },

    value:{
      required: false
    },

    placeholder: {
        type: String,
        required: false
    }
})

const element = ref();

const emit = defineEmits(['update:value', 'onEnter']);

function resize(e) {
    element.value.style.height = 'auto';
    element.value.style.height = `${element.value.scrollHeight}px`;
}

function onEnter(e){
    if(e.which === 13 && !e.shiftKey) {
        e.preventDefault();

        emit('onEnter')
    }
}


defineExpose({
    resize
})
</script>

<style scoped>
textarea {
    box-sizing: border-box;
    resize: none;
}
</style>
