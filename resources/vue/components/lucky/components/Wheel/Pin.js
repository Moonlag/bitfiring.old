import * as PIXI from 'pixi.js';
import {Back, gsap, Linear, Power0} from 'gsap';

class Pin extends PIXI.Sprite {
    constructor(tickers) {
        super();
        this.texture = PIXI.Texture.from('bracket.png')
        this.anchor.set(1, 0.5)
        this.scale.set(0, 0)
        this.x = 173

        this.animation();
    }

    animation() {
        this.x = 255
        this.alpha = 0.7
        const timeline = gsap.timeline({duration: 0.15})

        timeline.to(this, {
            pixi: {
                scale: 1,
                x: 173
            }, alpha: 1, ease: Back.easeOut
        })
    }

    animationSpin(callback) {
        return new Promise((resolve => {
            let timeline = gsap.timeline();

            timeline
                .to(this, .15, {
                    pixi: {
                        scale: 1.3,
                    },
                })
                .to(this, .3, {
                    pixi: {
                        scale: 1
                    },
                    ease: Back.easeOut
                });

            callback();

            timeline.call(resolve)
        }))

    }
}

export default Pin;
