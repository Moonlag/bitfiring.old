<template>
    <Datepicker v-model="date" @update:modelValue="onUpdate" placeholder="Select Date"
     range :partialRange="false" :presetRanges="presetRanges"></Datepicker>
</template>

<script setup>
import '@vuepic/vue-datepicker/dist/main.css';
import Datepicker from '@vuepic/vue-datepicker';
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths, startOfDay } from 'date-fns';
import {ref, defineEmits} from 'vue';


const emit = defineEmits([
    'onChange'
])

const date = ref('')
const presetRanges = ref([
    { label: 'Today', range: [startOfDay(new Date()), new Date()] },
    { label: 'This month', range: [startOfMonth(new Date()), endOfMonth(new Date())] },
    {
        label: 'Last month',
        range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))],
    },
    {label: 'This year', range: [startOfYear(new Date()), endOfYear(new Date())] },
]);


const onUpdate = (newValue) => {
    emit('onChange', newValue)
}

</script>

<style lang="scss">
$dp__input_padding: 11px 12px;

.dp__theme_light {
    input {
        --dp-text-color: #ABABAB;
    }
}
</style>
