<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');

Route::group('admin', function () {
    Route::post('login', 'admin.Auth/login');
});

Route::group('sales/report', function () {
    Route::post('summary', 'sales.Report/summary');
    Route::post('order-list', 'sales.Report/orderList');
});

Route::group('sales/order', function () {
    Route::post('bootstrap', 'sales.Order/bootstrap');
    Route::post('list', 'sales.Order/list');
    Route::post('detail', 'sales.Order/detail');
    Route::post('create', 'sales.Order/create');
    Route::post('audit-pass', 'sales.Order/auditPass');
    Route::post('ship-invoice', 'sales.Order/shipInvoice');
});

Route::group('sales/business', function () {
    Route::post('bootstrap', 'sales.Business/bootstrap');
    Route::post('list', 'sales.Business/list');
    Route::post('detail', 'sales.Business/detail');
    Route::post('create', 'sales.Business/create');
    Route::post('batch-delete', 'sales.Business/batchDelete');
    Route::post('generate-sales-order', 'sales.Business/generateSalesOrder');
});

Route::group('sales/progress', function () {
    Route::post('list', 'sales.Progress/list');
    Route::post('detail', 'sales.Progress/detail');
});

Route::group('sales/duplicate', function () {
    Route::post('list', 'sales.DuplicateOrder/list');
    Route::post('create', 'sales.DuplicateOrder/create');
});

Route::group('sales/order-analysis', function () {
    Route::post('list', 'sales.OrderAnalysis/list');
    Route::post('detail', 'sales.OrderAnalysis/detail');
});

Route::group('sales/customer-analysis', function () {
    Route::post('list', 'sales.CustomerAnalysis/list');
    Route::post('detail', 'sales.CustomerAnalysis/detail');
});

Route::group('sales/product-list', function () {
    Route::post('list', 'sales.ProductList/list');
    Route::post('detail', 'sales.ProductList/detail');
});

Route::group('sales/analysis-report', function () {
    Route::post('list', 'sales.AnalysisReport/list');
    Route::post('detail', 'sales.AnalysisReport/detail');
});

Route::group('sales/order-report', function () {
    Route::post('list', 'sales.OrderReport/list');
});

Route::group('sales/invoice-record', function () {
    Route::post('bootstrap', 'sales.InvoiceRecord/bootstrap');
    Route::post('list', 'sales.InvoiceRecord/list');
    Route::post('detail', 'sales.InvoiceRecord/detail');
    Route::post('create', 'sales.InvoiceRecord/create');
    Route::post('reverse-audit', 'sales.InvoiceRecord/reverseAudit');
});

Route::group('sales/customer-balance', function () {
    Route::post('list', 'sales.CustomerBalance/list');
});

Route::group('sales/customer-reconciliation', function () {
    Route::post('list', 'sales.CustomerReconciliation/list');
});

Route::group('sales/arrears', function () {
    Route::post('list', 'sales.Arrears/list');
    Route::post('detail', 'sales.Arrears/detail');
});

Route::group('sales/price-lookup', function () {
    Route::post('list', 'sales.PriceLookup/list');
});

Route::group('sales/delivery', function () {
    Route::post('bootstrap', 'sales.Delivery/bootstrap');
    Route::post('list', 'sales.Delivery/list');
    Route::post('detail', 'sales.Delivery/detail');
    Route::post('save', 'sales.Delivery/save');
    Route::post('audit-pass', 'sales.Delivery/auditPass');
    Route::post('reverse-audit', 'sales.Delivery/reverseAudit');
    Route::post('batch-delete', 'sales.Delivery/batchDelete');
    Route::post('print', 'sales.Delivery/print');
});

Route::group('sales/product-return', function () {
    Route::post('bootstrap', 'sales.ProductReturn/bootstrap');
    Route::post('list', 'sales.ProductReturn/list');
    Route::post('detail', 'sales.ProductReturn/detail');
    Route::post('save', 'sales.ProductReturn/save');
    Route::post('audit-pass', 'sales.ProductReturn/auditPass');
    Route::post('reverse-audit', 'sales.ProductReturn/reverseAudit');
    Route::post('batch-delete', 'sales.ProductReturn/batchDelete');
});

Route::group('sales/freight-report', function () {
    Route::post('list', 'sales.FreightReport/list');
    Route::post('update', 'sales.FreightReport/update');
});

Route::group('sales/complaint', function () {
    Route::post('list', 'sales.Complaint/list');
});

Route::group('sales/satisfaction', function () {
    Route::post('list', 'sales.Satisfaction/list');
});
