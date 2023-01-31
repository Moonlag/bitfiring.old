<template>
  <div style="display: flex; justify-content: center; align-items: center; position: relative; z-index: 1">
    <canvas ref="pixi" id="pixi"
            style="touch-action: auto; width: 424px; height: 528px; cursor: inherit; z-index: -1"></canvas>
  </div>
</template>

<script setup>
import {ref, computed, onMounted, onUnmounted, defineEmits} from 'vue';
import axios from 'axios';
import * as PIXI from 'pixi.js';
import FPSDisplay from './components/FPSDisplay'
import Wheel from './components/Wheel'
import Banner from './components/Banner'
import PlayButton from "./components/Button";
import Popup from "./components/Popup";
import {init} from './until/init'
import Timeline from "./components/Timeline";
import {LoadSound} from "./until/sound"
import {sound} from '@pixi/sound';
import {useStore} from 'vuex';
import {useRouter} from "vue-router";

const store = useStore();
const router = useRouter();

const emit = defineEmits(['onFreespin', 'onClose'])

const pixi = ref();
let app = null
const Loader = PIXI.Loader.shared;
const debug = false

const config = computed(() => {
  return store.state.lucky.config
})

onMounted(async () => {
  init();
  app = createApplication();

  if (!Loader.resources.wheel) {
    Loader
        .add('wheel', '/public/assets/img/lucky_wheel/wheel@2x.json')
        .add('sound', "/public/assets/sound/sounds.mp3")
  }

  Loader
      .load(drawPixi.bind(app));
})


function createApplication() {
  const {gameWidth, gameHeight} = config.value;
  const app = new PIXI.Application({
    width: gameWidth,
    height: gameHeight,
    view: pixi.value,
    antialias: true,
    transparent: true,
    resolution: 2
  })

  return app;
}

async function drawPixi(loader, resource) {
  await LoadSound(resource)

  const wheel = new Wheel(config.value);
  this.stage.addChild(wheel);

  const banner = new Banner();
  this.stage.addChild(banner);

  const playButton = new PlayButton({x: 173.5 + 38, y: 173.5 + 268 + 32, text: 'Spin now'})
  playButton.animation()

  this.stage.addChild(playButton)

  if (config.value.spin) {
    playButton.visible = false
    wheel.spin.onDisable()
    const timeline = new Timeline(config.value);

    timeline.countdown(() => {
      timeline.destroy()
      playButton.visible = true
      playButton.animation()
      wheel.spin.onReset()
      playButton.setActive()
      this.stage.addChild(playButton)
    })
    this.stage.addChild(timeline)
  }

  wheel.spin.onClick(async () => {
        await this.onSpin()
      }
  )

  wheel.spin.onTouchend(async () => {
        await this.onSpin()
      }
  )

  playButton.on('click', async () => {
        await this.onSpin()
      }
  )

  playButton.on('touchend', async () => {
        await this.onSpin()
      }
  )

  this.onSpin = async () => {
    await sound.play('wheel', 'click');
    playButton.setDisabled();
    wheel.spin.onSpin((reward) => {
      store.commit('lucky/SET_SPIN', true);
      playButton.visible = false

      const timeline = new Timeline(config.value);

      timeline.countdown(() => {
        timeline.destroy()
        playButton.visible = true
        playButton.animation()
        wheel.spin.onReset()
        playButton.setActive();
        store.commit('lucky/SET_SPIN', false);
        this.stage.addChild(playButton)
      })

      this.stage.addChild(timeline)
      const popup = new Popup(config.value, reward, emit, store, router)
      this.stage.addChild(popup)
    });
  }

  if (debug) {
    const fpsDisplay = new FPSDisplay({FPSDisplayPosition: {x: 340, y: 3}}, this.ticker);
    this.stage.addChild(fpsDisplay);
  }
}

onUnmounted(() => {
  if (app) {
    app.destroy();
  }
})

function onClose() {
  emit('onClose')
}

</script>

<style scoped>

</style>
