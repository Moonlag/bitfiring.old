<template>
  <div @click.stop class="popup fancybox-content" style="display: inline-block">
    <div class="profile-card profile-card_popup-main profile-card">
      <slot></slot>
      <div class="profile-card__header mt-28 mb-28">
        <h2 class="profile-card__title profile-card__title_lg mb-10">
          You account is suspended.
        </h2>
        <h5>Please, contact techical support via chat to get more information</h5>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AppBannedUser",
  methods: {
    onClose() {
      this.$store.commit('close_pop')
    }
  }

}
</script>

<style scoped>
.profile-card_block {
  display: flex;
  justify-content: space-between;
}

h2 {
  text-align: center;
}

h5 {
  font-family: Raleway;
  font-style: normal;
  font-weight: 500;
  font-size: 20px;
  line-height: 28px;
  /* or 150% */

  color: #FFFFFF;
}

.mt-28 {
  margin-top: 28px;
}

.profile-card__header {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

</style>
