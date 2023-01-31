import * as PIXI from 'pixi.js';
import Button from "../UI/Button";
import {gsap, Linear} from 'gsap';

class SpinButton extends PIXI.Sprite {
    constructor(config) {
        const texture = PIXI.Texture.from('spin_text.png')
        super();
        this.texture = PIXI.Texture.from('spin_button.png')

        this.anchor.set(0.5);
        this.hitArea = new PIXI.Circle(0, 0, 50)

        this.text = new PIXI.Sprite(texture)
        this.text.anchor.set(0.5);
        this.addChild(this.text)

        this.timeline = gsap.timeline({yoyo: true, repeat: -1})

        this.animate()
    }

    setActive() {
        this.timeline.resume(true)
    }

    setInactive() {
        this.timeline.pause(0, true)
    }

    setDisabled() {
        this.timeline.pause(0, true)
    }

    animate(){
        this.timeline.fromTo(this.text.scale, {x: 1, y: 1, ease: Linear.easeIn}, {
            duration: 1,
            x: 1.2,
            y: 1.2,
            ease: Linear.easeIn
        });

        this.timeline.fromTo(this.text, {rotation: 0, ease: Linear.easeIn}, {
            duration: 1,
            rotation: 0.1,
            ease: Linear.easeIn
        }, ">-1");

        this.timeline.fromTo(this.scale, {x: 1, y: 1, ease: Linear.easeIn}, {
            duration: 1,
            x: 1.1,
            y: 1.1,
            ease: Linear.easeIn
        }, ">-1");

        this.timeline.pause(0, true)
    }
}

export default SpinButton;
