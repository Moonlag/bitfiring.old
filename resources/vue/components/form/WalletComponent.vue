<template>
    <div class="profile-select2">
        <select2 v-model="s2model"
                 :options="wallet"
                 :settings="{placeholder: 'Wallets', width: '100%', data: id}"
                 @select="mySelectEvent($event)"
        ></select2>
    </div>
</template>

<script>
export default {
    name: "WalletComponent",
    props: ['wallets'],
    created() {
        this.s2model = this.wallets.find(el => el.primary === 1).id
    },
    data() {
        return {
            s2model: '',
        }
    },
    computed: {
        wallet() {
            return this.wallets.map(el => {
                return {
                    id: el.id,
                    text: el.code + ' Wallet'
                }
            })
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
