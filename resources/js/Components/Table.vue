<script setup>

const props = defineProps({
    columns: Array,
    rows: Array,
    footer: Array || null,
    theadClass: String || 'text-sm',
});



</script>

<template>
    <table class="table">
        <thead :class="['text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400', theadClass]">
            <tr>
                <th v-for="(column, index) in columns" :key="index">{{ column.label }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
                <td v-for="(column, colIndex) in columns" :key="colIndex">
                  {{ row[column.key] }} 
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr v-if="footer">
               <td v-for="(item, index) in footer" :key="index">{{ item }}</td>
            </tr>
        </tfoot>
    </table>
</template>
