import * as PIXI from 'pixi.js';
import {Back, gsap, Linear, Power0} from 'gsap';

class Title extends PIXI.Sprite {
    constructor() {
        super();
        this.texture = PIXI.Texture.from('title1.png')
        this.anchor.set(.5)
        this.y = 10
    }
}

export default Title;
