import * as PIXI from 'pixi.js';
import Light from "./Light";
import Ticker from "./Tickers";
import {sound} from '@pixi/sound';
import {Back, gsap, Power1} from 'gsap';
import axios from 'axios';

class WheelLucky extends PIXI.Sprite {
    constructor(config) {
        super();
        this.texture = PIXI.Texture.from('ring.png')
        this.anchor.set(0.5, 0.5);
        this.rotation = -2.70
        this.alpha = 0
        this.scale.set(0.5, 0.5)

        for (let index = 0; index < 13; index++) {
            if (index < 12) {
                const light = new Light(index);
                this.addChild(light);
            }
            const ticker = new Ticker(index, config.tickers[index].icon)
            this.addChild(ticker)
        }
    }

    animation() {
        gsap.to(this.scale, {x: 1, y: 1, ease: Back.easeOut})
        gsap.to(this, {rotation: 0.12, alpha: 1, ease: Back.easeOut})
    }

    onSpin() {
        return new Promise(async (resolve) => {
            const win = await this.getWinnId();
            await this.animationSpin(win.win_id)
            resolve(win)
        })
    }

    animationSpin(win) {
        return new Promise(async (resolve) => {
            await sound.play('wheel', 'spin');
            this.rotation = 0.12;
            const rotation = (this.rotation - this.rotation % 360 + 6 * 360 - (360 * win / 13)) * (Math.PI / 180);
            let tl = gsap.timeline();
            tl.to(this, 3, {rotation: `+=${rotation}`, delay: 0.1, ease: Power1.easeOut}).call(() => resolve(win))
        })
    }

    getWinnId() {
        return new Promise(async (resolve, reject) => {
            await axios.post('/lucky_wheel/spin').then(response => {
                const {data} = response
                resolve(data)
            })
        })
    }
}

export default WheelLucky;
