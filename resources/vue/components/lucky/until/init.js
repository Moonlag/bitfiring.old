import {gsap} from 'gsap';
import {CSSPlugin} from 'gsap/CSSPlugin'
import {PixiPlugin} from 'gsap/PixiPlugin'
import * as PIXI from "pixi.js";

export const init = () => {
    gsap.registerPlugin(CSSPlugin);
    gsap.registerPlugin(PixiPlugin);
    PixiPlugin.registerPIXI(PIXI);
}
