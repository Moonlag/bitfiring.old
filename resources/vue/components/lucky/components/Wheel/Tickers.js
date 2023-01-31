import * as PIXI from 'pixi.js';

const textStyle = {
    lineHeight: 15,
    fontSize: 14,
    fontWeight: 600,
    dropShadowDistance: 2,
    fill: 'white',
    dropShadow: true,
};

class Ticker extends PIXI.Container {
    constructor(index, text) {
        super();
        this.rotation = Number(index) * (Math.PI * 2 / 13);
        const content = new PIXI.Text(text.toUpperCase(), new PIXI.TextStyle(textStyle));
        content.x = 62
        content.y = 22
        content.anchor.set(0, 0.5)
        content.rotation = 0.35
        this.addChild(content)
    }
}


export default Ticker;