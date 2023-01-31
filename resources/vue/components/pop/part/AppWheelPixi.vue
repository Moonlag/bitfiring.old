<template>
  <div @click.stop class="popup fancybox-content" style=" display: inline-block;">
    <slot></slot>
    <div class="popup-card">
      <div class="popup-card__main">
        <div style="display: flex; justify-content: center; align-items: center; background: #09051d">
          <Transition>
            <div v-if="loading.status" class="lds">
              <h2 class="lds-text">Loading Lucky Spinner...</h2>
              <div class="lds-loading">
                <div class="Loading">
                  <div ref="progress" class="Loading-progress"></div>
                </div>
              </div>
            </div>
          </Transition>
          <iframe ref="roulette" @load="onLoad" :src="iframe" frameborder="0" id="game" name="game_frame"
                  style="touch-action: auto; width: 424px; height: 668px; cursor: inherit;"></iframe>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, onMounted, computed, inject, onUnmounted, watch, reactive} from 'vue';
import {useStore} from 'vuex';

const store = useStore()
const iframe = ref();
const progress = ref();
const loading = reactive(
    {status: true, progress: 0}
);
let speed = 0.1;

let interval = null;

const user = computed(() => {
  return store.state.user.user
})

let userID = false;
const whell = inject('whell');

// watch(auth.value ,(newV, oldV) => {
//   if(newV !== oldV){
//     if(newV){
//       whell.emit('auth', {userID: newV.id})
//     }
//   }
// })

function onLoad() {
  if (iframe.value) {
    setTimeout(() => {
      speed = 2
    }, 1000)
  }
}

onMounted(() => {
  const sessionID = localStorage.getItem("sessionID") || false;

  interval = setInterval(() => {
    if(loading.progress >= 100){
      loading.status = false
      clearInterval(interval)
    }
    progress.value.style.width = (loading.progress += speed) + '%'
  }, 25)

  if (user.value) {
    userID = user.value.id
    console.log(userID, sessionID)
  }

  whell.auth = {sessionID: sessionID, userID: userID}


  whell.connect();

  whell.on('session', ({sessionID, userID, url}) => {
    whell.auth = {sessionID, userID: userID};
    localStorage.setItem("sessionID", sessionID);
    setTimeout(() => {
      iframe.value = url
      speed = 0.5;
    }, 500);
  });

})

onUnmounted(() => {
  clearInterval(interval)
  whell.disconnect();
})

</script>

<style scoped lang="scss">
.Loading {
  position: relative;
  display: inline-block;
  width: 100%;
  height: 10px;
  background: #f1f1f1;
  box-shadow: inset 0 0 5px rgba(0, 0, 0, .2);
  border-radius: 4px;
  overflow: hidden;

  &-progress {
    position: absolute;
    left: 0;
    width: 0;
    height: 100%;
    border-radius: 4px;
    box-shadow: 0 0 5px rgba(0, 0, 0, .2);
    background: linear-gradient(99.8deg,#c42fed,#639eff,#639eff,#c42fed);
    background-size: 300%;
    transition: ease .1s;
    animation: progress-animation 6s linear infinite
  }
}

.lds {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  position: absolute;
  top: 50%;
  left: 50%;
  background-color: rgb(9, 5, 29);

  transform: translate(-50%, -50%);
  width: 93%;
  height: 96%;

  &-text {
    -webkit-text-fill-color: transparent;
    background: linear-gradient(315deg, #c42fed, #f99705);
    -webkit-background-clip: text;
    color: #eb3800;
    display: table;
    font-family: Raleway;
    font-size: 65px;
    font-style: normal;
    font-weight: 900;
    line-height: 130%;
    text-align: center;
  }

  &-loading {
    width: 250px;
    margin-top: 25px;
  }
}


.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

@keyframes progress-animation {
  0% {
    background-position: 300%
  }
  100% {
    background-position: 0
  }
}

</style>
