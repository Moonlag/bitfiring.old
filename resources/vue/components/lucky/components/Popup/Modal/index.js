import * as PIXI from "pixi.js";
import Box from "./Box";
import {Back, gsap} from "gsap";


class Modal extends PIXI.Container {
    constructor() {
        super();

        const boxTop = new Box({x: -180, y: -268 / 2, width: 360, height: 149, scale: {x: 1, y: 1}});
        const boxBottom = new Box({x: -180, y: 268 / 2, width: 360, height: 149, scale: {x: 1, y: -1}});
        this.addChild(boxTop, boxBottom)
    }

    setChild(payload) {
        this.addChild(payload)
    }

    animation(){
        this.scale.set(0.3)
        gsap.to(this, {
            pixi: {
                scale: 1
            }, duration: .5, ease: Back.easeOut
        })
    }
}


export default Modal;