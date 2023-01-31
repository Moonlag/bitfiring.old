<template>
    <div class="profile-select2">
        <select2 v-model="s2model"
                 :options="status"
                 :settings="{placeholder: 'Status', width: '100%', data: id}"
                 @select="mySelectEvent($event)"
        ></select2>
    </div>
</template>

<script>
export default {
    name: "StatusComponent",
    props: ['status'],
    emits: ['select2'],
    data(){
        return {
            s2model: ''
        }
    },
    methods: {
        mySelectEvent(evt) {
            this.$emit('select2', this.s2model)
        }
    }
}
</script>

<style scoped>

</style>
