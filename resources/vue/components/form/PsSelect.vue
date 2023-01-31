<template>
    <div class="select-ps">
        <select2 v-model="vmodel" @select="mySelectEvent($event)" :options="options"
                 :settings="{ width: '100%', placeholder: 'Select Network', templateSelection: formatState, templateResult: formatState}"></select2>
    </div>
</template>

<script>
import $ from "jquery";

export default {
    name: "SwapSelect",
    props: {
        vmodel: {
            required: true,
            type: Object
        },
        options: {
            required: true,
            type: Array
        }
    },
    methods: {
        formatState(state) {
            if (!state.id) {
                return state.text;
            }
            let baseUrl = `/uploads/ps/${state.text}.svg`;
            let $state = $(
                '<span class="select2-swap"><img src="' + baseUrl + '" width="32px" height="32px" /> ' + state.text + '</span>'
            );
            return $state;
        },
        mySelectEvent({id, text}){
            this.$emit('select:value', id)
        }
    }
}
</script>

<style scoped>
.select-swap .select2-container--default .select2-selection--single{
    background-color: #e6e1f0!important;
}

</style>
