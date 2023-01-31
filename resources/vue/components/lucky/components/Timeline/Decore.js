import * as PIXI from "pixi.js";

const TextStyle = {
    fontSize: 30,
    fill: 'white',
    fontWeight: "bold"
}

class Decore extends PIXI.Text {
    constructor(config) {
        super(':', TextStyle);

        this.anchor.set(0.5)
        this.x = config.x
        this.y = config.y
    }

}


export default Decore;