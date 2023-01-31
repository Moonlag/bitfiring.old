import * as PIXI from 'pixi.js';
import Title from "./Title";
import {Circ, gsap, Linear, Power0} from 'gsap';

class Flag extends PIXI.Sprite {
    constructor() {
        super();
        this.texture = PIXI.Texture.from('flag.png')
        this.anchor.set(.5)
        this.y = 100;

        this.animation().then(() => {
            const title = new Title();
            this.addChild(title)
        })
    }

    animation(){
        return new Promise((resolve, reject) => {
            const timeline = gsap.timeline()
            timeline.to(this, {y: 0, ease: Circ.easeOut, duration: 0.5}).call(resolve)
        })
    }
}

export default Flag;
