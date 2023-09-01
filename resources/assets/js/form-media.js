import { createApp } from 'vue';

const app = createApp({});

import RequiredFixedMediaUploader from './components/RequiredFixedMediaUploader.vue';

app.component('required-fixed-media-uploader', RequiredFixedMediaUploader);

app.mount('#fixed-media-vue');
