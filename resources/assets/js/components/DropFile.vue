<template>
    <div v-if="!file">
        <div :class="['dropZone', dragging ? 'dropZone-over' : '']" @dragenter="dragging = true" @dragleave="dragging = false">
            <div class="dropZone-info" @drag="onChange">
                <span class="fa fa-cloud-upload dropZone-title"></span>
                <span class="dropZone-title">Drop file or click to upload</span>
                <div class="dropZone-upload-limit-info">
                    <div>extension support: txt</div>
                    <div>maximum file size: 5 MB</div>
                </div>
            </div>
            <input type="file" @change="onChange">
        </div>
    </div>
    <div v-else class="dropZone-uploaded">
        <div class="dropZone-uploaded-info">
            <span class="dropZone-title">Uploaded</span>
            <button type="button" class="btn btn-primary removeFile" @click="removeFile">Remove File</button>
        </div>
    </div>

</template>

<script setup>

import {ref} from "vue";

const file = ref('');
const dragging = ref(false)

function onChange(e) {
    var files = e.target.files || e.dataTransfer.files;

    if(!files.length) {
        this.dragging = false;
        return;
    }

    this.createFile(files[0])
}

</script>
