import * as PIXI from 'pixi.js';
import Button from '../UI/Button';
import {Back, gsap} from "gsap";

const TextStyle = {
    fontSize: 16,
    fontWeight: "bold",
    fill: "white"
}

class ButtonMain extends Button {
    constructor(config) {
        super({
            activeTexture: PIXI.Texture.from('button.png'),
            inactiveTexture: PIXI.Texture.from('button.png'),
            disabledTexture: PIXI.Texture.from('button.png')
        });
        this.position.set(config.x, config.y);
        this.anchor.set(0.5)
        const content = new PIXI.Text(config.text, TextStyle);
        content.anchor.set(0.5)
        this.addChild(content);
    }

    animation() {
        this.alpha = 0
        const timeline = gsap.timeline({duration: 0.5})
        timeline.to(this, {alpha: 1, ease: Back.easeOut, duration: 1})
    }

    pointerdown(){
        gsap.to(this.scale, {x: .9, y: .9, duration: 0.2})
    }

    pointerup(){
        gsap.to(this.scale, {x: 1, y: 1, duration: 0.2})
    }

    pointerupoutside(){
        gsap.to(this.scale, {x: 1, y: 1, duration: 0.2})
    }
}

export default ButtonMain;
