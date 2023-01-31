import * as PIXI from 'pixi.js';
import {Sine, gsap, Linear} from "gsap";

class Pap extends PIXI.Sprite {
    constructor(config) {
        super();
        this.texture = PIXI.Texture.from(config.texture);
        this.anchor.set(0.5);
        this.y = config.y
        this.animation(config)
    }

    animation(config){
        let timeline = gsap.timeline({duration: config.duration});
        this.y = -100
        this.scale.set(0.4)

        timeline
            .to(this, 0.7, {
                y: config.start.y,
                x: config.start.x,
                rotation: 2.5,
                pixi: {
                    scale: 1,
                },
                ease: Linear.easeOut
            })
            .to(this, 1.2, {
                pixi: {
                    scale: 1.5,
                },
                rotation: 5,
                zIndex: 10,
                y: config.end.y,
                x: config.end.x,
                ease: Linear.easeIn
            }).call(() => this.destroy());
    }

}

export default Pap;
