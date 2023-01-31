<template>
  <div @click.stop class="popup fancybox-content" style=" display: inline-block;">
    <slot></slot>
    <div class="popup-card">
      <div class="popup-card__main">
        <div style="display: flex; justify-content: center; align-items: center; background: #09051d">
            <App/>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import App from "../../Lucky/App"


</script>

<style scoped>

</style>
