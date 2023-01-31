import * as PIXI from 'pixi.js';

class Button extends PIXI.Sprite {
    constructor(params) {
        super();
        this.texture = PIXI.Texture.WHITE
        this.width = 424;
        this.height = 668;
        this.tint = 0;
        this.alpha = 0.7;
        this.anchor.set(.5)
        this.setActive()
    }

    setActive() {
        this.interactive = true;
    }

    setInactive() {
        this.interactive = true;
    }

    setDisabled() {
        this.interactive = false;
    }
}

export default Button;
