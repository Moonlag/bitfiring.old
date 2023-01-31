import * as PIXI from 'pixi.js';
import {gsap, Linear, Circ, Back, Power1} from 'gsap';

class Button extends PIXI.Sprite {
    constructor(params) {
        super();
        this.activeTexture = params.activeTexture;
        this.inactiveTexture = params.inactiveTexture;
        this.disabledTexture = params.disabledTexture;
        this.buttonMode = true;
        this.setInactive();
    }

    setActive() {
        this.texture = this.activeTexture;
        this.interactive = true;
    }

    setInactive() {
        this.texture = this.inactiveTexture;
        this.interactive = true;
    }

    setDisabled() {
        this.texture = this.disabledTexture;
        this.interactive = false;
    }
}

export default Button;
