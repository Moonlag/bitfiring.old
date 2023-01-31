import { createSSRApp, h } from "vue";
import { renderToString } from "@vue/server-renderer";
import { createInertiaApp } from "@inertiajs/inertia-vue3";
import createServer from "@inertiajs/server";
import DefaultLayout from "./layouts/DefaultLayout";
import PlayLayout from "./layouts/PlayLayout";
import ProfileLayout from "./layouts/ProfileLayout";
import Select2 from "vue3-select2-component";
import BubbleTopComponent from "./components/buble/BubbleTopComponent";



createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: (name) => require(`./views/${name}`),
        setup({ el, app, props, plugin }) {
            return createSSRApp({
                render: () => h(app, props),
            })
                .use(plugin)
                .mount(el)
                .component('default-layout', DefaultLayout)
                .component('play-layout', PlayLayout)
                .component('profile-layout', ProfileLayout)
                .component('Select2', Select2)
                .component('BubbleTopComponent', BubbleTopComponent);
        },
    })
);
