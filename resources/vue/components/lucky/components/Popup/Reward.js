import * as PIXI from "pixi.js";

const TextStyle = {
    fontSize: 28,
    fontFamily: 'Raleway900, sans-serif',
    fontWeight: 900,
    lineHeight: 24,
    fill: '#f28e15',
    dropShadow: true,
}

class Reward extends PIXI.Text {
    constructor(config) {
        super(config.text.toUpperCase(), TextStyle);
        this.anchor.set(0.5)
        this.y = -15;
    }
}


export default Reward;