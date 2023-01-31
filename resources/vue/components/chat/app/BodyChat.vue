<template>
    <div class="panel-body">
        <slot v-if="connection && isUsername" name="chat-box"></slot>
        <slot v-else name="login-area"></slot>
    </div>
</template>

<script>
export default {
    name: "BodyChat",
    props: ['connection', 'isUsername'],
    data(){
        return {
            message: '',
        }
    },
}
</script>

<style scoped>
.panel-body {
    position: relative;
    overflow-y: auto;
    background-color: #151131;
    height: 454px;
}
</style>
