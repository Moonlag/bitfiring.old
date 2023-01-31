import * as PIXI from 'pixi.js';
import {Power4, gsap, Bounce} from "gsap";

class Decore extends PIXI.Sprite {
    constructor(params) {
        super();
        this.texture = PIXI.Texture.from('more_gold.png');
        this.anchor.set(0.5);
        this.y = -170
    }


    animation(){
        let timeline = gsap.timeline();
        this.y = 0
        this.scale.set(0.4)

        timeline
            .to(this, 0.5, {
                y: -190,
                pixi: {
                    scale: 0.8
                },
                ease: Power4.easeInOut
            })
            .to(this, .5, {
                pixi: {
                    scale: 1
                },
                zIndex: 5,
                y: -170,
                ease: Bounce.easeOut
            });
    }

}

export default Decore;
