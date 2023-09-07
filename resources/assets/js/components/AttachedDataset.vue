<template>
    <div class="card mb-2"
         :class="requiredMedia.dataset_id ? 'border-success' : 'border-dark'"
    >
        <div class="card-header d-flex justify-content-start align-items-center">
            <h3 class="mb-0 me-4">
                {{ requiredMedia.name }} ({{ requiredMedia.type }} file )
                <i class="la la-check-circle" v-if="requiredMedia.has_media"></i>
            </h3>
            <div class="form-check form-switch d-flex align-items-center mb-0">
                <input class="form-check-input me-2" type="checkbox" role="switch" id="is_static" v-model="requiredMedia.is_static">
                <label class="form-check-label" for="is_static">Is this a static media file?</label>
            </div>

        </div>

        <div class="card-body">
            <div class="row mb-4" v-if="!requiredMedia.is_static">
                <div class="col-md-6 col-12">
                    <div class="alert alert-info show text-dark">Select the dataset to link to the form. Each team will use their own data for the form.</div>
                </div>
                <div class="col-md-6 col-12">
                    <label>Select dataset:</label>
                    <vSelect
                        v-model="requiredMedia.dataset_id"
                        :options="datasets"
                        :reduce="option => option.value"
                    ></vSelect>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-success" @click="saveDataset">Save</button>
                </div>
            </div>
            <div class="row">
                <div class="col-12" v-if="!requiredMedia.is_static">
                    <hr/>
                    <div class="alert alert-info text-dark">
                        For testing, please upload an example csv file in the correct format. This csv file will also be used to inform teams what variables they need in their own dataset.
                    </div>
                </div>
                <div class="col-md-6 col-12">

                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <label class="font-bold me-4">Uploaded file: </label>
                        <a v-if="requiredMedia.has_media" :href="imageUrl">{{ requiredMedia.media[0].file_name }}</a>
                        <span v-else> ~ no file uploaded ~ </span>
                    </div>
                    <i class="d-flex" v-if="requiredMedia.has_media">
                        (uploaded on {{ new Date(requiredMedia.media[0].created_at).toDateString() }})
                        <br/>
                        You can replace this file by uploading another file.</i>
                </div>
                <div class="col-md-6 col-12 d-flex justify-content-center align-items-center">
                    <DragAndDropSingleUploader :required-media="requiredMedia" @fileUploaded="saveFiles"/>
                </div>
            </div>


        </div>
    </div>
</template>

<script setup>

import {computed, onMounted, ref} from "vue";
import DragAndDropSingleUploader from "./DragAndDropSingleUploader.vue";
import axios from "axios";
import vSelect from "vue-select";
import 'vue-select/dist/vue-select.css';

import {defineProps, defineEmits} from 'vue'
import Noty from "noty";
import "noty/src/noty.scss";
import "noty/src/themes/mint.scss";


const props = defineProps({
    requiredMediaInit: {
        type: Object,
        required: true
    }
})
const requiredMedia = ref({})
const datasets = ref([])

onMounted(() => {
    requiredMedia.value = props.requiredMediaInit

    const res = axios.get('/admin/datasets').then((res) => {
        datasets.value = res.data.map((dataset) => {
            return {
                label: dataset.name,
                value: dataset.id
            }

        })
    })
})

function saveFiles(file) {

    axios.postForm('/admin/required-media/' + requiredMedia.value.id + '/file', {
        uploaded_file: file,
        is_static: requiredMedia.value.is_static ? 1 : 0 // convert to int as postForm cannot handle boolean
    })
        .then((res) => {
            console.log(res)
            console.log('ok');

            requiredMedia.value = res.data.required_media
        })

}

const imageUrl = computed(() => {
    return requiredMedia.value.has_media ? requiredMedia.value.media[0].original_url : '';
})

function saveDataset() {
    axios.post('/admin/required-media/' + requiredMedia.value.id + '/dataset', {
        dataset_id: requiredMedia.value.dataset_id,
        is_static: requiredMedia.value.is_static,
    }).then((res) => {

        new Noty({
            'type': 'success',
            'text': 'Dataset linked successfully',
        }).show()

        requiredMedia.value = res.data.required_media
    })
}


</script>
