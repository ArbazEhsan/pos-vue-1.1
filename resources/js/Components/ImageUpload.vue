<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, File, null],
    default: null
  },
  placeholderLabel: {
    type: String,
    default: 'Add Image'
  },
  isCover: {
    type: Boolean,
    default: false
  },
  dateLabel: {
      type: String,
      default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const imagePreview = ref(null);
const uploading = ref(false);
const error = ref(null);

watch(() => props.modelValue, (newVal) => {
  if (newVal instanceof File) {
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(newVal);
  } else if (typeof newVal === 'string') {
    imagePreview.value = newVal;
  } else {
    imagePreview.value = null;
  }
}, { immediate: true });

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  if (!file.type.match('image.*')) {
    error.value = 'Please select an image file';
    return;
  }

  error.value = null;
  emit('update:modelValue', file);

  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  imagePreview.value = null;
  emit('update:modelValue', null);
  fileInput.value.value = '';
};
</script>

<template>
  <div class="relative group">
    <div
      @click="triggerFileInput"
      :class="[
        'relative w-[120px] h-[120px] bg-gray-50 border-2 border-dashed rounded-lg cursor-pointer transition-all duration-200 flex flex-col items-center justify-center overflow-hidden',
        imagePreview ? 'border-gray-400' : 'border-gray-300 hover:border-gray-400 hover:bg-gray-100'
      ]"
    >
      <template v-if="imagePreview">
        <img :src="imagePreview" alt="Preview" class="w-full h-full object-contain p-1.5" />
        <!-- Overlay on hover -->
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200 flex items-center justify-center">
            <button
            class="text-white opacity-0 group-hover:opacity-100 bg-red-700 rounded-full p-1.5 hover:bg-red-800 transition-all duration-200 transform scale-90 group-hover:scale-100"
            @click.stop="removeImage"
            title="Remove Image"
            >
            <i class="fas fa-trash-alt text-xs"></i>
            </button>
        </div>
        
        <!-- Cover Badge -->
        <div v-if="dateLabel || isCover" class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-60 text-white text-[10px] py-1 text-center">
            {{ isCover ? 'Main Image' : dateLabel }}
        </div>
      </template>
      
      <template v-else>
        <div class="flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-camera text-2xl mb-2"></i>
            <span class="text-[10px] font-medium text-gray-500">{{ placeholderLabel }}</span>
        </div>
      </template>

      <input
        type="file"
        ref="fileInput"
        @change="handleFileChange"
        accept="image/*"
        class="hidden"
      />
    </div>
    
    <p v-if="error" class="text-xs text-red-500 mt-1 text-center w-[120px]">{{ error }}</p>
  </div>
</template>
