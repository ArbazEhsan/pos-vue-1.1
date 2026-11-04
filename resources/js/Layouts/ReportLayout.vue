<script setup>
</script>

<template>
    <div>
        <slot />
    </div>
</template>
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .copyrights {
            display: block !important;
        } 

        /* Hide everything initially */
        body * {
            visibility: hidden;
        }

        /* Make the printable area visible */
        #printableArea, #printableArea * {
            visibility: visible;
        }
    }
    
</style>
