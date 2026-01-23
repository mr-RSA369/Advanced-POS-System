import "./bootstrap";
import { createApp, h } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import Layout from "./layouts/layout.vue";

createInertiaApp({
    progress: {
        // The color of the progress bar...
        color: "darkblue",
        // Whether to include the default NProgress styles...
        includeCSS: true,
        // Whether the NProgress spinner will be shown...
        showSpinner: true,
    },
    title: (title) => `My App${title}`,
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        let page = pages[`./Pages/${name}.vue`];
        if (page.default.layout === undefined) {
            page.default.layout = Layout;
        }
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .component("Head", Head)
            .component("Link", Link)
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
});
