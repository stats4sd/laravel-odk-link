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
                    <div v-bind="getRootProps(style)" :style="style">
                        <input v-bind="getInputProps()"/>
                        <p v-if="isDragActive">Drop the files here ...</p>
                        <p v-else-if="uploadErrors.length > 0">
                            <span v-for="error in uploadErrors" class="text-danger">{{ error }}</span>
                        </p>
                        <p v-else>Drag 'n' drop your file here, or click to select a file</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import {useDropzone} from "vue3-dropzone";
import {computed, onMounted, ref} from "vue";
import axios from "axios";

// ***** Dropzone Setup *****
function saveFiles(acceptFiles) {
    // get file
    const file = acceptFiles[0];

    let formData = new FormData();
    formData.append('uploaded_file', file);

    axios.post('/admin/required-media/'+requiredMedia.value.id, formData, {
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

const uploadErrors = ref([]);

function onDrop(acceptFiles, rejectReasons) {
    uploadErrors.value = [];


    if(rejectReasons.length > 0) {

        console.log(rejectReasons);

        let messages = rejectReasons.map((reason) => {
            return reason.errors.map((error) => error.message);
        }).flat()

        // if any error is about multiple files, that's the most important one to return
        if(messages.includes('Too many files')) {
            uploadErrors.value[0] = "Please upload a single file";
        } else {
            uploadErrors.value = messages
        }


        return;
    }

    saveFiles(acceptFiles);

}

const acceptedFileTypes = computed(() => {
    return requiredMedia.value.type + '/*';
})

const {
    getRootProps,
    getInputProps,
    isDragActive,
    isFocused,
    isDragAccept,
    isDragReject,
    ...rest
} = useDropzone({
    accept: acceptedFileTypes,
    onDrop: onDrop,
    maxFiles: 1,
});

const baseStyle = {
  flex: 1,
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  padding: '20px',
  borderWidth: 2,
  borderRadius: 2,
  borderColor: '#eeeeee',
  borderStyle: 'dashed',
  backgroundColor: '#fafafa',
  color: '#bdbdbd',
  outline: 'none',
  transition: 'border .24s ease-in-out'
};

const focusedStyle = {
  borderColor: '#2196f3'
};

const acceptStyle = {
  borderColor: '#00e676'
};

const rejectStyle = {
  borderColor: '#ff1744'
};

const style = computed(() => {
    return {
        ...baseStyle,
        ...(isFocused.value ? focusedStyle : {}),
        ...(isDragAccept.value ? acceptStyle : {}),
        ...(isDragReject.value ? rejectStyle : {}),
    }
})

const props = defineProps({
    requiredMediaInit: Object,
});

const requiredMedia = ref({});
const imageUrl = computed(() => {
    console.log('ha');
    return requiredMedia.value.media ? requiredMedia.value.media[0].original_url : '';
})

onMounted(() => {
    requiredMedia.value = props.requiredMediaInit
})

</script>
