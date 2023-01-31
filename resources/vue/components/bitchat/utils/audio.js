import {ref} from "vue";
const src = "/sounds/you-have-new-message-484.ogg"

const defaultAudioOptions = {
    src: src,
    autoplay: true,
    loop: false,
    muted: false,
    volume: 0.5,
};

export default function useAudio() {

    function createAudio(options) {
        const audio = document.createElement('audio')

        const {src, autoplay, loop, volume, muted} = Object.assign({...defaultAudioOptions}, options);

        audio.src = src;
        audio.style.display = "none";
        audio.autoplay = autoplay;
        audio.volume = volume;
        audio.loop = loop;
        audio.muted = muted;

        if (!loop) {
            audio.onended = function () {
                audio.remove(); //remove after playing to clean the Dom
            };
        }

        audio.addEventListener("load", async function () {
            await audio.play();
        }, true);


        document.body.appendChild(audio);
    }

    return {
        createAudio
    }
}
