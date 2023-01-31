<template>
    <div class="input">
    <input
           :class="[{ 'error': error}, { 'valid': valid},]"
           :type="type"
           id="password"
           name="password"
           :placeholder="label"
           :value="password"
           @input.prevent="updateValue($event.target.value)"
           aria-label="Email" required>
    <div class="show-pass" @click="onChange"></div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref } from 'vue'

const props = defineProps({
    password: String,
    error: Boolean,
    valid: Boolean,
    label: {
        type: String,
        default: 'Password'
    }
})

const emit = defineEmits(['update:password', 'input'])
const type = ref('password')

function updateValue(value) {
    emit('update:password', value)
    emit('input')
}

function onChange(){
    type.value = type.value === 'password' ? 'text' : 'password';
}

</script>


<style scoped lang="scss">
.input {
    position: relative;
}

input[type=password]~.show-pass:before,
input[type=text]~.show-pass:after{
    opacity: 1;
}

.show-pass {
    cursor: pointer;
    width: 24px;
    position: absolute;
    top: 0;
    bottom: 0;
    right: 17px;
    margin: 0 -4px;
    -webkit-transition: opacity .2s cubic-bezier(.645, .045, .355, 1);
    transition: opacity .2s cubic-bezier(.645, .045, .355, 1);

    &:after, &:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-repeat: no-repeat;
        background-position: 50% 50%;
        background-size: 16px auto;
        opacity: 0;
        -webkit-transition: opacity .2s cubic-bezier(.645, .045, .355, 1);
        transition: opacity .2s cubic-bezier(.645, .045, .355, 1);
    }

    &:before {
        top: 7px;
        background-image: url('../assets/img/form/eye-close.svg');
    }

    &:after {
        top: 3px;
        background-image: url('../assets/img/form/eye-open.svg');
    }
}
</style>
