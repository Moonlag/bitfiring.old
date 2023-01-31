import * as PIXI from 'pixi.js';

const TextStyle = {
    fontSize: 28,
    fontFamily: 'Raleway900, sans-serif',
    fontWeight: 900,
    lineHeight: 24,
    fill: '#f28e15',
    dropShadow: true,
}

class Title extends PIXI.Text {
    constructor(config) {
        super('You`ve won'.toUpperCase(), TextStyle);
        this.anchor.set(0.5);
        this.y = -50
    }

}

export default Title;
