<template>
   <AttachedDataset :required-media-init="datasetMedia" v-for="datasetMedia in requiredDataMedia" :key="datasetMedia.id" />
</template>

<script setup>

import AttachedDataset from "./AttachedDataset.vue";

const props = defineProps({
    requiredDataMedia: Array,
});

</script>
