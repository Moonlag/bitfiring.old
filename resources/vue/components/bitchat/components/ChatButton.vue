<template>
    <button @click="$emit('onShow')"
            class="chat--button">
        <TransitionGroup
            enter-from-class="chat-opacity-0 -chat-rotate-45 chat-scale-75"
            enter-active-class="chat-transition chat-duration-200 chat-transform chat-ease"
            leave-active-class="chat-transition chat-duration-100 chat-transform chat-ease"
            leave-to-class="chat-opacity-0 -chat-rotate-45"
        >
            <svg v-if="!show" class="chat-w-6 chat-h-6 chat-text-white chat-absolute" xmlns="http://www.w3.org/2000/svg"
                 width="16"
                 height="16"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>

            <svg v-else class="chat-w-6 chat-h-6 chat-text-white chat-absolute" xmlns="http://www.w3.org/2000/svg"
                 width="16" height="16"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </TransitionGroup>
    </button>
</template>

<script setup>

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    }
})

const emit = defineEmits(['onShow'])


</script>

<style lang="scss" scoped>
    .chat--button {

        background-color: rgba(79,70,229,1);
        transition-property: background-color,border-color,color,fill,stroke,opacity,box-shadow,transform,filter,-webkit-backdrop-filter;
        transition-property: background-color,border-color,color,fill,stroke,opacity,box-shadow,transform,filter,backdrop-filter;
        transition-property: background-color,border-color,color,fill,stroke,opacity,box-shadow,transform,filter,backdrop-filter,-webkit-backdrop-filter;
        transition-timing-function: cubic-bezier(.4,0,.2,1);
        transition-duration: .15s;

        --tw-shadow: 0 10px 15px -3px rgba(0,0,0,0.1),0 4px 6px -2px rgba(0,0,0,0.05);
        box-shadow: var(--tw-ring-offset-shadow,0 0 transparent),var(--tw-ring-shadow,0 0 transparent),var(--tw-shadow);
        --tw-bg-opacity: 1;
        background-color: rgba(99,102,241,var(--tw-bg-opacity));
        border-radius: 9999px;
        justify-content: center;
        align-items: center;
        width: 3.5rem;
        height: 3.5rem;
        display: flex;
        z-index: 40;
        bottom: 1.25rem;
        right: 1.25rem;
        position: fixed;
        cursor: pointer;
        background-image: none;
        &:hover {
            transition-duration: .3s;
        }

    }
</style>
