<script setup>
import { Link } from '@inertiajs/vue3';

defineProps ({
    pagination: Object,

});


</script>
<template>
    <!-- <nav class="relative flex justify-end ">
        <div class="d-flex flex-column align-items-center">
            <span class="">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        </div>
        <template v-for="(link, k) in pagination.links" :key="k">
            <Link 
                preserve-scroll
                :href="link.url ?? ''" 
                v-html="link.label"
                class="flex items-center justify-center px-3 py-2 text-sm rounded-lg text-gray-600" 
                :class="{'bg-gray-200': link.active, '!text-gray-300': !link.url}"/>
        </template>
        
    </nav> -->
    <div class="d-flex flex-column align-items-center">
        <span class="">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <nav aria-label="Page navigation" class="no-print">
            <ul class="pagination">
                <li v-for="(link, k) in pagination.links" :key="k" class="page-item" >
                    <!-- <Link class="page-link" :href="link.url" v-html="link.label"/> -->
                    <Link 
                        preserve-scroll
                        :href="link.url ?? ''" 
                        v-html="link.label"
                        class="page-link" 
                        :class="{ 'active': link.active, 'disabled': !link.url }"/>
                </li>
            </ul>
        </nav>
    </div>
</template>

