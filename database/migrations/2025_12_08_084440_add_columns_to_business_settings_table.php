<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            // General / Tax
            $table->boolean('inline_tax_sale_purchase')->default(false);

            // Products
            $table->string('product_expiry_type', 50)->default('add_item_expiry');
            $table->string('on_product_expiry', 50)->default('keep_selling');
            $table->boolean('enable_brands')->default(false);
            $table->boolean('enable_sub_category')->default(false);
            $table->boolean('enable_price_tax_info')->default(false);
            $table->boolean('enable_sub_units')->default(false);
            $table->boolean('enable_racks')->default(false);
            $table->boolean('enable_row')->default(false);
            $table->boolean('enable_position')->default(false);
            $table->boolean('enable_warranty')->default(false);
            $table->boolean('enable_product_image')->default(false);
            
            // Contact
            $table->decimal('default_credit_limit', 22, 4)->default(0);

            // Sale
            $table->decimal('default_sale_discount', 5, 2)->default(0);
            $table->string('default_sale_tax', 100)->nullable();
            $table->string('sales_item_addition_method', 50)->default('Add Item In New Row');
            $table->string('amount_rounding_method', 50)->default('None');
            $table->boolean('sales_price_is_minimum_selling_price')->default(false);
            $table->boolean('allow_overselling')->default(false);
            $table->boolean('enable_sales_order')->default(false);
            $table->boolean('is_pay_term_required')->default(false);
            $table->string('commission_agent', 50)->default('Disable');
            $table->string('commission_calculation_type', 50)->default('Invoice Value');
            $table->boolean('is_commission_agent_required')->default(false);
            $table->string('public_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->boolean('is_payment_link_enabled')->default(false);

            // POS System - Shortcuts
            $table->string('express_checkout', 100)->nullable();
            $table->string('pay_and_checkout', 100)->nullable();
            $table->string('draft', 100)->nullable();
            $table->string('cancelVal', 100)->nullable();
            $table->string('go_to_product_quantity', 100)->nullable();
            $table->string('weighing_scale', 100)->nullable();
            $table->string('edit_discount', 100)->nullable();
            $table->string('edit_order_tax', 100)->nullable();
            $table->string('add_payment_row', 100)->nullable();
            $table->string('finalize_payment', 100)->nullable();
            $table->string('add_new_product', 100)->nullable();

            // POS Settings
            $table->boolean('disable_multiple_pay')->default(false);
            $table->boolean('disable_draft')->default(false);
            $table->boolean('disable_express_checkout')->default(false);
            $table->boolean('disable_product_suggestion')->default(false);
            $table->boolean('disable_recent_transactions')->default(false);
            $table->boolean('disable_discount')->default(false);
            $table->boolean('disable_order_tax')->default(false);
            $table->boolean('subtotal_editable')->default(false);
            $table->boolean('disable_suspend_sale')->default(false);
            $table->boolean('enable_transaction_date')->default(false);
            $table->boolean('enable_service_staff')->default(false);
            $table->boolean('is_service_staff_required')->default(false);
            $table->boolean('disable_credit_sale_button')->default(false);
            $table->boolean('enable_weighing_scale')->default(false);
            $table->boolean('show_invoice_scheme')->default(false);
            $table->boolean('show_invoice_layout_dropdown')->default(false);
            $table->boolean('print_invoice_on_suspend')->default(false);
            $table->boolean('show_pricing_on_product_suggestion_tooltip')->default(false);

            // Weighing Scale
            $table->string('weighing_scale_prefix', 50)->nullable();
            $table->integer('product_sku_length')->default(4);
            $table->integer('quantity_integer_part_length')->default(2);
            $table->integer('quantity_fractional_part_length')->default(2);

            // Purchases
            $table->boolean('enable_editing_product_price_from_purchase_screen')->default(false);
            $table->boolean('enable_purchase_status')->default(false);
            $table->boolean('enable_lot_number')->default(false);
            $table->boolean('enable_purchase_order')->default(false);
            $table->boolean('enable_purchase_requisition')->default(false);

            // Payment
            $table->text('cash_denominations')->nullable();
            $table->string('enable_cash_denomination_on', 50)->default('POS Screen');
            $table->string('enable_cash_denomination_for_payment_methods')->nullable();
            $table->boolean('strict_check')->default(false);

            // Prefixes
            $table->string('prefix_purchase', 50)->nullable();
            $table->string('prefix_purchase_return', 50)->nullable();
            $table->string('prefix_purchase_requisition', 50)->nullable();
            $table->string('prefix_purchase_order', 50)->nullable();
            $table->string('prefix_stock_transfer', 50)->nullable();
            $table->string('prefix_stock_adjustment', 50)->nullable();
            $table->string('prefix_sell_return', 50)->nullable();
            $table->string('prefix_expenses', 50)->nullable();
            $table->string('prefix_contacts', 50)->nullable();
            $table->string('prefix_purchase_payment', 50)->nullable();
            $table->string('prefix_sell_payment', 50)->nullable();
            $table->string('prefix_expense_payment', 50)->nullable();
            $table->string('prefix_business_location', 50)->nullable();
            $table->string('prefix_username', 50)->nullable();
            $table->string('prefix_subscription_no', 50)->nullable();
            $table->string('prefix_draft', 50)->nullable();
            $table->string('prefix_sales_order', 50)->nullable();

            // Email Settings
            $table->string('mail_driver')->default('smtp');
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->default('tls');
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            //
        });
    }
};
