import { createApp } from 'vue';

const app = createApp({});

import RequiredFixedMediaUploader from './components/RequiredFixedMediaUploader.vue';
import AttachedDataset from './components/AttachedDataset.vue';
import RequiredMediaList from "./components/RequiredMediaList.vue";
import RequiredDatasetList from "./components/RequiredDatasetList.vue";

app.component('required-fixed-media-uploader', RequiredFixedMediaUploader);
app.component('attached-dataset', AttachedDataset);
app.component('required-media-list', RequiredMediaList);
app.component('required-dataset-list', RequiredDatasetList);

app.mount('#xlsform-template-review');
