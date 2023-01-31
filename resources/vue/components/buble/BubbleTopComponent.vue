<template>
    <div class="bubble">
        <picture v-if="img">
            <source :srcset="`${img}.webp`" type="image/webp">
            <img class="top-bg__img img-fluid" :src="`${img}.webp`" alt="img">
        </picture>
    </div>
</template>

<script>
export default {
    name: "BubbleTopComponent",
    props: ['img']
}
</script>

<style scoped>

</style>
