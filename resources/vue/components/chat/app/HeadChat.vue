<template>
    <div class="panel-heading">
        <div class="justify_between">
            <span><i class="far fa-comment"></i>&nbsp; Live Chat</span>
            <a href="#" class="pull-right chat-expand"><i class="far fa-times-circle"></i></a>
            <a @click="onToggle" href="javascript:;">
                <picture>
                    <source srcset="/assets/img/common/close-popup.svg" type="image/webp">
                    <img class="popup__close" src="/assets/img/common/close-popup.svg" alt="close">
                </picture>
            </a>
        </div>
    </div>
</template>

<script>
export default {
    name: "HeadChat",
    emits: ['onToggle'],
    methods: {
        onToggle() {
            this.$emit('onToggle')
        }
    }
}
</script>

<style scoped>

</style>
