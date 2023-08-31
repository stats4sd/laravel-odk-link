<template>
    <p>Your phrase of the day is: {{ phrase }}</p>
</template>

<script setup>

import {onMounted, ref} from "vue";
import axios from "axios";

const phrase = ref('')

onMounted(async () => {
    const res = await axios.get('https://api.api-ninjas.com/v1/dadjokes?limit=1', {
        headers: { 'X-Api-Key': 'CY4gyQEBW2BQekOKxD4yFg==M0Je6s3ECSeVbPCM'}
    });

    phrase.value = res.joke
})


</script>
