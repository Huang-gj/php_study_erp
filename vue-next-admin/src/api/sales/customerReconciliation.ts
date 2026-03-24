import request from '/@/utils/request';

export interface SalesCustomerReconciliationListParams {
	customer_name?: string;
	date_range?: string[];
	page?: number;
	page_size?: number;
}

export interface SalesCustomerReconciliationListItem {
	id: number;
	sales_order_id: number;
	customer_name: string;
	total_tax_amount: string;
	discount_amount: string;
	logistics_fee: string;
	order_date: string;
	delivery_date: string;
	payment_method_text: string;
	invoice_required_text: string;
}

export interface SalesCustomerReconciliationListData {
	list: SalesCustomerReconciliationListItem[];
	total: number;
	page: number;
	page_size: number;
}

export function useSalesCustomerReconciliationApi() {
	return {
		getList: (data: SalesCustomerReconciliationListParams) => {
			return request({
				url: '/sales/customer-reconciliation/list',
				method: 'post',
				data,
			});
		},
	};
}
