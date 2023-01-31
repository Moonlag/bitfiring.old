import * as PIXI from "pixi.js";
import Overlay from "./Overlay";
import Modal from "./Modal";
import Decore from "./Decore";
import Title from "./Title";
import ButtonMain from "../Button";
import Reward from "./Reward";
import Pap from "./Pap";
import {sound} from '@pixi/sound';

const StatusIDs = {
    1: 1,
    2: 2,
    3: 2,
    4: 1,
    5: 1,
    6: 1,
    7: 2,
    8: 2,
    9: 3,
    10: 3,
    11: 3,
    12: 3,
    13: 4,
}

const Text = {
    0: 'Register & Claim',
    1: 'Claim & Play',
    2: 'Claim & Deposit',
    3: 'Claim & Activate',
    4: 'Better luck tomorrow!'
}

class Popup extends PIXI.Container {
    constructor(config, winn, emit, store, router) {
        super();
        const {win_id: win, id: reward_id, data} = winn;

        this.sortableChildren = true;

        const overlay = new Overlay();
        this.addChild(overlay);
        this.position.set(173.5 + 38, 334);

        const decore = new Decore()
        decore.animation()
        this.addChild(decore)

        const pap1 = new Pap({
            texture: `pap1.png`,
            y: 0,
            duration: 0.2,
            start: {y: -500, x: -200},
            end: {y: 400, x: -300}
        })
        this.addChild(pap1)

        const pap2 = new Pap({
            texture: `pap2.png`,
            y: 0,
            duration: 0.15,
            start: {y: -500, x: -150},
            end: {y: 400, x: -300}
        })
        this.addChild(pap2)

        const pap3 = new Pap({
            texture: `pap3.png`,
            y: 0,
            duration: 0,
            start: {y: -500, x: -100},
            end: {y: 400, x: -200}
        })
        this.addChild(pap3)

        const pap4 = new Pap({
            texture: `pap4.png`,
            y: 0,
            duration: 0.13,
            start: {y: -500, x: -50},
            end: {y: 400, x: -250}
        })
        this.addChild(pap4)

        const pap5 = new Pap({texture: `pap5.png`, y: 0, duration: 0.1, start: {y: -500, x: 0}, end: {y: 400, x: -200}})
        this.addChild(pap5)

        const pap6 = new Pap({texture: `pap6.png`, y: 0, duration: 0.2, start: {y: -500, x: 50}, end: {y: 400, x: 250}})
        this.addChild(pap6)

        const pap7 = new Pap({texture: `pap7.png`, y: 0, duration: 0, start: {y: -500, x: 100}, end: {y: 400, x: 300}})
        this.addChild(pap7)

        const pap8 = new Pap({
            texture: `pap1.png`,
            y: 0,
            duration: 0.15,
            start: {y: -500, x: 150},
            end: {y: 400, x: 350}
        })
        this.addChild(pap8)


        const modal = new Modal();
        modal.animation();
        this.addChild(modal)

        const title = new Title()
        modal.setChild(title)

        const button = new ButtonMain({x: 0, y: 70, text: config.auth ? Text[StatusIDs[win]] : Text[0]})
        modal.setChild(button)

        button.on('click', () => {
            this.destroy()
            sound.play('wheel', 'gold')

            emit('onClose')

            if (config.auth) {
                switch (StatusIDs[win]) {
                    case 1:
                        emit('onFreespin', data)
                        break;
                    case 2:
                        router.push({
                            name: 'deposit',
                            params: {
                                bonuses: reward_id
                            }
                        })
                        break;
                    case 3:
                        router.push({
                            name: 'promo-bonuses',
                        })
                    default:
                }
            }else {
                store.commit('open_pop', 1)
            }

        })

        button.on('touchend', () => {
            this.destroy()
            sound.play('wheel', 'gold')

            emit('onClose')

            if (config.auth) {
                switch (StatusIDs[win]) {
                    case 1:
                        emit('onFreespin', data)
                        break;
                    case 2:
                        router.push({
                            name: 'deposit',
                            params: {
                                bonuses: reward_id
                            }
                        })
                        break;
                    case 3:
                        router.push({
                            name: 'promo-bonuses',
                        })
                    default:
                }
            }else {
                store.commit('open_pop', 1)
            }

        })

        const ticker = config.tickers.find(el => el.id === win);

        const reward = new Reward({text: ticker.winn});
        modal.setChild(reward)
    }

}


export default Popup;
