import * as PIXI from 'pixi.js';
import Light from "./Light";
import Ticker from "./Tickers";
import Button from "./Button";
import WheelLucky from "./WheelLucky";
import Pin from "./Pin";
import {Back, gsap} from "gsap";

class Wheel extends PIXI.Container {
    constructor(config) {
        const texture = PIXI.Texture.from("ring.png")
        super();
        this.position.set(173.5 + 38, 173.5 + 13)

        const wheel = new WheelLucky(config)
        wheel.animation()
        this.addChild(wheel)

        const mask = this.setMask()

        this.addChild(mask.mask1)
        this.addChild(mask.mask2)

        const button = new Button()
        button.animation(config)
        this.addChild(button)

        const pin = new Pin()
        pin.animation()
        this.addChild(pin)

        this.spin = {
            onSpin: (callback) => {
                button.setDisabled()
                wheel.onSpin().then(response => {
                    pin.animationSpin(mask.animationSpin)
                        .then(() => callback(response))
                })
            },
            onClick: (callback) => button.on('click', callback),
            onTouchend: (callback) => button.on('touchend', callback),
            onReset:  () => button.setActive(),
            onDisable: () => button.setDisabled(),
        }
    }

    setMask() {
        const mask1 = new PIXI.Graphics()
        mask1.beginFill(16777215, .35);
        mask1.drawRect(0, 0, 20, 100);
        mask1.endFill()
        mask1.x = 30;
        mask1.y = -50;
        mask1.visible = false

        const mask2 = new PIXI.Graphics()
        mask2.x = 25;
        mask2.y = -22
        mask2.rotation = -0.16
        mask2.beginFill(0);
        // mask2.drawRect(0, 0, 20, 100);
        mask2.moveTo(0, 12);
        mask2.lineTo(130, 0);
        mask2.lineTo(123, 80);
        mask2.lineTo(0, 32);
        mask2.closePath();
        mask2.endFill();

        mask1.skew.set(-Math.PI / 8, 0)
        mask1.mask = mask2

        return {
            mask1,
            mask2,
            animationSpin: () => {
                const timeline = gsap.timeline();
                mask1.visible = true
                timeline
                    .fromTo(mask1, {
                        pixi: {
                            x: 30,
                            scale: 1
                        },
                    }, {
                        duration: 1.2,
                        pixi: {
                            x: 290,
                            scale: 1.5
                        }
                    }, 0)
            }
        }
    }

}


export default Wheel;
