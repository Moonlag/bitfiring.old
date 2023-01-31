<template>
    <div class="w-full z-10">
        <select class="hidden" v-model="model" :value="value">
            <option :value="option[id]" v-for="option in options" :key="option">{{ option[label] }}</option>
        </select>
        <label v-if="placeholder" class="text-gray-400">
            {{ placeholder }}
            <span v-if="required" class="text-red-500 required-dot">
            *
        </span>
        </label>
        <div v-click-outside="onClose" class="relative">
            <button @click="onToggle" type="button"
                    class="relative w-full pl-2 pr-9 py-3 text-left"
                    :class="{'opacity-90 cursor-not-allowed': disabled, 'cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500': !disabled}">
            <span v-if="current" class="flex items-center">
                <img :src="current[icon]" :alt="current[label]" class="flex shrink-0 h-6 w-6 rounded-full">
                <span class="ml-2 block truncate font-medium"
                      :class="{'text-gray-600': disabled, 'text-white': !disabled}">
                    {{ current[label] }}
                </span>
            </span>
                <span class="ml-3 absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                     fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                          clip-rule="evenodd">
                    </path>
                </svg>
            </span>
            </button>
            <div v-show="open" class="absolute mt-1 w-full z-10 rounded-md bg-dark shadow-lg">
                <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label" aria-activedescendant="listbox-item-3"
                    class="max-h-56 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm scroll">
                    <li v-for="option in options" v-show="current !== option" :key="option"
                        @click="onSelect(option[id])" id="listbox-item-0" role="option"
                        class="text-white option cursor-pointer select-none relative py-2 pl-3">
                        <div class="flex items-center">
                            <img :src="option[icon]" :alt="option[label]"
                                 class="flex shrink-0 h-6 w-6 rounded-full">
                        <span class="ml-2 block font-normal truncate">
                            {{ option[label] }}
                        </span>
                        </div>
                        <span v-if="option === current" class="absolute inset-y-0 right-0 flex items-center pr-4">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                             aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                  clip-rule="evenodd">
                            </path>
                        </svg>
                    </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, computed} from "vue"

const props = defineProps({
    options: {
        type: Array,
        required: true
    },
    id: {
        type: Text,
        default: 'id',
    },
    label: {
        type: Text,
        default: 'text'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    value: {
        required: true
    },
    placeholder: {
        type: Text,
        default: ''
    },
    icon: {
      type: Text,
      default: 'icon'
    },
    required: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['select:value'])

const open = ref(false)

const model = computed({
    get() {
        return props.value;
    },
    set(value) {
        emit('select:value', value);
    }
})

const current = computed(() => props.options?.find((el) => el.id === model.value))

function onToggle() {
    if (props.disabled) {
        return
    }
    console.log('onToggle')
    open.value = !open.value
}

function onClose() {
    open.value = false
}

function onSelect(option) {
    open.value = false
    emit('select:value', option);
}
</script>

<style lang="scss" scoped>
.w-full {
    width: 100%;
}

.hidden {
    display: none;
}

.relative {
    position: relative;
}

.block {
    display: block;
}

.flex {
    display: flex;
}

.z-10 {
    z-index: 10;
}

.text-gray-400 {
    color: rgb(156 163 175);
}

.text-red-500 {
    color: rgb(239 68 68);
}

.required-dot {

}

.rounded-md {
    border-radius: 0.375rem;
}

.shadow-lg {
    box-shadow: 0 10px 15px -3px rgb(0 0 calc(0 / 0.1)), 0 4px 6px -4px rgb(0 0 calc(0 / 0.1));
}

.mt-1 {
    margin-top: 0.25rem;
}

.ml-3 {
    margin-left: 0.75rem;
}

.ml-2 {
    margin-left: 0.50rem;
}

.pl-3 {
    padding-left: 0.75rem; /* 12px */
}

.pr-2 {
    padding-right: 0.50rem
}

.pr-10 {
    padding-right: 2.5rem; /* 40px */
}

.pr-9 {
    padding-right: 2.25rem; /* 40px */
}

.py-3 {
    padding-top: 0.75rem; /*  12px  */
    padding-bottom: 0.75rem; /* 12px */
}

.py-2{
    padding-top: 0.50rem; /*  12px  */
    padding-bottom: 0.50rem; /* 12px */
}

.text-left {
    text-align: left;
}

.cursor-default {
    cursor: default;
}
.cursor-pointer {
    cursor: pointer;
}

.absolute {
    position: absolute;
}

.right-0 {
    right: 0px;
}

.inset-y-0 {
    top: 0px;
    bottom: 0px;
}

.h-5 {
    height: 1.25rem;
}

.w-5 {
    width: 1.25rem;
}

.h-6{
    height: 1.50rem;
}
.w-6{
    width: 1.50rem
}

.items-center {
    align-items: center;
}

.bg-white {
    background-color: rgb(255 255 255);
}

.bg-dark {
    backdrop-filter: blur(10px);
    background: linear-gradient(99.8deg,rgba(110,32,149,.9) 14.73%,rgba(62,85,153,.9) 88.93%);
}

.text-white {
    color: rgb(255 255 255);
}

.text-gray-900 {
    color: rgb(17 24 39);
}

.font-medium {
    font-weight: 500;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.shrink-0{
    flex-shrink: 0;
}

.rounded-full{
    border-radius: 9999px;
}

.max-h-56{
    max-height: 14rem; /* 224px */
}

.overflow-auto{
    overflow: auto;
}

.scroll {
    scrollbar-width: 6px;
    scrollbar-color: rgb(244, 63, 94) rgb(99, 102, 241);
}
.scroll::-webkit-scrollbar {
    width: 4px;
}
.scroll::-webkit-scrollbar-track {
    background: transparent;
}
.scroll::-webkit-scrollbar-thumb {
    background-color: #ffffff;
    border-radius: 6px;
    border: 2px solid transparent;
}

.option{
    &:hover{
        background-color: #7859F1;
    }
}


</style>
