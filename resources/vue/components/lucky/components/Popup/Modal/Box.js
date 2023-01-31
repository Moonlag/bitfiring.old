import * as PIXI from 'pixi.js';

class Box extends PIXI.NineSlicePlane {
    constructor(config) {
        super(PIXI.Texture.from("box1.png"), 6, 6, 6, 0);
        this.x = config.x;
        this.y = config.y;
        this.width = config.width;
        this.height = config.height;
        this.scale.set(config.scale.x, config.scale.y);
    }

}

export default Box;
