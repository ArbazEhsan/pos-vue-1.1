<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>

    <div class="flex vh-100">
        <aside class="bg-green-100 w-[20%]">
            <h6>Menu</h6>
        </aside>

        <main class="flex-1 overflow-y-auto bg-gray-100">
            <div class="container-fluid">
                <slot />
            </div>
        </main>
    </div>


</template>