<template>
    <div v-bind="getRootProps(style)" :style="style">
        <input v-bind="getInputProps()"/>
        <p v-if="isDragActive">Drop the files here ...</p>
        <p v-else-if="uploadErrors.length > 0">
            <span v-for="error in uploadErrors" class="text-danger">{{ error }}</span>
        </p>
        <p v-else>Drag 'n' drop your file here, or click to select a file</p>
    </div>
</template>


<script setup>

import {useDropzone} from "vue3-dropzone";
import {computed, ref} from "vue";


const emit = defineEmits(['file-uploaded']);

const props = defineProps({
    requiredMedia: {
        type: Object,
        required: true
    }
})

const acceptedFileTypes = computed(() => {

    if(props.requiredMedia.type === 'file') {
        return 'text/csv';
    }

    return props.requiredMedia.type + '/*';
})

const uploadErrors = ref([]);



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

function onDrop(acceptFiles, rejectReasons) {
    uploadErrors.value = [];


    if (rejectReasons.length > 0) {

        console.log(rejectReasons);

        let messages = rejectReasons.map((reason) => {
            return reason.errors.map((error) => error.message);
        }).flat()

        // if any error is about multiple files, that's the most important one to return
        if (messages.includes('Too many files')) {
            uploadErrors.value[0] = "Please upload a single file";
        } else {
            uploadErrors.value = messages
        }


        return;
    }

    saveFiles(acceptFiles);

}

function saveFiles(acceptFiles) {
    // get file
    const file = acceptFiles[0];

    emit('file-uploaded', file);
}

// Style stuff

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



</script>
