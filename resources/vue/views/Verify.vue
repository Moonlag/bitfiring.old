<template>
    <main class="main top-bg">
      <bubble-top-component></bubble-top-component>
        <header class="header container">
            <div class="header__caption flex_center">
                <h1 class="header__title mb-24">
                    {{ status }}
                </h1>
                <router-link class="btn btn-xl btn_gradient" to="/">
                    Go to the homepage
                </router-link>
            </div>
        </header>
    </main>
</template>

<script>
import axios from "axios";
import BubbleTopComponent from "../components/buble/BubbleTopComponent";
export default {
    name: "Verify",
    props: ['token'],
    data(){
        return {
            status: 'Verification successful, please wait..',
            user: {}
        }
    },
     beforeMount(){
        if(this.token){
           axios.post('/a/verify',  {token: this.token}).then(response => {
                if(response.data.errors){
                    this.status = response.data.errors
                }
                if(response.data.success){
                  setTimeout(() => {
                    this.$router.push('/')
                  }, 3000)
                }
            })
        }
    },
  components: {
    BubbleTopComponent
  }
}
</script>

<style scoped>

</style>
