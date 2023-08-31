import {createApp} from 'vue/dist/vue.esm-bundler';

import RequiredDataMedia from "./components/RequiredDataMedia.vue";
import {Suspense} from "vue";

createApp()
    .component('required-data-media', RequiredDataMedia)
    .component('Suspense', Suspense)
    .mount('#required-data-media')
