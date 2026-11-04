# Product Search Component

## ✅ Features
- **Reusability**: Encapsulated search logic into a standalone component.
- **Keyboard Navigation**: Arrows, Tab, Enter, Escape supported.
- **Live Search**: Filters by name or barcode.
- **Auto-scroll**: Highlighted result scrolls into view.

## 📄 Files
- `resources/js/Components/ProductSearch.vue`: The new reusable component.
- `resources/js/Pages/Inventory/Product/PrintLabel.vue`: Updated to use the component.

## 🛠 Usage
```vue
<ProductSearch 
    :products="products"
    @select="handleProductSelection"
    placeholder="Optional placeholder..."
/>
```

## 🔧 Props
- `products` (Array, required): List of product objects to search within.
- `placeholder` (String, optional): Custom placeholder text.

## 🔊 Events
- `select`: Emitted when a product is chosen (clicked or Enter). Payload is the product object.
