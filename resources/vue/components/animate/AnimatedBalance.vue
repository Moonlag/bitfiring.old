<template>
    <span :class="css">{{left}} {{ toFixedHard(animatedNumber, fix) }} {{code}}</span>
</template>

<script>
import {gsap, Circ} from "gsap"

export default {
    name: "AnimatedBalance",
    props: {
        value: {
            type: Number,
            required: true
        },
        fix: {
            type: Number,
            default: 8
        },
        css: {
            type: String,
        },
        speed: {
            type: Number,
            default: 0.5
        },
        animated: {
            type: Boolean,
            default: true
        },
        code: {
            type: String,
        },
        left: {
          type: String,
          require: false,
          default: ''
        }
    },
    data() {
        return {
            tweeningValue: 0,
        }
    },
    computed: {
        animatedNumber: function() {
            return this.tweeningValue;
        }
    },
    watch: {
        value: function (newValue) {
            if(!this.animated){
                this.tweeningValue = Number(this.value)
                return
            }
            this.tween(newValue)
        }
    },
    mounted: async function () {
        if(!this.animated){
            this.tweeningValue = Number(this.value)
            return
        }
        if(this.fix !== 0){
            this.tweeningValue = Number(this.value)
        }else {
            await this.$nextTick()
            setTimeout(() => this.tween(this.value), 100)
        }
    },
    methods: {
        tween(newValue) {
            gsap.to(this.$data, {duration: this.speed, tweeningValue: newValue, ease: Circ.easeInOut})
        },
        toFixedHard(number, x) {
            let amount = (number).toFixed(x);

            return amount || `0.00`;
        }
    }
}
</script>

<style scoped>
 .ml-10{
     margin-left: 10px;
 }
 .ml-5{
     margin-left: 5px;
 }

</style>
