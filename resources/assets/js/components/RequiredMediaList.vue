<template>
    <required-fixed-media-uploader v-for="requiredMedia in requiredFixedMedia" :key="requiredMedia.id" :required-media-init="requiredMedia" />
</template>

<script setup>

const props = defineProps({
    requiredFixedMedia: Array,
});

</script>
