# Print Label Page - Complete Redesign

## Overview
The Print Label page has been completely redesigned with a modern, user-friendly interface that allows users to search products, configure label settings, and print customized product labels.

## Key Features

### 1. **Large Search Bar**
- **Live Search**: Real-time product search as you type
- **Search Criteria**: Search by product name OR barcode
- **Dropdown Results**: Shows up to 10 matching products
- **Quick Add**: Click on any result to add to the print queue
- **Auto-clear**: Search field clears after adding a product

### 2. **Dynamic Products Table**
The table dynamically displays selected products with the following columns:

#### Columns:
- **Product** (40% width)
  - Product name (bold)
  - Barcode (gray text)
  - Price (blue text)
  
- **No. of Labels** (20% width)
  - Number input field
  - Minimum value: 1
  - Default: 1 label per product
  
- **Packaging Date** (25% width)
  - Date picker input
  - Default: Today's date
  - Individual date per product
  
- **Action** (15% width)
  - Remove button (trash icon)
  - Removes product from print queue

#### Features:
- Empty state message when no products selected
- Hover effect on rows
- Responsive design
- Badge showing total products count

### 3. **Label Content & Size Settings**

Each setting has:
- **Checkbox**: Toggle visibility on/off
- **Font Size Control**: Adjustable size (8-24px)
- **Disabled State**: Size control disabled when checkbox unchecked

#### Available Settings:

**a) Product Name**
- Default: Enabled
- Default Size: 14px
- Shows product name on label

**b) Product Variation**
- Default: Enabled
- Default Size: 12px
- Shows variation details if product has variations

**c) Price**
- Default: Enabled
- Default Size: 16px
- **Price Type Dropdown**:
  - "Inc Tax" (Including Tax)
  - "Exc Tax" (Excluding Tax)

**d) Business Name**
- Default: Enabled
- Default Size: 10px
- Shows business/company name

**e) Packaging Date**
- Default: Enabled
- Default Size: 10px
- Shows the packaging date from table

### 4. **Barcode & Sheet Settings**

#### Labels Per Sheet
Options:
- 20 Labels per Sheet
- 30 Labels per Sheet
- 32 Labels per Sheet
- 40 Labels per Sheet
- 50 Labels per Sheet

Default: 20

#### Sheet Size
Options:
- A4 (210 x 297 mm)
- Letter (8.5 x 11 in)
- Legal (8.5 x 14 in)

Default: A4

#### Label Size
Options:
- Small (38 x 21 mm)
- Standard (52 x 29 mm) - Default
- Medium (70 x 37 mm)
- Large (99 x 57 mm)

### 5. **Print Button**
- **Location**: Bottom right
- **Large Size**: btn-lg with padding
- **Dynamic Text**: Shows total number of labels to print
- **Disabled State**: Disabled when no products selected
- **Icon**: Print icon with text

## Technical Implementation

### Data Structure

```javascript
// Selected Product Structure
{
    id: number,
    name: string,
    barcode: string,
    retail: number,
    variation: array,
    labels: number,
    packagingDate: string (YYYY-MM-DD)
}

// Label Settings Structure
{
    // Visibility toggles
    showProductName: boolean,
    showVariation: boolean,
    showPrice: boolean,
    showBusinessName: boolean,
    showPackagingDate: boolean,
    
    // Font sizes
    productNameSize: number (px),
    variationSize: number (px),
    priceSize: number (px),
    businessNameSize: number (px),
    packagingDateSize: number (px),
    
    // Price options
    priceType: 'inc' | 'exc',
    
    // Barcode settings
    labelsPerSheet: 20 | 30 | 32 | 40 | 50,
    sheetSize: 'A4' | 'Letter' | 'Legal',
    labelSize: 'small' | 'standard' | 'medium' | 'large'
}
```

### Key Functions

**1. Search & Filter**
```javascript
const filteredProducts = computed(() => {
    // Filters products by name or barcode
    // Returns top 10 matches
});
```

**2. Add Product**
```javascript
const addProduct = (product) => {
    // Prevents duplicates
    // Adds to selectedProducts array
    // Clears search
};
```

**3. Remove Product**
```javascript
const removeProduct = (index) => {
    // Removes from selectedProducts array
};
```

**4. Print Labels**
```javascript
const printLabels = () => {
    // Validates products selected
    // Stores data in sessionStorage
    // Opens print preview window
};
```

## User Experience Flow

1. **Search Product**: User types product name or barcode
2. **Select Product**: Click on search result to add
3. **Configure Quantity**: Set number of labels per product
4. **Set Packaging Date**: Choose date for each product
5. **Customize Label**: Toggle fields and adjust font sizes
6. **Configure Layout**: Select sheet size and labels per sheet
7. **Print**: Click print button to generate labels

## Visual Design

### Color Scheme
- **Primary**: Blue (#007bff)
- **Success**: Green
- **Danger**: Red
- **Gray Shades**: For text hierarchy
- **White**: Card backgrounds

### Typography
- **Headers**: Bold, larger font
- **Body**: Regular weight
- **Small Text**: 0.875rem (14px)
- **Labels**: Font-weight: 600

### Spacing
- **Card Padding**: 1rem - 1.5rem
- **Section Margins**: 1rem (mb-4)
- **Input Gaps**: 0.5rem

### Components Used
- Bootstrap 5 cards
- Bootstrap form controls
- Font Awesome icons
- Custom hover effects
- Responsive grid system

## Next Steps (Backend Integration)

To complete the implementation, you'll need to:

1. **Create Print Preview Route**
   ```php
   Route::get('/printLabel/preview', [ProductController::class, 'printPreview'])
       ->name('printLabelPreview');
   ```

2. **Create Print Preview Blade Template**
   - Read data from sessionStorage
   - Generate label layout based on settings
   - Include barcode generation library
   - Apply CSS for print media

3. **Barcode Generation**
   - Use a library like `picqer/php-barcode-generator`
   - Generate barcodes based on product barcode
   - Render as SVG or image

## Benefits

✅ **User-Friendly**: Intuitive search and selection
✅ **Flexible**: Customizable label content and sizes
✅ **Efficient**: Batch printing with different dates
✅ **Professional**: Clean, modern interface
✅ **Responsive**: Works on all screen sizes
✅ **Accessible**: Clear labels and visual hierarchy
