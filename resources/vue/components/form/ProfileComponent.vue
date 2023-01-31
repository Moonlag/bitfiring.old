<template>
    <div class="profile-select2">
        <select2 v-model="s2model"
                 :options="options"
                 :settings="{placeholder: placeholder, width: '100%', data: id}"
                 @select="mySelectEvent($event)"
        ></select2>
    </div>
</template>

<script>
export default {
    name: "ProfileComponent",
    emits: ['select2'],
    props: ['options', 's2model', 'placeholder'],
    methods: {
        mySelectEvent(evt) {
            this.$emit('select2', this.s2model)
        }
    }
}
</script>

<style scoped>

</style>
