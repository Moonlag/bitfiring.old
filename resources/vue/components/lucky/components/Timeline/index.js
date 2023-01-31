import * as PIXI from "pixi.js";
import Num from "./Num";
import Decore from "./Decore";
import {Back, gsap} from "gsap";

const TextStyle = {
    fontSize: 30,
    fill: 'white',
    fontWeight: "bold"
}

class Timeline extends PIXI.Container {
    constructor(config) {
        super();
        this.position.set(173.5 + 19, 173.5 + 268 + 32)
        this.currentTime = new Date();
        this.deadline = new Date(config.re_spin * 1000);


        const h1 = new Num({x: -100})
        this.addChild(h1)

        const h2 = new Num({x: -56})
        this.addChild(h2)

        const m1 = new Num({x: 0})
        this.addChild(m1)

        const m2 = new Num({x: 44})
        this.addChild(m2)

        const s1 = new Num({x: 100})
        this.addChild(s1)

        const s2 = new Num({x: 144})
        this.addChild(s2)


        const decor1 = new Decore({x: -28, y: -3})
        this.addChild(decor1)

        const decor2 = new Decore({x: 72, y: -3})
        this.addChild(decor2)

        this.animation()

        this.updateTime = () => {
            h1.content.onUpdate(Math.floor(((this.currentTime / (1000 * 60 * 60)) % 24) / 10 ) || 0)
            h2.content.onUpdate(Math.floor(((this.currentTime / (1000 * 60 * 60)) % 24) % 10) || 0)

            m1.content.onUpdate(Math.floor(((this.currentTime / 1000 / 60) % 60) / 10) || 0)
            m2.content.onUpdate(Math.floor(((this.currentTime / 1000 / 60) % 60) % 10) || 0)

            s1.content.onUpdate(Math.floor(((this.currentTime / 1000) % 60) / 10) || 0)
            s2.content.onUpdate(Math.floor(((this.currentTime / 1000) % 60) % 10) || 0)
        }
    }

    animation() {
        this.alpha = 0
        const timeline = gsap.timeline({duration: 0.5})
        timeline.to(this, {alpha: 1, ease: Back.easeOut, duration: 1})
    }

    countdown(callback) {
        this.currentTime = Date.parse(this.deadline) - Date.parse(new Date());
        if (this.currentTime > 0) {
            this.updateTime()
            setTimeout(() => {this.countdown(callback)}, 1000);
        } else {
            callback()
            this.currentTime = null;
        }
    }

}


export default Timeline;