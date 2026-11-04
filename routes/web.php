<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CategoryController;
// use App\Http\Controllers\BrandController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustVenController;
use App\Http\Controllers\UserRightsController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ManufactureController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TransController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\OnboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

/* ----------------------------------------
   Onboarding Routes (Guest Access)
---------------------------------------- */

// Onboarding flow for new users
Route::middleware('guest')->group(function () {
    Route::get('/onboard', [OnboardController::class, 'index'])->name('onboard.index');
    Route::post('/onboard', [OnboardController::class, 'store'])->name('onboard.store');
});

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// Route::get('/', [AuthenticatedSessionController::class, 'checkUserHas']);

Route::middleware(['auth'])->group(function () {
    
   
   
      // Route::get('/', function () {
      //    return Inertia::render('Inventory/CheckPoint');
      // })->name('home');
      Route::get('/', [HomeController::class, 'index'])->name('home');

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard_view');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


/* ----------------------------------------
   Company route Start
---------------------------------------- */

    Route::get('/viewCompany',[CompanyController::class, 'index'])->name('viewCompany');
    // Route::get('/addCompany',[CompanyController::class, 'index'])->name('addCompany');

    Route::get('/addCompany', function () {
        return Inertia::render('Company/Create');
    })->name('addCompany');

    Route::post('/insertCompany',[CompanyController::class, 'store'])->name('insertCompany');
    Route::get('/deleteCompany/{id}',[CompanyController::class, 'delete'])->name('deleteCompany');

/* ----------------------------------------
   Company route End
---------------------------------------- */
    

/* ----------------------------------------
   Category route Start
---------------------------------------- */

    Route::get('/category',[CategoryController::class, 'index'])->name('category')->middleware('permission:category_view');
    Route::get('/createCategory', function () {
        return Inertia::render('Inventory/Category/Create');
    })->name('createCategory')->middleware('permission:category_create');
    Route::post('/insertCategory',[CategoryController::class, 'store'])->name('insertCategory')->middleware('permission:category_create');
    Route::get('/editCategory/{id}',[CategoryController::class, 'edit'])->name('editCategory')->middleware('permission:category_update');
    Route::post('/updateCategory',[CategoryController::class, 'update'])->name('updateCategory')->middleware('permission:category_update');
    Route::get('/deleteCategory/{id}',[CategoryController::class, 'delete'])->name('deleteCategory')->middleware('permission:category_update');
    Route::get('/statusCategory/{status}/{id}',[CategoryController::class, 'status'])->name('statusCategory');

    Route::get('/addEditCategory/{id}',[CategoryController::class, 'addEdit'])->name('addEditCategory');

/* ----------------------------------------
   Category route End
---------------------------------------- */


/* ----------------------------------------
   Brand route Start
---------------------------------------- */

   //  Route::get('/brand',[BrandController::class, 'index'])->name('brand');
   //  Route::post('/insertBrand',[BrandController::class, 'store'])->name('insertBrand');
   //  Route::get('/addEditBrand/{id}',[BrandController::class, 'addEdit'])->name('addEditBrand');
   //  Route::get('/statusBrand/{status}/{id}',[BrandController::class, 'status'])->name('statusBrand');

/* ----------------------------------------
   Brand route End
---------------------------------------- */


/* ----------------------------------------
   Stock route Start
---------------------------------------- */

    Route::get('/viewStock',[StockController::class, 'index'])->name('viewStock');
    Route::get('/getStockDetails',[StockController::class, 'getStockDetails'])->name('getStockDetails');
    Route::get('/addStock',[StockController::class, 'fillCategory2'])->name('addStock');
    Route::post('/insertStock',[StockController::class, 'store2'])->name('insertStock');
    Route::get('/editStock/{id}',[StockController::class, 'edit2'])->name('editStock');
    Route::post('/updateStock',[StockController::class, 'update2'])->name('updateStock');
    Route::get('/statusStock/{status}/{id}',[StockController::class, 'status'])->name('statusStock');

 /* ----------------------------------------
   Stock route End
---------------------------------------- */   
    

/* ----------------------------------------
   Product route Start
---------------------------------------- */

    Route::get('/viewProduct/',[ProductController::class, 'index'])->name('viewProduct')->middleware('permission:product_view');
    Route::get('/addProduct',[ProductController::class, 'createProduct'])->name('addProduct')->middleware('permission:product_create');
    Route::get('/getProductDetails',[ProductController::class, 'getProductDetails'])->name('getProductDetails');
    Route::get('/exportProduct',[ProductController::class, 'exportProducts'])->middleware('permission:product_view');
    Route::post('/insertProduct',[ProductController::class, 'store'])->name('insertProduct')->middleware('permission:product_create');
    Route::get('/editProduct/{id}',[ProductController::class, 'edit'])->name('editProduct')->middleware('permission:product_update');
    Route::post('/updateProduct',[ProductController::class, 'update'])->name('updateProduct')->middleware('permission:product_update');
    Route::get('/deleteProduct/{id}',[ProductController::class, 'delete'])->middleware('permission:product_update');
    Route::get('/statusProduct/{status}/{id}',[ProductController::class, 'status'])->name('statusProduct');
    
    // Print Label Routes
    Route::get('/printLabelForm/{id?}',[ProductController::class, 'viewPrintLabel'])->name('printLabelForm')->middleware('permission:print_label_create');
    Route::get('/printLabel/{productId}/{quantity}',[ProductController::class, 'printLabel'])->name('printLabel')->middleware('permission:print_label_create');
    Route::post('/product/store-print-session', [ProductController::class, 'storePrintSession'])->name('product.storePrintSession');
    Route::get('/product/print-preview', [ProductController::class, 'printPreview'])->name('product.printPreview');
    Route::get('/product/stock-history/{id?}', [ProductController::class, 'viewStockHistory'])->name('product.stockHistory');
    Route::get('/product/duplicate/{id?}', [ProductController::class, 'duplicateProduct'])->name('product.duplicateProduct');
    Route::get('/api/product/stock-history/{id}', [ProductController::class, 'stockHistoryApi'])->name('product.stockHistoryApi');
    
    Route::get('/inventoryCheck', function () {
        return Inertia::render('Inventory/CheckPoint');
    })->name('inventoryCheck');
    
 /* ----------------------------------------
   Product route End
---------------------------------------- */      
    

/* ----------------------------------------
   CustomerVendor route Start
---------------------------------------- */
        
    Route::get('/createAccount', function () {
        return Inertia::render('Account/Create');
    })->name('createAccount')->middleware('permission:account_create');

    Route::post('/insertAccount',[CustVenController::class, 'store'])->name('insertAccount')->middleware('permission:account_create');
    Route::post('/insertAccountApi',[CustVenController::class, 'storeApi'])->name('insertAccountApi')->middleware('permission:account_create');
    Route::get('/viewAccount',[CustVenController::class, 'index'])->name('viewAccount')->middleware('permission:account_view');
    Route::get('/editAccount/{id}',[CustVenController::class, 'edit'])->name('editAccount')->middleware('permission:account_update');
    Route::post('/updateAccount',[CustVenController::class, 'update'])->name('updateAccount')->middleware('permission:account_update');
    Route::get('/statusAccount/{status}/{id}',[CustVenController::class, 'statusAccount'])->name('statusAccount');
    
    Route::get('/checkPointCV',[CustVenController::class, 'checkPoint'])->name('checkPointCV');
    Route::get('/addCustVen',[CustVenController::class, 'form'])->name('addCustVen');
    Route::get('/viewCustomer',[CustVenController::class, 'viewCustomer'])->name('viewCustomer');
    Route::get('/viewVendor',[CustVenController::class, 'viewVendor']);
    Route::post('/insertCustVen',[CustVenController::class, 'store']);
    Route::get('/editCustVen/{id}',[CustVenController::class, 'edit']);
    Route::post('/updateCustVen',[CustVenController::class, 'update']);
    Route::get('/deleteCustVen/{id}',[CustVenController::class, 'delete']);
    Route::get('/statusCust/{status}/{id}',[CustVenController::class, 'statusCust']);
    Route::get('/statusVen/{status}/{id}',[CustVenController::class, 'statusVen']);

 /* ----------------------------------------
   CustomerVendor route End
---------------------------------------- */     
    

/* ----------------------------------------
   Transaction route Start
---------------------------------------- */   

    // Route::get('/viewTrans',[TransController::class, 'menuTran']);
    // Route::get('/cashPrint/{id}',[TransController::class, 'cashPrint']);

    Route::get('/transCheck', function () {
        return Inertia::render('Trans/CheckPoint');
    })->name('transCheck');

/* ----------------------------------------
   Transaction route End
---------------------------------------- */     
    

/* ----------------------------------------
   Cashin route Start
---------------------------------------- */ 

    Route::get('/viewTransIn/{id}',[TransController::class, 'indexTransIn'])->name('viewTransIn')->middleware('permission:trans_cashin_view');
    Route::get('/createTransIn',[TransController::class, 'fillTransIn'])->name('createTransIn')->middleware('permission:trans_cashin_create');
    Route::post('/insertTransIn',[TransController::class, 'storeTransIn'])->name('insertTransIn')->middleware('permission:trans_cashin_create');
    Route::get('/deleteTransIn/{id}',[TransController::class, 'deleteTransIn'])->name('deleteTransIn')->middleware('permission:trans_cashin_delete');

/* ----------------------------------------
   Cashin route End
---------------------------------------- */    
    

/* ----------------------------------------
   Cashout route Start
---------------------------------------- */

    Route::get('/viewTransOut/{id}',[TransController::class, 'indexTransOut'])->name('viewTransOut')->middleware('permission:trans_cashout_view');
    Route::get('/createTransOut',[TransController::class, 'fillTransOut'])->name('createTransOut')->middleware('permission:trans_cashout_create');
    Route::post('/insertTransOut',[TransController::class, 'storeTransOut'])->name('insertTransOut')->middleware('permission:trans_cashout_create');
    Route::get('/deleteTransOut/{id}',[TransController::class, 'deleteTransOut'])->name('deleteTransOut')->middleware('permission:trans_cashout_delete');

/* ----------------------------------------
   Cashout route End
---------------------------------------- */
    

/* ----------------------------------------
   Stock Opening route Start
---------------------------------------- */

    Route::get('/viewStockOpening',[ProductController::class, 'viewStock']);
    Route::post('/searchStockOpening',[ProductController::class, 'searchStock']);
    Route::post('/updateStockOpening',[ProductController::class, 'updateStock']);

/* ----------------------------------------
   Stock Opening route End
---------------------------------------- */    
    

/* ----------------------------------------
   Countsheet route Start
---------------------------------------- */    
    
    Route::get('/countsheet',[ProductController::class, 'countsheet'])->name('countsheet');
    Route::get('/searchProduct/{val}',[ProductController::class, 'searchProduct']);
    Route::get('/searchProductFor',[ProductController::class, 'searchProductFor'])->name('countsheet1');
    Route::get('/searchProductByFilter/{filter}',[ProductController::class, 'searchProductByFilter']);
    Route::post('/updateProductQty',[ProductController::class, 'updateProductQty'])->name('updateProductQty');

/* ----------------------------------------
   Countsheet route End
---------------------------------------- */     
    

/* ----------------------------------------
   Bulk price editing route Start
---------------------------------------- */

    Route::get('/viewBulk',[ProductController::class, 'viewBulk'])->name('viewBulk');
    Route::get('/searchBulk',[ProductController::class, 'searchBulk']);
    Route::get('/searchBulkByFilter/{filter}',[ProductController::class, 'searchBulkByFilter']);
    Route::post('/updateBulk',[ProductController::class, 'updateBulk'])->name('updateBulk');

/* ----------------------------------------
   Bulk price editing route End
---------------------------------------- */    
    

/* ----------------------------------------
   Re order route Start
---------------------------------------- */    
    
    Route::get('/viewReorder',[ProductController::class, 'viewReorder']);

/* ----------------------------------------
   Re order route End
---------------------------------------- */      
    

/* ----------------------------------------
   Ledger route Start
---------------------------------------- */

    Route::get('/viewLedgerForm',[LedgerController::class, 'ledgerForm'])->name('viewLedgerForm')->middleware('permission:ledger_view');

    Route::get('/checkPoint',[LedgerController::class, 'checkPoint']);
    Route::get('/viewCustLedger',[LedgerController::class, 'custForm']);
    Route::get('/viewVenLedger',[LedgerController::class, 'venForm']);
    Route::get('/Ledger',[LedgerController::class, 'ledger'])->name('Ledger');
    Route::get('/print/{f}/{id}',[LedgerController::class, 'printTrans']);

/* ----------------------------------------
   Ledger route End
---------------------------------------- */


/* ----------------------------------------
   Sale Invoice route Start
---------------------------------------- */

    Route::get('/saleForm3',[SaleController::class, 'saleForm3'])->name('saleForm3')->middleware('permission:sale_invoice_create'); // vue sale
    Route::post('/saveSaleInvoice',[SaleController::class, 'saveSaleInvoice'])->name('saveSaleInvoice');

    Route::get('/saleForm',[SaleController::class, 'saleForm'])->name('saleForm'); // blade sale
    Route::get('/saleForm2',[SaleController::class, 'saleForm2'])->name('saleForm2'); // touch sale
    Route::get('/saleForm4',[SaleController::class, 'saleForm4'])->name('saleForm4');
    Route::get('/saleForm5/{id}/{from?}',[SaleController::class, 'saleForm5'])->name('saleForm5');
    Route::get('/searchProduct/{barcode}/{name}/{rowNum}/{from}',[SaleController::class, 'getAllProduct']);
    Route::get('/getDetail/{id}/{from}',[SaleController::class, 'getDetail']);
    Route::post('/saveInvoice/{barcode}/{qty}/{price}/{finalAmt}/{from}',[SaleController::class, 'saveInvc'])->name('saveInvoice');
    Route::post('/saveInvoice2/',[SaleController::class, 'saveInvc2'])->name('saveInvoice2');
    Route::get('/salePrint/{id}',[SaleController::class, 'sPrint'])->name('salePrint');
    Route::get('/viewSInvoice',[SaleController::class, 'sInvoice'])->name('viewSInvoice')->middleware('permission:sale_invoice_view');
    Route::get('/getSInvoice',[SaleController::class, 'getSInvoice']);
    Route::get('/saleReturn/{id}',[SaleController::class, 'saleReturn'])->name('saleReturn');
    Route::post('/saleReturn/',[SaleController::class, 'saveSaleReturn'])->name('saveSaleReturn');
    Route::get('/getCustomerDetails/{id}',[SaleController::class, 'getCustomerDetails'])->name('getCustomerDetails');

    //// working on converting sale invoice to vue
    Route::get('/createSale',[SaleController::class, 'createSale'])->name('createSale');

    Route::get('/getRegisterDetails',[SaleController::class, 'getRegisterDetails'])->name('getRegisterDetails');
    Route::get('/checkRegisterStatus',[SaleController::class, 'checkRegisterStatus'])->name('checkRegisterStatus');
    Route::get('/getRecentInvoiceDetails',[SaleController::class, 'getRecentInvoiceDetails'])->name('getRecentInvoiceDetails');
    Route::delete('/deleteInvoiceApi/{id}',[SaleController::class, 'deleteInvoice'])->name('deleteInvoiceApi');
    Route::post('/insertRegister/',[SaleController::class, 'insertRegister'])->name('insertRegister');
    Route::post('/updateRegister/',[SaleController::class, 'updateRegister'])->name('updateRegister');
    Route::get('/customerDisplay',[SaleController::class, 'customerDisplay'])->name('customerDisplay');

/* ----------------------------------------
   Sale Invoice route End
---------------------------------------- */    
    

/* ----------------------------------------
   Purchase Invoice route Start
---------------------------------------- */

    Route::get('/purchaseForm2',[SaleController::class, 'purchaseForm2'])->name('purchaseForm2')->middleware('permission:purchase_invoice_create'); // vue sale
    Route::post('/savePurchaseInvoice',[SaleController::class, 'savePurchaseInvoice'])->name('savePurchaseInvoice');
    Route::get('/getVendorDetails/{id}',[SaleController::class, 'getVendorDetails'])->name('getVendorDetails');

    Route::get('/purchaseForm',[SaleController::class, 'purchaseForm'])->name('purchaseForm')->middleware('permission:purchase_invoice_create');
    Route::post('/purchaseInvoice/{barcode}/{qty}/{price}/{finalAmt}/{from}',[SaleController::class, 'savePInvc']);
    Route::get('/purPrint/{id}',[SaleController::class, 'pPrint']);
    Route::get('/viewPInvoice',[SaleController::class, 'pInvoice'])->name('viewPInvoice')->middleware('permission:purchase_invoice_view');
   //  Route::get('/purchaseReturn/{id}',[SaleController::class, 'purchaseReturn'])->name('purchaseReturn');
   //  Route::post('/purchaseReturn/',[SaleController::class, 'savepurchaseReturn'])->name('savepurchaseReturn');
   //  Route::post('/savePurchaseReturn/',[SaleController::class, 'savePurchaseReturn'])->name('savePurchaseReturn');
/* ----------------------------------------
   Purchase Invoice route End
---------------------------------------- */ 

/* ----------------------------------------
   Invoice Return route Start
---------------------------------------- */

    Route::get('/saleReturn/{id}',[SaleController::class, 'saleReturn'])->name('saleReturn');
    Route::post('/saleReturn/',[SaleController::class, 'saveSaleReturn'])->name('saveSaleReturn');
    
    Route::get('/purchaseReturn/{id}',[SaleController::class, 'purchaseReturn'])->name('purchaseReturn');
    Route::post('/savePurchaseReturn/',[SaleController::class, 'savePurchaseReturn'])->name('savePurchaseReturn');
    
    Route::get('/returnCheck', function () {
        return Inertia::render('InvoiceReturn/CheckPoint');
    })->name('returnCheck');
    
    Route::get('/viewSaleReturn',[SaleController::class, 'viewSaleReturn'])->name('viewSaleReturn')->middleware('permission:invoice_return_view');
    Route::get('/viewPurchaseReturn',[SaleController::class, 'viewPurchaseReturn'])->name('viewPurchaseReturn')->middleware('permission:invoice_return_view');


/* ----------------------------------------
   Invoice Return route End
---------------------------------------- */
    

/* ----------------------------------------
   Stock Form route Start
---------------------------------------- */

    Route::get('/stockForm',[ProductController::class, 'viewStockForm'])->name('stockForm');

/* ----------------------------------------
   Stock Form route End
---------------------------------------- */    
    

/* ----------------------------------------
   Business Settings route Start
---------------------------------------- */

    Route::get('/settings/business', [App\Http\Controllers\BusinessSettingController::class, 'index'])->name('business.settings.index')->middleware('permission:settings_create');
    Route::post('/settings/business', [App\Http\Controllers\BusinessSettingController::class, 'update'])->name('business.settings.update')->middleware('permission:settings_create');
    Route::post('/settings/business/test-email', [App\Http\Controllers\BusinessSettingController::class, 'testEmail'])->name('business.settings.test-email')->middleware('permission:settings_create');

/* ----------------------------------------
   Business Settings route End
---------------------------------------- */    
    

/* ----------------------------------------
   Tax Management route Start
---------------------------------------- */

    // Tax Routes
    Route::get('/settings/tax', [TaxController::class, 'index'])->name('tax.index')->middleware('permission:settings_create');
    Route::get('/api/taxes', [TaxController::class, 'getTaxes'])->name('tax.getTaxes')->middleware('permission:settings_create');
    Route::post('/tax/store', [TaxController::class, 'storeTax'])->name('tax.store')->middleware('permission:settings_create');
    Route::post('/tax/update', [TaxController::class, 'updateTax'])->name('tax.update')->middleware('permission:settings_create');
    Route::get('/tax/delete/{id}', [TaxController::class, 'deleteTax'])->name('tax.delete')->middleware('permission:settings_create');
    Route::get('/tax/toggle-status/{id}', [TaxController::class, 'toggleTaxStatus'])->name('tax.toggleStatus')->middleware('permission:settings_create');
    
    // Tax Group Routes
    Route::get('/api/tax-groups', [TaxController::class, 'getTaxGroups'])->name('taxGroup.getTaxGroups')->middleware('permission:settings_create');
    Route::post('/tax-group/store', [TaxController::class, 'storeTaxGroup'])->name('taxGroup.store')->middleware('permission:settings_create');
    Route::post('/tax-group/update', [TaxController::class, 'updateTaxGroup'])->name('taxGroup.update')->middleware('permission:settings_create');
    Route::get('/tax-group/delete/{id}', [TaxController::class, 'deleteTaxGroup'])->name('taxGroup.delete')->middleware('permission:settings_create');
    Route::get('/tax-group/toggle-status/{id}', [TaxController::class, 'toggleTaxGroupStatus'])->name('taxGroup.toggleStatus')->middleware('permission:settings_create');

/* ----------------------------------------
   Tax Management route End
---------------------------------------- */ 

/* ----------------------------------------
   Locations route Start
---------------------------------------- */

    Route::get('/settings/locations', [LocationController::class, 'index'])->name('locations.index')->middleware('permission:settings_create');
    Route::get('/api/locations', [LocationController::class, 'getLocations'])->name('locations.getLocations')->middleware('permission:settings_create');
    Route::post('/locations/store', [LocationController::class, 'storeLocation'])->name('locations.store')->middleware('permission:settings_create');
    Route::post('/locations/update', [LocationController::class, 'updateLocation'])->name('locations.update')->middleware('permission:settings_create');
    Route::get('/locations/delete/{id}', [LocationController::class, 'deleteLocation'])->name('locations.delete')->middleware('permission:settings_create');
    Route::get('/locations/toggle-status/{id}', [LocationController::class, 'toggleLocationStatus'])->name('locations.toggleStatus')->middleware('permission:settings_create');
    
/* ----------------------------------------
   Locations route End
---------------------------------------- */ 
    

/* ----------------------------------------
   User route Start
---------------------------------------- */

    Route::get('/viewProfile',[UserRightsController::class, 'viewUser'])->name('viewProfile')->middleware('permission:settings_create');
    Route::get('/viewUserForm',[UserRightsController::class, 'viewUForm'])->name('viewUserForm')->middleware('permission:settings_create');
    Route::get('/userRights/{id}',[UserRightsController::class, 'userRights'])->name('userRights')->middleware('permission:settings_create');
    Route::get('/saveUserRights',[UserRightsController::class, 'saveUserRights'])->middleware('permission:settings_create');
    
    Route::post('/createUser',[UserRightsController::class, 'store'])->name('createUser')->middleware('permission:settings_create');
    Route::get('/editUser/{id}',[UserRightsController::class, 'edit'])->name('editUser')->middleware('permission:settings_create');
    Route::post('/updateUser',[UserRightsController::class, 'update'])->name('updateUser')->middleware('permission:settings_create');
    Route::post('/setPermission',[UserRightsController::class, 'setPermission'])->name('setPermission')->middleware('permission:settings_create');
    Route::get('/deleteUser/{id}',[UserRightsController::class, 'deleteUser'])->name('deleteUser')->middleware('permission:settings_create');

/* ----------------------------------------
   User route End
---------------------------------------- */    
    

/* ----------------------------------------
   Report route Start
---------------------------------------- */

    Route::get('/report/{from}', function ($from) {
        return Inertia::render('Report/Create', [
            'from' => $from,
        ]);
    })->name('report')->middleware('permission:report_create');

    Route::get('/cashinReport',[ReportController::class, 'cashinReport'])->name('cashinReport');
    Route::get('/getCashinReport',[ReportController::class, 'getCashinReport'])->name('getCashinReport');
    Route::get('/receivableReport',[ReportController::class, 'receivableReport'])->name('receivableReport');
    Route::get('/getReceivableReport',[ReportController::class, 'getReceivableReport'])->name('getReceivableReport');
    Route::get('/dayendReport',[ReportController::class, 'dayendReport'])->name('dayendReport');
    Route::get('/getDayendReport',[ReportController::class, 'getDayendReport'])->name('getDayendReport');
    Route::get('/stockReport',[ReportController::class, 'stockReport'])->name('stockReport');
    Route::get('/getStockReport',[ReportController::class, 'getStockReport'])->name('getStockReport');
    Route::get('/cashoutReport',[ReportController::class, 'cashoutReport'])->name('cashoutReport');
    Route::get('/getCashoutReport',[ReportController::class, 'getCashoutReport'])->name('getCashoutReport');
    Route::get('/payableReport',[ReportController::class, 'payableReport'])->name('payableReport');
    Route::get('/getPayableReport',[ReportController::class, 'getPayableReport'])->name('getPayableReport');
    Route::get('/profitReport',[ReportController::class, 'profitReport'])->name('profitReport');
    Route::get('/dayendDetailReport',[ReportController::class, 'dayendDetailReport'])->name('dayendDetailReport');
    Route::get('/getDayendDetailReport',[ReportController::class, 'getDayendDetailReport'])->name('getDayendDetailReport');
    Route::get('/stockTrack',[ReportController::class, 'stockTrack'])->name('stockTrack');
    Route::get('/getStockTrackReport',[ReportController::class, 'getStockTrackReport'])->name('getStockTrackReport');
    Route::get('/palReport',[ReportController::class, 'palReport'])->name('palReport');
    Route::get('/getpalReport',[ReportController::class, 'getpalReport'])->name('getpalReport');
    Route::get('/productWiseSaleReport',[ReportController::class, 'productWiseSaleReport'])->name('productWiseSaleReport');
    Route::get('/getProductWiseSaleReport',[ReportController::class, 'getProductWiseSaleReport'])->name('getProductWiseSaleReport');
    Route::get('/brandWiseSaleReport',[ReportController::class, 'brandWiseSaleReport'])->name('brandWiseSaleReport');
    Route::get('/getBrandWiseSaleReport',[ReportController::class, 'getBrandWiseSaleReport'])->name('getBrandWiseSaleReport');

/* ----------------------------------------
   Report route End
---------------------------------------- */     
    

/* ----------------------------------------
   Barcode route Start
---------------------------------------- */   

    Route::get('/viewBarcode',[BarcodeController::class, 'viewBarcode'])->name('viewBarcode');
    Route::post('/createBarcode',[BarcodeController::class, 'createBarcode']);

/* ----------------------------------------
   Barcode route End
---------------------------------------- */


/* ----------------------------------------
   Manufacturing route Start
---------------------------------------- */

    Route::get('/manufacturing/view',[ManufactureController::class, 'index'])->name('manufacturingView')->middleware('permission:manufactring_view');
    Route::get('/manufacturing/recipe/create',[ManufactureController::class, 'createRecipe'])->name('manufacturing.recipe.create')->middleware('permission:manufactring_create');
    Route::post('/manufacturing/recipe/store',[ManufactureController::class, 'storeRecipe'])->name('manufacturing.recipe.store')->middleware('permission:manufactring_create');
    
    Route::get('/manufacturing/production/create',[ManufactureController::class, 'createProduction'])->name('manufacturing.production.create')->middleware('permission:manufactring_create');
    Route::post('/manufacturing/production/store',[ManufactureController::class, 'storeProduction'])->name('manufacturing.production.store')->middleware('permission:manufactring_create');
    Route::get('/manufacturing/getRecipes',[ManufactureController::class, 'getRecipes'])->name('getRecipes');
    Route::get('/manufacturing/getProductions',[ManufactureController::class, 'getProductions'])->name('getProductions');
    
    Route::get('/viewFinishedProducts',[ManufactureController::class, 'getProductions'])->name('viewFinishedProducts');
    
    Route::get('/manufacturing/recipe/edit/{id}',[ManufactureController::class, 'editRecipe'])->name('manufacturing.recipe.edit');
    Route::post('/manufacturing/recipe/update/{id}',[ManufactureController::class, 'updateRecipe'])->name('manufacturing.recipe.update');
    Route::get('/manufacturing/recipe/status/{status}/{id}',[ManufactureController::class, 'statusRecipe'])->name('manufacturing.recipe.status');
    Route::get('/manufacturing/recipe/delete/{id}',[ManufactureController::class, 'deleteRecipe'])->name('manufacturing.recipe.delete');
     
    Route::get('/manufacturing/production/edit/{id}',[ManufactureController::class, 'editProduction'])->name('manufacturing.production.edit');
    Route::post('/manufacturing/production/update/{id}',[ManufactureController::class, 'updateProduction'])->name('manufacturing.production.update');
    Route::get('/manufacturing/production/status/{status}/{id}',[ManufactureController::class, 'statusProduction'])->name('manufacturing.production.status');
    
    Route::get('/createFinishedProducts',[ManufactureController::class, 'createFinishedProducts'])->name('createFinishedProducts')->middleware('permission:manufactring_create');
    Route::post('/storeFinishedProducts',[ManufactureController::class, 'storeFinishedProducts'])->name('storeFinishedProducts')->middleware('permission:manufactring_create');
    Route::get('/editFinishedProducts/{id}',[ManufactureController::class, 'editFinishedProducts'])->name('editFinishedProducts')->middleware('permission:manufactring_update');
    Route::post('/updateFinishedProducts',[ManufactureController::class, 'updateFinishedProducts'])->name('updateFinishedProducts')->middleware('permission:manufactring_update');
    
 /* ----------------------------------------
   Manufacturing route End
---------------------------------------- */ 

/* ----------------------------------------
   Delivery Order route Start
---------------------------------------- */

    Route::get('/delivery/order/view',[DeliveryOrderController::class, 'index'])->name('deliveryOrder.view')->middleware('permission:delivery_order_view');
    Route::get('/getDeliveryOrderDetails',[DeliveryOrderController::class, 'getDeliveryOrderDetails'])->name('deliveryOrder.getDeliveryOrderDetails');
    Route::post('/delivery/order/update-status',[DeliveryOrderController::class, 'statusDeliveryOrder'])->name('deliveryOrder.updateStatus');
    
 /* ----------------------------------------
   Delivery Order route End
---------------------------------------- */ 
// testing component
   Route::get('/tableTest', function () {
      return Inertia::render('Test/index');
   })->name('tableTest');

   Route::get('/test', function () {
      return Inertia::render('Test/test');
   })->name('test');

});

require __DIR__.'/auth.php';
