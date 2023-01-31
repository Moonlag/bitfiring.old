<template>
    <div @click="onOpen" class="chat-icon" :class="{'chat-icon-profile': $route.path.includes('profile')}" v-if="!open">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
            <path fill="#C42FED" d="M13.275 21.23c7.217 0 12.809-4.122 12.723-10.238 0-5.412-5.726-9.739-12.943-9.739s-13.067 4.327-13.067 9.739c0 3.371 1.653 6.965 5.106 8.73 0 0.020-0.006 0.036-0.006 0.058 0 1.465-1.22 3.474-1.7 4.302h0.002c-0.038 0.090-0.061 0.188-0.061 0.292 0 0.41 0.33 0.739 0.74 0.739 0.061 0 0.159-0.012 0.194-0.012 0.010 0 0.014 0 0.013 0.003 2.552-0.417 6.321-3.289 6.879-3.97 0.573 0.085 0.96 0.098 1.44 0.098 0.204-0 0.421-0.003 0.679-0.003zM10.334 19.077l-1.281 1.064c-0.297 0.343-1.588 1.42-2.699 2.119 0.676-1.927 0.647-2.538 0.647-2.538l0.094-1.224-1.090-0.556c-2.771-1.417-4.016-4.367-4.016-6.95 0-4.302 4.965-7.739 11.068-7.739s10.943 3.437 10.943 7.739c-0.019 4.769-4.559 8.27-10.661 8.27 0 0-0.931 0.036-1.887-0.105zM32.010 19.069c0-2.71-1.059-4.778-3.701-6.329-0.064 0.721-0.27 1.495-0.494 2.174 1.708 1.18 2.195 2.393 2.195 4.154 0 2.157-1.005 3.722-3.341 4.917l-0.995 0.494s0.104 2.563 0.257 3.211c-1.951-1.43-2.622-2.625-2.622-2.625l-1.052 0.162c-0.416 0.061-1.541 0.061-1.541 0.061-2.717 0-4.73-0.648-6.448-1.992 0.466-0.029-2.787-0.027-2.86 0.036 1.986 2.426 5.234 3.956 9.308 3.956 0.221 0 0.409 0.002 0.585 0.002 0.415 0 0.75-0.012 1.245-0.084 0.484 0.587 3.195 3.172 5.401 3.532-0.001-0.002 0.002-0.002 0.011-0.002 0.029 0 0.115 0.011 0.167 0.011 0.355 0 0.64-0.285 0.64-0.64 0-0.090-0.020-0.175-0.053-0.252h0.002c-0.415-0.715-1.067-2.805-1.067-4.070 0-0.020-0.006-0.033-0.006-0.051 2.985-1.526 4.367-3.753 4.367-6.665z"></path>
<!--            <linearGradient id="linear-gradient">-->
<!--                <stop offset="100%" stop-color="#C42FED" stop-opacity="1"/>-->
<!--                <stop offset="100%" stop-color="#639EFF" stop-opacity="1"/>-->
<!--            </linearGradient>-->
        </svg>
        <span v-if="new_message" class="count-badge">{{new_message}}</span>
    </div>
</template>

<script>
export default {
    name: "ButtonChat",
    props: ['open', 'new_message'],
    emits: ['onOpen'],
    methods: {
        onOpen(){
            this.$emit('onOpen')
        }
    }
}
</script>

<style scoped>

.chat-icon {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    border-radius: 50%;
    background: linear-gradient(
        99.8deg
        , #C42FED, #639EFF, #639EFF, #C42FED);
    border: 4px solid transparent;
    box-sizing: border-box;
    padding: 1px;
    z-index: 999;
    width: 80px;
    height: 80px;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #FFF;

    opacity: 0.5;
    transition: opacity 1s ease;
}

.chat-icon:hover{
    opacity: 1;
}

.chat-icon::after {
    position: absolute;
    top: -1px;
    bottom: -1px;
    left: -1px;
    right: -1px;
    background: #09051D;
    background-size: 300%;
    content: '';
    z-index: -1;
    border-radius: 50%;
}

.count-badge{
    position: absolute;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: red;
    font-size: 15px;
    line-height: 15px;
    font-feature-settings: 'pnum' on, 'lnum' on;
    color: white;
    width: 20px;
    height: 20px;
    bottom: 5px;
    right: 0;
    border-radius: 50%;
}

#linear-gradient{
    /*background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffad26), color-stop(50%,#fea026), color-stop(51%,#ff8b00), color-stop(100%,#ff7701)); !* Chrome,Safari4+ *!*/
    background: -webkit-linear-gradient(99.8deg
    , #C42FED, #639EFF, #639EFF, #C42FED); /* Chrome10+,Safari5.1+ */
}

</style>
