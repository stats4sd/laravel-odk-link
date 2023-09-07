<template>
    <div class="card mb-2"
         :class="requiredMedia.has_media ? 'border-success' : 'border-dark'"
    >
        <div class="card-header">
            <h3 class="mb-0">
                {{ requiredMedia.name }} ( {{ requiredMedia.type }} file )
                <i class="la la-check-circle" v-if="requiredMedia.has_media"></i>

            </h3>
        </div>

        <div class="card-body">

            <div class="row">
                <div class="col-md-6 col-12 d-flex justify-content-center align-items-center">
                    <img v-if="requiredMedia.type === 'image' && requiredMedia.has_media" :src="imageUrl" width="120" class="me-4 ">
                    <a v-if="requiredMedia.has_media" :href="imageUrl">{{ requiredMedia.media[0].file_name}}</a>
                </div>
                <div class="col-md-6 col-12">
                    <DragAndDropSingleUploader
                        :required-media="requiredMedia"
                        @fileUploaded="saveFiles"
                    />
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>

import {computed, onMounted, ref} from "vue";
import axios from "axios";
import DragAndDropSingleUploader from "./DragAndDropSingleUploader.vue";

// ***** Dropzone Setup *****
function saveFiles(file) {

    let formData = new FormData();
    formData.append('uploaded_file', file);

    axios.post('/admin/required-media/'+requiredMedia.value.id+'/file', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    })
        .then((res) => {
            console.log(res)
            console.log('ok');

            requiredMedia.value = res.data.required_media
        })

}


const props = defineProps({
    requiredMediaInit: Object,
});

const requiredMedia = ref({});
const imageUrl = computed(() => {
    return requiredMedia.value.media ? requiredMedia.value.media[0].original_url : '';
})

onMounted(() => {
    requiredMedia.value = props.requiredMediaInit
})

</script>
