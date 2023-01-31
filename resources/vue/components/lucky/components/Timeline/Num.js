import * as PIXI from 'pixi.js';
import {Power4, gsap, Bounce} from "gsap";

const text = 0;
const TextStyle = {
    fontSize: 30,
    fill: 'white',
    fontWeight: "bold"
};

class Num extends PIXI.Sprite {
    constructor(config) {
        super();
        this.texture = PIXI.Texture.from('num_bg.png');
        this.anchor.set(0.5);
        this.x = config.x

        const content = new PIXI.Text(text, TextStyle)
        content.anchor.set(0.5)

        this.content = {
            onUpdate: (payload) => {
                content.text = payload
            }
        }

        this.addChild(content)
    }

}

export default Num;
