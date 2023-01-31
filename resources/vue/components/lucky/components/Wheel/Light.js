import * as PIXI from 'pixi.js';

class Light extends PIXI.Container {

    constructor(index) {
        const texture = PIXI.Texture.from("light.png")
        super();
        this.rotation = (Number(index) + 1) * Math.PI * 2 / 12;
        this.light = new PIXI.Sprite(texture)
        this.light.anchor.set(0.5)
        this.light.x = 162;
        this.light.y = -43;
        this.light.visible = index % 2 === 0;
        this.light.alpha = .8;
        this.animation(index)
        this.addChild(this.light)
    }

    animation(index){
        setInterval(() => {
            this.light.visible = Number(this.light.visible) % 2 === 0;
        }, 500)
    }

}


export default Light;
