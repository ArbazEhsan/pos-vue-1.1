<script setup>
import { ref, watch } from 'vue';
import { router } from "@inertiajs/vue3";
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    txt: {
        type: String,
        required: true, // Consider adding validation
    },

    placeholder: { // Corrected typo
        type: String,
    }
});

const search = ref('');
let delayTimeout;

watch(search, (value) => {
    clearTimeout(delayTimeout);
    delayTimeout = setTimeout(() => {
        router.get(props.txt, { search: value }, { preserveState: true, replace: true });
    }, 500);
});
</script>

<template>
    <div class="">
        <!-- <label class="text-sm">Search:</label> -->
        <TextInput id="search" type="text" class="form-control" v-model="search" :placeholder="placeholder"/>
    </div>
</template>