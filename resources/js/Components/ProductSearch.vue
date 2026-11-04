<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    placeholder: {
        type: String,
        default: 'Type product name or scan barcode...'
    },
    autofocus: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['select']);

const searchQuery = ref('');
const selectedSearchIndex = ref(-1);

// Search products by name or barcode
const filteredProducts = computed(() => {
    if (!searchQuery.value) return [];
    
    const query = searchQuery.value.toLowerCase().trim();
    if (query === '') return [];

    const results = [];
    
    props.products.forEach(product => {
        const pName = (product.name || '').toLowerCase();
        const pBarcode = (product.barcode || '').toLowerCase();
        const matchesProduct = pName.includes(query) || pBarcode.includes(query);
        
        let matchesVars = [];
        if (product.variations) {
            // Helper to map variation fields to display fields
            const mapVariation = (v) => ({
                ...v,
                _parent: product,
                // Map DB fields to display fields Arbaz changed at 1/15/2026
                name: v.var_name,
                // name: [product.name, product.variation_name, v.var_name].filter(Boolean).join(' '),
                barcode: v.var_sku || '',
                retail: v.var_retail_price || ''
            });

            if (matchesProduct) {
                // If main product matches, include all variations
                matchesVars = product.variations.map(mapVariation);
            } else {
                // Otherwise only matching variations
                matchesVars = product.variations.filter(v => {
                    const vName = (v.var_name || '').toLowerCase();
                    const vBarcode = (v.var_sku || '').toLowerCase();
                    return vName.includes(query) || vBarcode.includes(query);
                }).map(mapVariation);
            }
        }
        
        if (matchesProduct || matchesVars.length > 0) {
            // Add Parent
            results.push({
                ...product,
                _displayKey: `p-${product.id}`,
                _isVariation: false,
                _original: product
            });
            
            // Add Variations
            matchesVars.forEach(v => {
                results.push({
                    ...v,
                    _displayKey: `v-${v.id}`,
                    _isVariation: true
                });
            });
        }
    });
    
    return results.slice(0, 50);
});

// Handle keyboard navigation
const handleKeydown = (event) => {
    if (!filteredProducts.value.length) return;
    
    switch(event.key) {
        case 'ArrowDown':
            event.preventDefault();
            selectedSearchIndex.value = Math.min(
                selectedSearchIndex.value + 1, 
                filteredProducts.value.length - 1
            );
            break;
            
        case 'ArrowUp':
            event.preventDefault();
            selectedSearchIndex.value = Math.max(selectedSearchIndex.value - 1, 0);
            break;
            
        case 'Tab':
            event.preventDefault();
            selectedSearchIndex.value = (selectedSearchIndex.value + 1) % filteredProducts.value.length;
            break;
            
        case 'Enter':
            event.preventDefault();
            if (selectedSearchIndex.value >= 0 && selectedSearchIndex.value < filteredProducts.value.length) {
                selectProduct(filteredProducts.value[selectedSearchIndex.value]);
            }
            break;
            
        case 'Escape':
            clearSearch();
            break;
    }
};

// Search timeout for debouncing
let searchTimeout = null;

// Reset selected index when search query changes
const onSearchInput = () => {
    selectedSearchIndex.value = -1;
    
    // Clear previous timeout
    if (searchTimeout) clearTimeout(searchTimeout);

    // Debounce the exact match check
    searchTimeout = setTimeout(() => {
        // Check for exact barcode match
        const query = searchQuery.value.trim();
        if (query) {
            // Find exact barcode match from filtered results
            // Use filteredProducts because it already processes variations and regular products
            const exactMatch = filteredProducts.value.find(item => 
                item.barcode && item.barcode.toString().toLowerCase() === query.toLowerCase()
            );
            
            if (exactMatch) {
                selectProduct(exactMatch);
            }
        }
    }, 400); // 400ms delay to allow for typing/scanning
};

const selectProduct = (item) => {
    if (item._isVariation) {
        emit('select', { 
            ...item, 
            _isSelectedVariation: true,
            _parent: item._parent 
        });
    } else {
        emit('select', item._original || item);
    }
    clearSearch();
};

const clearSearch = () => {
    searchQuery.value = '';
    selectedSearchIndex.value = -1;
};

const searchInput = ref(null);

onMounted(() => {
    if (props.autofocus) {
        nextTick(() => {
            searchInput.value?.focus();
        });
    }
});

// Auto-scroll to selected search result
watch(selectedSearchIndex, (newIndex) => {
    if (newIndex >= 0) {
        nextTick(() => {
            const items = document.querySelectorAll('.search-result-item');
            if (items[newIndex]) {
                items[newIndex].scrollIntoView({ block: 'nearest' });
            }
        });
    }
});
</script>

<template>
    <div class="position-relative">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input 
                ref="searchInput"
                type="text" 
                class="form-control form-control-lg search-input" 
                v-model="searchQuery"
                @keydown="handleKeydown"
                @input="onSearchInput"
                :placeholder="placeholder"
                :autofocus="autofocus"
                autocomplete="off"
            >
        </div>
        
        <!-- Modern Search Results -->
        <div v-if="filteredProducts.length > 0" class="search-results">
            <div 
                v-for="(product, index) in filteredProducts" 
                :key="product._displayKey"
                @click="selectProduct(product)"
                class="search-result-item"
                :class="{ 
                    'active': index === selectedSearchIndex,
                    'variation': product._isVariation
                }"
            >
                <div class="d-flex align-items-center">
                    <div class="result-icon" :class="{'bg-secondary': product._isVariation}">
                        <i class="fas" :class="product._isVariation ? 'fa-tag' : 'fa-box'"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="result-name">{{ product.name }}</div>
                        <div class="result-barcode">
                            <i class="fas fa-barcode me-1"></i>{{ product.barcode }}
                        </div>
                    </div>
                    <div class="result-price">
                        Rs. {{ product.price || product.retail }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.search-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    z-index: 1;
}

.search-input {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s ease;
    padding-left: 48px !important;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-results {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    max-height: 400px;
    overflow-y: auto;
    z-index: 9999;
}

.search-result-item {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-result-item:hover,
.search-result-item.active {
    background: #f9fafb;
}

.search-result-item:last-child {
    border-bottom: none;
}

.result-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 12px;
}

.result-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.result-barcode {
    font-size: 0.875rem;
    color: #6b7280;
}

.result-price {
    font-weight: 700;
    color: #667eea;
    font-size: 1.125rem;
}

.search-result-item.variation {
    padding-left: 32px;
    background-color: #fafafa;
}

.search-result-item.variation .result-icon {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
    background: #9ca3af !important; /* Override gradient */
    margin-right: 12px;
}
</style>
