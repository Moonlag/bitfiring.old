import * as PIXI from "pixi.js";
import SpinButton from "./SpinButton";
import {Back, gsap} from "gsap";

class Button extends PIXI.Container {
    constructor() {
        super();
        this.button = new SpinButton()
        this.buttonMode = true;

        this.addChild(this.button)
    }

    setActive() {
        this.interactive = true;
        this.button.setActive()
    }

    setInactive() {
        this.interactive = true;
        this.button.setInactive()
    }

    setDisabled() {
        this.interactive = false;
        this.button.setDisabled()
    }

    pointerdown() {
        gsap.to(this.scale, {x: .9, y: .9, duration: 0.2})
    }

    pointerup() {
        gsap.to(this.scale, {x: 1, y: 1, duration: 0.2})
    }

    pointerupoutside() {
        gsap.to(this.scale, {x: 1, y: 1, duration: 0.2})
    }

    animation(config) {
        this.scale.set(0, 0)
        this.alpha = 0.5
        const timeline = gsap.timeline({duration: 0.2})
        timeline.to(this, {
            pixi: {
                scale: 1
            }, alpha: 1, ease: Back.easeOut, duration: 0.5
        }).call(() => {
            if(!config.spin){
                this.setActive()
            }

        })

    }
}


export default Button;