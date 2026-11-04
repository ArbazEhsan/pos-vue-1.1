<script setup>

defineProps({
    title: {
        type: String,
    },
    modal_id: {
        type: String,
    },
    Class:  {
        type: String,
        default: 'sm:max-w-1xl',
    },
    
});

</script>

<template>

    <div class="modal fade" :id="modal_id" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="false">
        <div :class="['modal-dialog sm:w-full ', Class]" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ title }}</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <slot />
                </div>
                <div class="modal-footer">
                    <!-- <button class="btn btn-primary" type="button" data-dismiss="modal">Save</button> -->
                </div>
            </div>
        </div>
    </div>

</template>
