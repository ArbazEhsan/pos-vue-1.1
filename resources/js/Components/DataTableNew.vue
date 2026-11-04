<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

const props = defineProps({
    apiUrl: { type: String, required: true },
    title: { type: String, default: 'Employees' },
    columns: {
        type: Array,
        default: () => [
            { key: 'id', label: 'ID' },
            { key: 'name', label: 'Name' },
            { key: 'age', label: 'Age', type: 'number' },
            { key: 'joinDate', label: 'Join date', type: 'date' },
            { key: 'role', label: 'Department' },
            { key: 'fullTime', label: 'Full-time', type: 'boolean' },
        ],
    },
    idKey: { type: String, default: 'id' },
    dataKey: { type: String, default: '' }, // path to the array in the response, e.g. "data" or "result.items"
    totalKey: { type: String, default: 'total' }, // server-side mode only
    headers: { type: Object, default: () => ({}) }, // e.g. { Authorization: 'Bearer ...' }
    serverSide: { type: Boolean, default: false }, // true: API does paging/sort/search/filter
    pageSizeOptions: { type: Array, default: () => [5, 10, 25, 50, 100] },
    initialPageSize: { type: Number, default: 10 },
    deletable: { type: Boolean, default: true },
    deleteFn: { type: Function, default: null }, // async (row) => {} — overrides the default DELETE {apiUrl}/{id}
    transform: { type: Function, default: null }, // (row) => row, to reshape each API item
})
const emit = defineEmits(['create', 'refresh', 'loaded', 'deleted', 'error'])

const OPS = [
    { v: 'contains', t: 'contains' },
    { v: 'equals', t: 'equals' },
    { v: 'starts', t: 'starts with' },
    { v: 'ends', t: 'ends with' },
]

// ---------- state ----------
const rows = ref([])
const total = ref(0)
const loading = ref(false)
const error = ref('')
const vis = reactive(Object.fromEntries(props.columns.map((c) => [c.key, true])))
const q = reactive({
    search: '', field: props.columns[0]?.key, op: 'contains', value: '',
    sortKey: null, dir: 1, page: 0, pageSize: props.initialPageSize,
})
const searchOpen = ref(false)
const open = ref(null) // 'cols' | 'filter' | 'export' | null
const colMenu = reactive({ show: false, key: '', top: 0, left: 0 })
const colSearch = ref('')
const filterW = ref(300)
const rootEl = ref(null), tbEl = ref(null), tableEl = ref(null)
const searchInput = ref(null), filterInput = ref(null), colSearchInput = ref(null)

const visCols = computed(() => props.columns.filter((c) => vis[c.key]))
const filterActive = computed(() => q.value !== '')

// ---------- helpers ----------
const pick = (o, path) => (path ? path.split('.').reduce((a, k) => a?.[k], o) : o)
const pad = (n) => String(n).padStart(2, '0')
function fmtDate(v) {
    const d = new Date(v)
    return isNaN(d) ? String(v ?? '') : `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`
}
function display(row, col) {
    const v = row[col.key]
    if (col.type === 'date') return fmtDate(v)
    if (col.type === 'boolean') return v ? 'Yes' : 'No'
    return v == null ? '' : String(v)
}
function matches(text, op, needle) {
    const t = text.toLowerCase(), n = needle.toLowerCase()
    return op === 'equals' ? t === n : op === 'starts' ? t.startsWith(n) : op === 'ends' ? t.endsWith(n) : t.includes(n)
}

// ---------- data loading ----------
let ctrl = null
let seq = 0
async function load() {
    ctrl?.abort()
    ctrl = new AbortController()
    const mine = ++seq
    loading.value = true
    error.value = ''
    try {
        const url = new URL(props.apiUrl, window.location.origin)
        if (props.serverSide) {
            const p = url.searchParams
            p.set('page', q.page + 1)
            p.set('pageSize', q.pageSize)
            if (q.sortKey) { p.set('sortBy', q.sortKey); p.set('sortDir', q.dir > 0 ? 'asc' : 'desc') }
            if (q.search) p.set('search', q.search)
            if (q.value) { p.set('filterField', q.field); p.set('filterOp', q.op); p.set('filterValue', q.value) }
        }
        const res = await fetch(url, { headers: { Accept: 'application/json', ...props.headers }, signal: ctrl.signal })
        if (!res.ok) throw new Error(`Could not load data (HTTP ${res.status})`)
        const json = await res.json()
        let list = pick(json, props.dataKey)
        list = Array.isArray(list) ? list : []
        if (props.transform) list = list.map(props.transform)
        rows.value = list
        total.value = props.serverSide ? Number(pick(json, props.totalKey) ?? list.length) : list.length
        emit('loaded', list)
    } catch (e) {
        if (e.name === 'AbortError') return
        error.value = e.message || 'Could not load data'
        emit('error', e)
    } finally {
        if (mine === seq) loading.value = false
    }
}
function refresh() { emit('refresh'); load() }

// ---------- client-side view ----------
const filtered = computed(() => {
    if (props.serverSide) return rows.value
    const s = q.search.trim().toLowerCase()
    const fcol = props.columns.find((c) => c.key === q.field)
    let out = rows.value.filter((r) => {
        if (s && !props.columns.some((c) => display(r, c).toLowerCase().includes(s))) return false
        if (q.value && fcol && !matches(display(r, fcol), q.op, q.value)) return false
        return true
    })
    if (q.sortKey) {
        const col = props.columns.find((c) => c.key === q.sortKey)
        const val = (r) => (col?.type === 'date' ? new Date(r[q.sortKey]).getTime() : r[q.sortKey])
        out = [...out].sort((a, b) => {
            const x = val(a), y = val(b)
            return (x < y ? -1 : x > y ? 1 : 0) * q.dir
        })
    }
    return out
})
const count = computed(() => (props.serverSide ? total.value : filtered.value.length))
const pageCount = computed(() => Math.max(1, Math.ceil(count.value / q.pageSize)))
const pageRows = computed(() =>
    props.serverSide ? rows.value : filtered.value.slice(q.page * q.pageSize, q.page * q.pageSize + q.pageSize),
)
const rangeText = computed(() => {
    if (!count.value) return '0 of 0'
    const from = q.page * q.pageSize + 1
    return `${from}–${Math.min(from + q.pageSize - 1, count.value)} of ${count.value}`
})

watch(() => [q.search, q.field, q.op, q.value, q.pageSize], () => { q.page = 0 })
watch(pageCount, (n) => { if (q.page >= n) q.page = n - 1 })
let timer = null
watch(
    () => [q.search, q.field, q.op, q.value, q.sortKey, q.dir, q.page, q.pageSize],
    () => {
        if (!props.serverSide) return
        clearTimeout(timer)
        timer = setTimeout(load, 300)
    },
)
watch(() => props.apiUrl, load)

// ---------- actions ----------
function sortBy(key, dir) {
    if (dir) { q.sortKey = key; q.dir = dir; return }
    if (q.sortKey === key) q.dir *= -1
    else { q.sortKey = key; q.dir = 1 }
}
function closeAll() { open.value = null; colMenu.show = false }
function toggle(name) {
    const was = open.value === name
    closeAll()
    if (was) return
    open.value = name
    if (name === 'filter') openFilterPanel()
    if (name === 'cols') { colSearch.value = ''; nextTick(() => setTimeout(() => colSearchInput.value?.focus(), 30)) }
}
function openFilterPanel() {
    const tb = tbEl.value, table = tableEl.value
    if (tb && table) filterW.value = Math.min(Math.max(table.offsetWidth / 2, 200), tb.clientWidth - 24)
    nextTick(() => setTimeout(() => filterInput.value?.focus(), 30))
}
function openFilter(key) {
    closeAll()
    q.field = key
    open.value = 'filter'
    openFilterPanel()
}
function openColMenu(e, key) {
    const was = colMenu.show && colMenu.key === key
    closeAll()
    if (was) return
    const r = e.currentTarget.getBoundingClientRect()
    colMenu.key = key
    colMenu.top = r.bottom + 4
    colMenu.left = Math.max(8, Math.min(r.left - 110, window.innerWidth - 170))
    colMenu.show = true
}
function menuAction(a) {
    const k = colMenu.key
    colMenu.show = false
    if (a === 'asc') sortBy(k, 1)
    else if (a === 'desc') sortBy(k, -1)
    else if (a === 'hide') vis[k] = false
    else if (a === 'filter') openFilter(k)
    else if (a === 'manage') toggle('cols')
}
function clearFilter() { q.value = ''; closeAll() }
function openSearch() { searchOpen.value = true; nextTick(() => searchInput.value?.focus()) }
function closeSearch() { q.search = ''; searchOpen.value = false }

async function remove(row) {
    const id = row[props.idKey]
    const snapshot = rows.value
    rows.value = rows.value.filter((r) => r[props.idKey] !== id)
    total.value = Math.max(0, total.value - 1)
    try {
        if (props.deleteFn) await props.deleteFn(row)
        else {
            const base = props.apiUrl.replace(/\/$/, '')
            const res = await fetch(`${base}/${encodeURIComponent(id)}`, { method: 'DELETE', headers: props.headers })
            if (!res.ok) throw new Error(`Could not delete (HTTP ${res.status})`)
        }
        emit('deleted', row)
        if (props.serverSide) load()
    } catch (e) {
        rows.value = snapshot
        total.value += 1
        error.value = e.message || 'Could not delete'
        emit('error', e)
    }
}

function exportCsv() {
    closeAll()
    const cols = visCols.value
    const esc = (s) => `"${String(s).replace(/"/g, '""')}"`
    const data = props.serverSide ? rows.value : filtered.value
    const csv = [cols.map((c) => esc(c.label)).join(',')]
        .concat(data.map((r) => cols.map((c) => esc(display(r, c))).join(',')))
        .join('\n')
    const a = document.createElement('a')
    a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }))
    a.download = `${props.title.toLowerCase().replace(/\s+/g, '-')}.csv`
    a.click()
    URL.revokeObjectURL(a.href)
}
function printTable() {
    closeAll()
    const cols = visCols.value
    const data = props.serverSide ? rows.value : filtered.value
    const headRow = cols.map(c => `<th>${c.label}</th>`).join('')
    const bodyRows = data.map(r =>
        '<tr>' + cols.map(c => `<td>${display(r, c)}</td>`).join('') + '</tr>'
    ).join('')
    const html = `<!DOCTYPE html><html><head><title>${props.title}</title>
    <style>
      body { font-family: Inter, Roboto, Arial, sans-serif; padding: 20px; margin: 0; }
      h1 { font-size: 22px; margin-bottom: 12px; }
      table { width: 100%; border-collapse: collapse; }
      th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; font-size: 13px; }
      th { background: #f0f0f0; font-weight: 700; }
    </style></head><body>
    <h1>${props.title}</h1>
    <table><thead><tr>${headRow}</tr></thead><tbody>${bodyRows}</tbody></table>
    </body></html>`
    const iframe = document.createElement('iframe')
    iframe.style.cssText = 'position:fixed;width:0;height:0;border:none;left:-9999px'
    document.body.appendChild(iframe)
    iframe.contentDocument.write(html)
    iframe.contentDocument.close()
    iframe.onload = () => {
        iframe.contentWindow.focus()
        iframe.contentWindow.print()
        setTimeout(() => document.body.removeChild(iframe), 500)
    }
}

const shownCols = computed(() => props.columns.filter((c) => c.label.toLowerCase().includes(colSearch.value.toLowerCase())))

onMounted(() => { load(); document.addEventListener('click', closeAll) })
onBeforeUnmount(() => { document.removeEventListener('click', closeAll); clearTimeout(timer); ctrl?.abort() })
defineExpose({ refresh: load })
</script>

<template>
    <div class="emp" ref="rootEl">
        <!-- <div class="crumb">{{ title }}</div> -->
        <div class="titlerow">
            <h1>{{ title }}</h1>
            <button class="sq" aria-label="Refresh" :disabled="loading" @click="refresh">
                <svg viewBox="0 0 24 24">
                    <path d="M20 12a8 8 0 1 1-2.3-5.7M20 4v5h-5" />
                </svg>
            </button>
            <button class="create" @click="emit('create')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" />
                </svg>Create
            </button>
        </div>

        <div v-if="error" class="err" role="alert">
            <span>{{ error }}</span><button @click="load">Try again</button>
        </div>

        <div class="grid">
            <div class="bar" v-if="loading" aria-hidden="true"></div>
            <div class="tb" :class="{ s: searchOpen }" ref="tbEl">
                <div v-if="searchOpen" class="sbox" @click.stop>
                    <input ref="searchInput" v-model="q.search" type="text" placeholder="Search..."
                        aria-label="Search" />
                    <button class="c" aria-label="Clear search" @click="closeSearch">✕</button>
                </div>
                <button class="sq" aria-label="Columns" @click.stop="toggle('cols')">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 5h4v14H4zM10 5h4v14h-4zM16 5h4v14h-4z" fill="currentColor" />
                    </svg>
                </button>
                <button class="sq" aria-label="Filters" @click.stop="toggle('filter')">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 7h16M7 12h10M10 17h4" />
                    </svg>
                    <span v-if="filterActive" class="badge fb">1</span>
                </button>
                <span class="sep"></span>
                <button class="sq" aria-label="Export" @click.stop="toggle('export')">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 4v11M7 11l5 5 5-5M5 20h14" />
                    </svg>
                </button>
                <span class="sep"></span>
                <button v-if="!searchOpen" class="sq" aria-label="Search" @click.stop="openSearch">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="6" />
                        <path d="M16 16l4 4" />
                    </svg>
                </button>

                <div v-if="open === 'cols'" class="pop" @click.stop>
                    <input ref="colSearchInput" v-model="colSearch" type="text" placeholder="Search" class="cs" />
                    <label v-for="c in shownCols" :key="c.key"><input v-model="vis[c.key]" type="checkbox" /> {{ c.label
                        }}</label>
                </div>
                <div v-if="open === 'filter'" class="pop wide" :style="{ width: filterW + 'px' }" @click.stop>
                    <div class="fp">
                        <button class="x" aria-label="Remove filter" @click="clearFilter">✕</button>
                        <label>Column
                            <select v-model="q.field">
                                <option v-for="c in columns" :key="c.key" :value="c.key">{{ c.label }}</option>
                            </select>
                        </label>
                        <label>Operator
                            <select v-model="q.op">
                                <option v-for="o in OPS" :key="o.v" :value="o.v">{{ o.t }}</option>
                            </select>
                        </label>
                        <label>Value
                            <input ref="filterInput" v-model="q.value" type="text" placeholder="Filter value" />
                        </label>
                    </div>
                </div>
                <div v-if="open === 'export'" class="pop menu" @click.stop>
                    <button @click="printTable">Print</button>
                    <button @click="exportCsv">Download as CSV</button>
                </div>
            </div>

            <div class="scroll">
                <table ref="tableEl">
                    <thead>
                        <tr>
                            <th v-for="c in visCols" :key="c.key" :class="{ num: c.type === 'number' }"
                                @click="sortBy(c.key)">
                                <div class="h">
                                    <span>
                                        {{ c.label }}
                                        <svg v-if="filterActive && q.field === c.key" class="fi" viewBox="0 0 24 24"
                                            width="16" height="16" fill="currentColor" aria-label="Filtered"
                                            @click.stop="openFilter(c.key)">
                                            <path d="M3 5h18l-7 8.5V20l-4-2v-4.5z" />
                                        </svg>
                                    </span>
                                    <button class="dots si" :class="{ on: q.sortKey === c.key }" aria-label="Sort">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                            stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                :d="q.sortKey === c.key && q.dir < 0 ? 'M12 4v16M6 14l6 6 6-6' : 'M12 20V4M6 10l6-6 6 6'" />
                                        </svg>
                                    </button>
                                    <button class="dots" aria-label="Column menu"
                                        @click.stop="openColMenu($event, c.key)">⋮</button>
                                </div>
                            </th>
                            <th v-if="deletable" class="act"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in pageRows" :key="r[idKey]">
                            <td v-for="c in visCols" :key="c.key" :class="{ num: c.type === 'number' }">
                                <slot :name="`cell-${c.key}`" :row="r" :value="r[c.key]">
                                    <span v-if="c.type === 'boolean'" class="ft" :class="r[c.key] ? 'y' : 'n'">{{
                                        r[c.key] ? '✓' : '✕' }}</span>
                                    <template v-else>{{ display(r, c) }}</template>
                                </slot>
                            </td>
                            <td v-if="deletable" class="act">
                                <button class="del" aria-label="Delete row" title="Delete"
                                    @click="remove(r)">🗑</button>
                            </td>
                        </tr>
                        <tr v-if="!pageRows.length">
                            <td :colspan="visCols.length + 1" class="empty">
                                <slot name="empty">{{ loading ? 'Loading…' : error ? 'No data' : 'No rows' }}</slot>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="foot">
                <span class="rpp">Rows per page:
                    <select v-model.number="q.pageSize" aria-label="Rows per page">
                        <option v-for="n in pageSizeOptions" :key="n" :value="n">{{ n }}</option>
                    </select>
                </span>
                <span class="pg">
                    <span>{{ rangeText }}</span>
                    <button class="sq" aria-label="Previous page" :disabled="q.page === 0" @click="q.page--">
                        <svg viewBox="0 0 24 24">
                            <path d="M15 6l-6 6 6 6" />
                        </svg>
                    </button>
                    <button class="sq" aria-label="Next page" :disabled="q.page >= pageCount - 1" @click="q.page++">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </button>
                </span>
            </div>
        </div>

        <div v-if="colMenu.show" class="cm" :style="{ top: colMenu.top + 'px', left: colMenu.left + 'px' }" @click.stop>
            <button @click="menuAction('asc')"><span class="ic"><i class="fa fa-solid fa-arrow-up"></i></span>Sort
                by
                ASC</button>
            <button @click="menuAction('desc')"><span class="ic"><i class="fa fa-solid fa-arrow-down"></i></span>Sort by
                DESC</button>
            <hr />
            <button @click="menuAction('filter')"><span class="ic"><i
                        class="fa fa-solid fa-filter"></i></span>Filter</button>
            <hr />
            <button @click="menuAction('hide')">
                <span class="ic">
                    <i class="fa fa-eye-slash"></i>
                </span>
                Hide column
            </button>
            <button @click="menuAction('manage')"><span class="ic"><i class="fa fa-solid fa-bars"></i></span>Manage
                columns</button>
        </div>
    </div>
</template>

<style scoped>
.emp {
    --bg: #fcfcfd;
    --surface: #fcfcfd;
    --head: #f5f7fb;
    --text: #0b0d0e;
    --muted: #5b6672;
    --border: #d8dee6;
    --hover: #eef2f7;
    --primary: #0b6bcb;
    --btn: #12171d;
    --btnt: #fff;
    color: var(--text);
    font: 15px/1.5 Inter, Roboto, 'Helvetica Neue', Arial, sans-serif;
    box-sizing: border-box;
}

@media (prefers-color-scheme: dark) {
    .emp {
        --bg: #0b0e12;
        --surface: #0f1419;
        --head: #141a21;
        --text: #eef1f4;
        --muted: #9aa5b1;
        --border: #26303b;
        --hover: #1a2129;
        --primary: #66b2ff;
        --btn: #eef1f4;
        --btnt: #0b0e12;
    }
}

:global(.dark) .emp {
    --bg: #0b0e12;
    --surface: #0f1419;
    --head: #141a21;
    --text: #eef1f4;
    --muted: #9aa5b1;
    --border: #26303b;
    --hover: #1a2129;
    --primary: #66b2ff;
    --btn: #eef1f4;
    --btnt: #0b0e12;
}

.emp *,
.emp *::before,
.emp *::after {
    box-sizing: border-box;
}

.crumb {
    font-size: 15px;
    margin-bottom: 14px;
}

.titlerow {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
}

h1 {
    font-size: 34px;
    font-weight: 600;
    margin: 0;
    flex: 1;
    letter-spacing: -0.01em;
}

button {
    font: inherit;
}

.sq {
    position: relative;
    width: 42px;
    height: 42px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--surface);
    color: var(--text);
    display: grid;
    place-items: center;
    cursor: pointer;
    padding: 0;
}

.sq:hover:not(:disabled) {
    background: var(--hover);
}

.sq:disabled {
    opacity: 0.4;
    cursor: default;
}

.sq svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.create {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 44px;
    padding: 0 18px;
    border-radius: 10px;
    border: 1px solid #000;
    background: var(--btn);
    color: var(--btnt);
    font-weight: 600;
    cursor: pointer;
}

.create svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
}

.err {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    margin-bottom: 12px;
    border-radius: 10px;
    background: #fde7e7;
    color: #c62828;
}

.err button {
    border: 1px solid currentColor;
    background: none;
    color: inherit;
    border-radius: 8px;
    padding: 4px 10px;
    cursor: pointer;
}

.grid {
    position: relative;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: var(--surface);
    overflow: hidden;
}

.bar {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: 3px;
    background: var(--primary);
    animation: load 1s ease-in-out infinite;
    transform-origin: left;
    z-index: 6;
}

@keyframes load {
    0% {
        transform: scaleX(0.1);
    }

    50% {
        transform: scaleX(0.8);
    }

    100% {
        transform: scaleX(0.1);
        opacity: 0.4;
    }
}

.tb {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 6px;
    padding: 12px;
    background: var(--head);
    position: relative;
}

.tb .sq {
    width: 40px;
    height: 40px;
}

.sep {
    width: 1px;
    height: 28px;
    background: var(--border);
    margin: 0 4px;
}

.sbox {
    flex: 1;
    position: relative;
}

.sbox input {
    width: 100%;
    height: 40px;
    border: 2px solid var(--primary);
    border-radius: 10px;
    padding: 0 34px 0 12px;
    background: var(--surface);
    color: var(--text);
    font: inherit;
}

.sbox .c {
    position: absolute;
    right: 6px;
    top: 7px;
    border: none;
    background: none;
    color: var(--text);
    cursor: pointer;
    font-size: 16px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
}

input:focus,
select:focus,
button:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 1px;
}

.badge {
    background: var(--primary);
    color: var(--btnt);
    font-size: 11px;
    font-weight: 600;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    padding: 0 5px;
    line-height: 1;
    display: grid;
    place-items: center;
}

.fb {
    position: absolute;
    top: -6px;
    right: -6px;
}

.pop {
    position: absolute;
    right: 12px;
    top: 58px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px 14px;
    z-index: 5;
    min-width: 200px;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.18);
    overflow-x: auto;
}

.pop label {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 3px 0;
    cursor: pointer;
}

.pop .cs {
    width: 100%;
    height: 36px;
    margin-bottom: 6px;
    border: 2px solid var(--primary);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text);
    padding: 0 8px;
    font: inherit;
}

.pop.menu {
    min-width: 170px;
    padding: 6px;
}

.pop.menu button,
.cm button {
    display: block;
    width: 100%;
    text-align: left;
    border: none;
    background: none;
    color: var(--text);
    padding: 8px 10px;
    border-radius: 6px;
    cursor: pointer;
}

.pop.menu button:hover,
.cm button:hover {
    background: var(--hover);
}

.pop.wide {
    min-width: 0;
}

.fp {
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: flex-end;
    min-width: 460px;
}

.pop .fp label {
    display: block;
    flex: 1;
    min-width: 0;
    padding: 0;
    font-size: 12px;
    color: var(--muted);
    cursor: default;
}

.pop .fp select,
.pop .fp input[type='text'] {
    display: block;
    width: 100%;
    height: 40px;
    margin: 2px 0 0;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text);
    padding: 0 8px;
    font: inherit;
}

.fp .x {
    flex: none;
    width: 36px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    padding: 0;
    border: none;
    background: none;
    color: var(--text);
    font-size: 18px;
    cursor: pointer;
    border-radius: 8px;
    margin-bottom: 8px;
}

.fp .x:hover {
    background: var(--hover);
}

.scroll {
    position: relative;
    overflow-x: auto;
    min-height: 260px;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 620px;
}

th {
    background: var(--head);
    text-align: left;
    font-weight: 600;
    padding: 0 6px 0 16px;
    height: 60px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}

th .h {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 2px;
}

th .h>span {
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    margin-right: 6px;
}

th.num {
    text-align: right;
    padding-right: 16px;
}

th.num .h {
    justify-content: flex-end;
}

th.num .h>span {
    order: 3;
    margin: 0;
}

th.num .h>.dots {
    order: 1;
}

th.num .h>.dots.si {
    order: 2;
}

th.act {
    width: 50px;
    cursor: default;
}

.dots {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: none;
    color: var(--text);
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    padding: 0;
}

.dots:hover {
    background: var(--hover);
}

.si:not(.on) {
    display: none;
}

@media (hover: hover) {
    .si:not(.on) {
        display: inline-flex;
    }

    th .dots {
        opacity: 0;
    }

    th:hover .dots,
    th .dots:focus-visible,
    th .dots.si.on {
        opacity: 1;
    }
}

.fi {
    margin-left: 8px;
    vertical-align: middle;
    cursor: pointer;
    color: var(--text);
}

td {
    padding: 0 16px;
    height: 57px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

td.num {
    text-align: right;
}

tbody tr:hover {
    background: var(--hover);
}

td.act {
    text-align: right;
}

.del {
    border: none;
    background: none;
    color: var(--muted);
    cursor: pointer;
    font-size: 16px;
    padding: 4px 8px;
    border-radius: 6px;
}

.del:hover {
    color: #d32f2f;
    background: var(--hover);
}

.ft {
    font-weight: 700;
}

.ft.y {
    color: #2e7d32;
}

.ft.n {
    color: #d32f2f;
}

.empty {
    text-align: center;
    color: var(--muted);
    padding: 40px;
}

.foot {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-items: center;
    gap: 14px;
    padding: 12px;
    background: var(--head);
}

.foot .sq {
    width: 44px;
    height: 44px;
}

.rpp {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-right: auto;
    font-size: 13px;
}

.rpp select {
    height: 36px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text);
    padding: 0 6px;
    font: inherit;
}

.pg {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cm {
    position: fixed;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 6px;
    z-index: 50;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
    min-width: 150px;
}

.cm hr {
    border: 0;
    border-top: 1px solid var(--border);
    margin: 4px 0;
}

.cm .ic {
    display: inline-block;
    width: 22px;
    color: var(--muted);
}


</style>