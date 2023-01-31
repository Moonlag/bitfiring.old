import * as PIXI from 'pixi.js';

const textStyle = {
    fontSize: 18,
    fill: '#F4802C',
    align: 'right',
    fontWeight: 700,
    fontFamily: 'Raleway, sans-serif'
};

class FPSDisplay extends PIXI.Container {
    constructor(config, ticker = PIXI.Ticker.shared) {
        super();

        this.position.set(config.FPSDisplayPosition.x, config.FPSDisplayPosition.y);
        const content = new PIXI.Text('', textStyle);
        this.addChild(content);

        setInterval(() => {
            const fps = ticker.FPS.toFixed(0);
            content.text = `FPS: ${fps}`;
        }, 1000);
    }
}

export default FPSDisplay;
