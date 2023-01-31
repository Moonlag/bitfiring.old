<template>
    <div class="profile-select2">
        <select2 v-model="s2model"
                 :options="currency"
                 :settings="{placeholder: 'Currency', width: '100%', data: id}"
                 @select="mySelectEvent($event)"
        ></select2>
    </div>
</template>

<script>
export default {
    name: "CurrencyComponent",
    emits: ['select2'],
    data() {
        return {
            s2model: '',
        }
    },
    computed: {
        currency() {
            return this.$store.state.data.currency.filter(el => el.excluded === 0) || []
        },
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
