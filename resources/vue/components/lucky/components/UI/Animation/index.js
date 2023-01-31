export const onClick = (el) => {
    let isDown = false, isUp = true, scale = 1

    el.interactive = true;
    el.buttonMode = true;

    el.pointerdown = function () {
        sound.play('alias', 'click');
        isDown = true
        isUp = false
    }

    el.pointerup = function () {
        isDown = false
        isUp = true
    }

    el.pointerupoutside = function () {
        isDown = false
        isUp = true
    }

    function animate() {
        if (!isDown && !isUp) {
            return
        }

        if (scale > .9 && isDown) {
            scale -= 0.005
        }

        if (scale < 1 && isUp) {
            scale += 0.005;
        }

        el.scale.set(scale)
        requestAnimationFrame(animate);
    }

    animate()
}