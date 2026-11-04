<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import Code from '@editorjs/code';
import InlineCode from '@editorjs/inline-code';
import Delimiter from '@editorjs/delimiter';
import Table from '@editorjs/table';
import Marker from '@editorjs/marker';
import Underline from '@editorjs/underline';
import LinkTool from '@editorjs/link';
import Paragraph from '@editorjs/paragraph';
import ImageTool from '@editorjs/image';

const props = defineProps({
    modelValue: {
        type: [String, Object],
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Type here...'
    }
});

const emit = defineEmits(['update:modelValue']);

// Generate unique ID
const editorId = `editorjs-${Math.random().toString(36).substr(2, 9)}`;
const editorInstance = ref(null);
const isReady = ref(false);

const initEditor = () => {
    if (editorInstance.value) return;

    let dataToLoad = { blocks: [] };

    // Robust data parsing
    if (props.modelValue) {
        try {
            let rawData = props.modelValue;
            
            // Handle string input
             if (typeof rawData === 'string') {
                if (rawData.trim().startsWith('{')) {
                    try {
                        rawData = JSON.parse(rawData);
                    } catch {
                        // If JSON parse fails, treat as plain text paragraph
                        rawData = { blocks: [{ type: 'paragraph', data: { text: rawData } }] };
                    }
                } else if (rawData.trim().length > 0) {
                    // Plain text
                    rawData = { blocks: [{ type: 'paragraph', data: { text: rawData } }] };
                }
            }

            // Deep clone and validate
            const cleanData = JSON.parse(JSON.stringify(rawData));
            if (cleanData && Array.isArray(cleanData.blocks)) {
                // Filter out invalid blocks
                cleanData.blocks = cleanData.blocks.filter(b => b && b.type && b.data);
                dataToLoad = cleanData;
            }
        } catch (e) {
            console.warn('EditorJS: Data parsing failed, starting empty.');
        }
    }

    editorInstance.value = new EditorJS({
        holder: editorId,
        placeholder: props.placeholder,
        data: dataToLoad,
        // Expanded Toolset without Image
        tools: {
            header: {
                class: Header,
                config: {
                    placeholder: 'Enter a heading',
                    levels: [1, 2, 3, 4, 5, 6],
                    defaultLevel: 1
                }
            },
            paragraph: {
                class: Paragraph,
                inlineToolbar: true,
            },
            list: {
                class: List,
                inlineToolbar: true,
                config: {
                    defaultStyle: 'unordered'
                }
            },
            quote: {
                class: Quote,
                inlineToolbar: true,
                config: {
                    quotePlaceholder: 'Enter a quote',
                    captionPlaceholder: 'Quote\'s author',
                },
            },
            delimiter: Delimiter,
            table: {
                class: Table,
                inlineToolbar: true,
            },
            code: Code,
            inlineCode: {
                class: InlineCode,
            },
            marker: {
                class: Marker,
            },
            underline: {
                class: Underline,
            },
            linkTool: {
                class: LinkTool,
                config: {
                    endpoint: '/editorjs/fetchUrl', // Backend endpoint for link previews if needed
                }
            },
            image: {
                class: ImageTool,
                config: {
                    uploader: {
                        uploadByFile(file) {
                            return new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.readAsDataURL(file);
                                reader.onload = (e) => {
                                    resolve({
                                        success: 1,
                                        file: {
                                            url: e.target.result,
                                        }
                                    });
                                };
                                reader.onerror = (e) => reject(e);
                            });
                        },
                        uploadByUrl(url) {
                            return Promise.resolve({
                                success: 1,
                                file: {
                                    url: url,
                                }
                            });
                        }
                    }
                }
            }
        },
        onChange: async () => {
            try {
                const savedData = await editorInstance.value.save();
                emit('update:modelValue', JSON.stringify(savedData));
            } catch (e) {
                console.error('EditorJS save failed', e);
            }
        },
        onReady: () => {
            isReady.value = true;
        }
    });
};

onMounted(() => {
    initEditor();
});

onBeforeUnmount(() => {
    if (editorInstance.value && typeof editorInstance.value.destroy === 'function') {
        editorInstance.value.destroy();
        editorInstance.value = null;
    }
});

// Watch for external changes (avoiding loops)
watch(() => props.modelValue, async (newVal, oldVal) => {
    if (!editorInstance.value || !isReady.value) return;
    
    // Only re-init if the content is drastically different/external update
    // Simple equality check might be enough if parent doesn't mutate heavily
    if (newVal === oldVal) return;

    // For now, we won't auto-reload on prop change to avoid removing user focus/cursor
    // unless strictly necessary. Uncomment below if bidirectional sync is needed from outside.
    /*
    const currentData = await editorInstance.value.save();
    if (JSON.stringify(currentData) !== (typeof newVal === 'string' ? newVal : JSON.stringify(newVal))) {
       // logic to re-render
    }
    */
});
</script>

<template>
    <div class="editor-wrapper">
        <div :id="editorId" class="prose max-w-none"></div>
    </div>
</template>

<style scoped>
.editor-wrapper {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: white;
    min-height: 150px;
}
</style>
