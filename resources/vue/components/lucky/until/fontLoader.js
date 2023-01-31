import * as WebFont from "webfontloader";

export const WebFontLoader = () => {
    WebFont.load({
        custom: {
            families: [ "Raleway", "Raleway900" ],
        },
        fontactive: (name, fvd) => {
            console.log(`Loading font files ${name}:${fvd}`);
        },
        active: () => {
            console.log("All font files loaded.");
            // CONTINUE here
        },
        inactive: () => {
            console.error("Error loading fonts");
            // REPORT ERROR here
        },
    });
}
