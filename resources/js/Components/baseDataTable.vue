<script setup>
import { ref, computed, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';

// Import all DataTables extensions
import 'datatables.net-buttons-dt';
import 'datatables.net-responsive-dt';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import 'datatables.net-buttons/js/buttons.colVis.mjs';

// Import additional DataTables features (as side-effects)
import 'datatables.net-fixedcolumns-dt';
import 'datatables.net-fixedheader-dt';
import 'datatables.net-keytable-dt';
import 'datatables.net-rowgroup-dt';
import 'datatables.net-scroller-dt';
import 'datatables.net-searchbuilder-dt';
import 'datatables.net-searchpanes-dt';
import 'datatables.net-datetime';

// Import required libraries
import jszip from 'jszip';
import pdfmake from 'pdfmake';
import vfs_fonts from 'pdfmake/build/vfs_fonts';
import Dropdown from '@/Components/Dropdown.vue';
import { Link } from '@inertiajs/vue3';



// Register core and extensions
DataTablesCore.Buttons.jszip(jszip);
DataTablesCore.Buttons.pdfMake(pdfmake);

// Assign vfs to pdfMake
if (vfs_fonts && vfs_fonts.pdfMake && vfs_fonts.pdfMake.vfs) {
  pdfmake.vfs = vfs_fonts.pdfMake.vfs;
} else if (vfs_fonts && vfs_fonts.vfs) {
  pdfmake.vfs = vfs_fonts.vfs;
}

// Register DataTablesCore (extensions auto-register when imported)
DataTable.use(DataTablesCore);

const props = defineProps({
  ajaxUrl: { type: String, required: true },
  permission: { type: String, required: false },
  userType: { type: String, required: false },
  moreActions: { type: Array, required: false, default: [] },
  viewModal: { type: Boolean, required: false, default: false },
  columns: { type: Array, required: true },
  theadClass: { type: String },
  options: { type: Object, default: () => ({}) },
  
  // Advanced Features Props
  enableFixedHeader: { type: Boolean, default: false },
  enableFixedColumns: { type: Boolean, default: false },
  enableKeyTable: { type: Boolean, default: false },
  enableRowGroup: { type: Boolean, default: false },
  enableScroller: { type: Boolean, default: false },
  enableSearchBuilder: { type: Boolean, default: false },
  enableSearchPanes: { type: Boolean, default: false },
  
  // Feature-specific configurations
  fixedColumnsLeft: { type: Number, default: 1 },
  fixedColumnsRight: { type: Number, default: 0 },
  rowGroupColumn: { type: Number, default: 0 },
  scrollerHeight: { type: String, default: '400px' },
});

const emit = defineEmits(['view', 'status']);

const dt = ref(null);
const pageLength = ref(10);
const searchQuery = ref('');

// Watchers controls
watch(pageLength, (val) => {
    if (dt.value) dt.value.dt.page.len(val).draw();
});

const handleSearch = () => {
    if (dt.value) dt.value.dt.search(searchQuery.value).draw();
};

// Build default options with conditional features
const buildDefaultOptions = () => {
  const baseOptions = {
    responsive: true,
    serverSide: false,
    destroy: true,
    ajax: {
      url: props.ajaxUrl,
      dataSrc: '' 
    },
    dom: "Brt<'flex justify-between items-center mt-4'ip>",
    buttons: [
        { 
          extend: 'copy', 
          className: 'dt-hidden-button buttons-copy',
          exportOptions: {
            columns: function (idx, data, node) {
              var colTitle = node.textContent.trim();
              return colTitle !== 'Action' && colTitle !== 'Actions';
            }
          }
        },
        { 
          extend: 'csv', 
          className: 'dt-hidden-button buttons-csv',
          exportOptions: {
            columns: function (idx, data, node) {
              var colTitle = node.textContent.trim();
              return colTitle !== 'Action' && colTitle !== 'Actions';
            }
          }
        },
        { 
          extend: 'excel', 
          className: 'dt-hidden-button buttons-excel',
          exportOptions: {
            columns: function (idx, data, node) {
              var colTitle = node.textContent.trim();
              return colTitle !== 'Action' && colTitle !== 'Actions';
            }
          }
        },
        { 
          extend: 'pdf', 
          className: 'dt-hidden-button buttons-pdf',
          exportOptions: {
            columns: function (idx, data, node) {
              var colTitle = node.textContent.trim();
              return colTitle !== 'Action' && colTitle !== 'Actions';
            }
          },
          customize: function (doc) {
            doc.styles.tableHeader = {
              fillColor: '#4E73DF',
              color: 'white',
              alignment: 'center',
              bold: true,
            };
            
            if (dt.value && dt.value.dt) {
              var colCount = dt.value.dt.columns().header().length;
              doc.content[1].table.widths = Array(colCount).fill('*');
            }
            
            doc.pageOrientation = 'landscape';
            doc.pageMargins = [40, 60, 40, 60];
            
            doc.content.splice(0, 0, {
              text: 'Product Report',
              style: 'header',
              alignment: 'center',
              margin: [0, 0, 0, 10]
            });
            
            doc.styles.header = {
              fontSize: 18,
              bold: true,
              color: '#4E73DF'
            };
            
            doc.styles.tableBodyEven = {
              fillColor: '#f9fafb'
            };
            doc.styles.tableBodyOdd = {
              fillColor: '#ffffff'
            };
          }
        },
        { 
          extend: 'print', 
          className: 'dt-hidden-button buttons-print',
          exportOptions: {
            columns: function (idx, data, node) {
              var colTitle = node.textContent.trim();
              return colTitle !== 'Action' && colTitle !== 'Actions';
            }
          }
        },
    ],
    language: {
      paginate: {
        next: '<i class="fas fa-chevron-right"></i>',
        previous: '<i class="fas fa-chevron-left"></i>' 
      }
    }
  };

  // Add FixedHeader
  if (props.enableFixedHeader) {
    baseOptions.fixedHeader = {
      header: true,
      headerOffset: 0
    };
  }

  // Add FixedColumns
  if (props.enableFixedColumns) {
    baseOptions.fixedColumns = {
      left: props.fixedColumnsLeft,
      right: props.fixedColumnsRight
    };
  }

  // Add KeyTable
  if (props.enableKeyTable) {
    baseOptions.keys = true;
  }

  // Add RowGroup
  if (props.enableRowGroup) {
    baseOptions.rowGroup = {
      dataSrc: props.rowGroupColumn
    };
  }

  // Add Scroller
  if (props.enableScroller) {
    baseOptions.scroller = true;
    baseOptions.scrollY = props.scrollerHeight;
    baseOptions.deferRender = true;
  }

  // Add SearchBuilder
  if (props.enableSearchBuilder) {
    baseOptions.dom = "QBrt<'flex justify-between items-center mt-4'ip>";
    baseOptions.searchBuilder = {
      columns: ':not(.no-search)'
    };
  }

  // Add SearchPanes
  if (props.enableSearchPanes) {
    baseOptions.dom = "PBrt<'flex justify-between items-center mt-4'ip>";
    baseOptions.searchPanes = {
      cascadePanes: true,
      viewTotal: true,
      columns: ':not(.no-search-pane)'
    };
  }

  // Merge with user options
  return { ...baseOptions, ...props.options };
};

const defaultOptions = buildDefaultOptions();

// Trigger Export Function
const triggerExport = (type) => {
    if (dt.value) {
        dt.value.dt.button(`.buttons-${type}`).trigger();
    }
};

// Column Visibility Toggle
const toggleColumn = (index) => {
    if (dt.value) {
        const column = dt.value.dt.column(index);
        column.visible(!column.visible());
    }
};

// Helper for column visibility state (optional, for UI feedback)
// Since DataTables state isn't reactive in Vue automatically, we rely on the checkbox toggling the state blindly or would need to sync state. 
// For simplicity, we assume checked = visible initially.
</script>

<template>
  <div class="datatable-wrapper relative">
    
    <!-- Custom Toolbar overlaid or placed above -->
    <!-- We place it absolute top-right or flex above. 
         Given the search bar is usually top-right, let's put this top-right but shift it? 
         Or separate row. -->
    <!-- Custom Toolbar -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-2 sm:space-y-0">
        
        <!-- Left Side: Page Length & Actions -->
        <div class="flex items-center space-x-3 w-full sm:w-auto">
            <!-- Page Length Select -->
            <div class="flex items-center space-x-2 bg-white border border-gray-300 rounded-md px-2 py-1">
                <span class="text-sm text-gray-500 font-medium ml-1">Show</span>
                <select 
                    v-model="pageLength" 
                    class="form-select border-0 py-1 pl-1 pr-6 text-sm text-gray-700 focus:ring-0 focus:outline-none cursor-pointer bg-transparent"
                style="    background-image: none;"
                >
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
                <span class="text-sm text-gray-500 font-medium mr-1">entries</span>
            </div>

            <!-- Export & Actions Dropdown -->
            <Dropdown align="left" width="48">
                <template #trigger>
                    <button class="flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors duration-150">
                        Export & Actions
                        <i class="fas fa-chevron-down ml-2 text-xs text-gray-400"></i>
                    </button>
                </template>
                <template #content>
                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 border-b">
                        Export
                    </div>
                    <button @click="triggerExport('copy')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-copy mr-2 text-gray-400"></i> Copy
                    </button>
                    <button @click="triggerExport('csv')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-file-csv mr-2 text-gray-400"></i> CSV
                    </button>
                    <button @click="triggerExport('excel')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-file-excel mr-2 text-green-600"></i> Excel
                    </button>
                    <button @click="triggerExport('pdf')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-file-pdf mr-2 text-red-600"></i> PDF
                    </button>
                    <button @click="triggerExport('print')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors border-b">
                        <i class="fas fa-print mr-2 text-gray-400"></i> Print
                    </button>

                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 border-b">
                        Columns
                    </div>
                    <div class="max-h-48 overflow-y-auto">
                        <div v-for="(col, index) in columns" :key="index" class="px-4 py-2 flex items-center hover:bg-gray-50 cursor-pointer" @click.stop>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer w-full select-none">
                                <input type="checkbox" checked @change="toggleColumn(index)" class="rounded border-gray-300 text-blue-600 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span>{{ col.title || col.data || 'Column ' + (index+1) }}</span>
                            </label>
                        </div>
                    </div>
                </template>
            </Dropdown>
        </div>

        <!-- Right Side: Search -->
        <div class="w-full sm:w-64 relative">
             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input 
                v-model="searchQuery" 
                @input="handleSearch"
                type="text" 
                placeholder="Search..." 
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm transition duration-150 ease-in-out"
            />
        </div>
    </div>

    <DataTable 
      ref="dt"
      :columns="columns" 
      :options="defaultOptions" 
      class="table w-full border"
    >
      <thead :class="['text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400', theadClass]">
        <tr>
          <th v-for="column in columns" :key="column.data">{{ column.title }}</th>
        </tr>
      </thead>

      <!-- This targets any column in your array with name: 'index' -->
      <template #column-index="props">
        <slot name="column-index" v-bind="props">
          <!-- Default behavior: row index + page start offset -->
          {{ props.rowIndex + 1 + (props.inst?.page?.info()?.start || 0) }}
        </slot>
      </template>

      <!-- Image Column Slot -->
      <template #column-product-image="props">
        <div class="flex justify-center product-icon">
          <img
            v-if="props.cellData"
            :src="`/storage/${props.cellData}`"
            alt="Product Image" 
            class="w-11 h-11 object-cover rounded shadow-sm border border-gray-200"
            @error="(e) => e.target.src = '/assets/img/placeholder2.png'"
          />
          <i v-else class="fas fa-cube"></i>
        </div>
      </template>

      <!-- Name Column Slot -->
      <template #column-product-name="props">
        <div class="flex flex-col">
          <span class="">{{ props.rowData.name }}</span>
          <span class="">{{ "(SKU:" + props.rowData.barcode + ")" }}</span>
        </div>
      </template>

      <!-- Status Column Slot -->
      <template #column-product-status="props">
        <span :class="{'text-green-500': props.rowData.status == 1, 'text-red-500': props.rowData.status == 0}">
          {{ props.rowData.status == 1 ? 'Active' : 'Inactive' }}
        </span>
      </template>

      <!-- Actions Column Slot -->
      <template #column-product-actions="props">
        <div class="flex justify-center">
            <Dropdown align="right" width="48" v-if="userType == 'Admin' || permission">
                <template #trigger>
                    <button class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none">
                        More
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                </template>
                <template #content>
                     <!-- Dynamic Actions -->
                     <template v-if="moreActions && moreActions.length > 0">
                        <div v-for="(action, idx) in moreActions" :key="idx">
                            <!-- Button for actions without href (e.g. View) -->
                            <button 
                                v-if="!action.href"
                                @click="$emit(action.action || 'view', props.rowData.id)"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <i :class="action.icon"></i> {{ action.label }}
                            </button>
                            
                            <!-- Link for routed actions -->
                            <Link 
                                v-else
                                :href="route(action.href, action.params ? action.params(props.rowData) : props.rowData.id)" 
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <i :class="action.icon"></i> {{ action.label }}
                            </Link>
                        </div>
                     </template>

                     <!-- Legacy Logic Fallback -->
                     <template v-else>
                         <button @click="$emit('view', props.rowData.id)" v-if="viewModal" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-eye mr-2"></i> View
                         </button>
                         <Link :href="route(editUrl, props.rowData.id)" v-if="editUrl" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-edit mr-2"></i> Edit
                         </Link>
                         <Link :href="route('printLabelForm', props.rowData.id)" v-if="statusUrl" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-barcode mr-2"></i> Labels
                         </Link>
                         <Link :href="route(statusUrl, [props.rowData.status, props.rowData.id])" v-if="statusUrl" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-exchange-alt mr-2"></i> Change Status
                         </Link>
                     </template>
                </template>
            </Dropdown>
            <span v-else>
                <button class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none">
                    Restricted
                </button>
            </span>
        </div>
      </template>
      
    </DataTable>
  </div>
</template>

<style>
@import 'datatables.net-dt';

/* Hide default controls: buttons, length, and filter input */
.dt-buttons, .dataTables_filter, .dataTables_length {
    display: none !important;
}

/* Info Text Styling */
.dataTables_info {
    padding: 0 !important;
    font-size: 0.875rem;
    color: #6b7280; /* gray-500 */
}

/* Pagination Styling */
.dataTables_paginate {
    padding: 0 !important;
    display: flex;
    justify-content: flex-end;
    gap: 0.375rem;
}

.dataTables_paginate .paginate_button {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 2.25rem;
    height: 2.25rem;
    padding: 0.375rem 0.75rem !important;
    margin: 0 !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    color: #374151 !important; /* gray-700 */
    background: #ffffff !important;
    border: 1px solid #d1d5db !important; /* gray-300 */
    border-radius: 0.375rem !important; /* rounded-md */
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    text-decoration: none !important;
}

.dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
    background: #f9fafb !important; /* gray-50 */
    color: #111827 !important; /* gray-900 */
    border-color: #9ca3af !important; /* gray-400 */
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
}

.dataTables_paginate .paginate_button.current,
.dataTables_paginate .paginate_button.current:hover {
    background: #2563eb !important; /* blue-600 */
    color: #ffffff !important;
    border-color: #2563eb !important;
    box-shadow: 0 1px 3px 0 rgba(37, 99, 235, 0.3) !important;
    font-weight: 600 !important;
}

.dataTables_paginate .paginate_button.disabled,
.dataTables_paginate .paginate_button.disabled:hover {
    opacity: 0.4;
    cursor: not-allowed;
    background: #ffffff !important;
    color: #9ca3af !important; /* gray-400 */
    border-color: #e5e7eb !important; /* gray-200 */
    box-shadow: none !important;
}

/* Previous/Next buttons */
.dataTables_paginate .paginate_button.previous,
.dataTables_paginate .paginate_button.next {
    padding: 0.375rem 0.875rem !important;
}

/* Fix stacking contexts — force all table layers to overflow visible */
:deep(.dataTables_wrapper),
:deep(.dataTables_scroll),
:deep(.dataTables_scrollBody),
:deep(.dataTable),
:deep(.dataTable tbody),
:deep(.dataTable tbody tr),
:deep(.dataTable tbody td) {
  overflow: visible !important;
}

/* Ensure dropdown sits on top of everything */
:deep(.dataTable tbody td .relative) {
  position: static !important;
}

.group-focus-within div {
  z-index: 9999;
}

.product-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
}
</style>
