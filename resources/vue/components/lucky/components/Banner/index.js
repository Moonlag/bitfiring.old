import * as PIXI from 'pixi.js';
import Flag from "./Flag";
import {Back, gsap, Linear, Power0} from 'gsap';

class Banner extends PIXI.Container {
    constructor() {
        super();

        this.position.set(173.5 + 38, 360)

        const flag = new Flag();
        this.addChild(flag)
    }
}

export default Banner;
