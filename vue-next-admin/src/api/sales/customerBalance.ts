import request from '/@/utils/request';

export interface SalesCustomerBalanceListParams {
	customer_name?: string;
	date_range?: string[];
	page?: number;
	page_size?: number;
}

export interface SalesCustomerBalanceListItem {
	id: number;
	customer_id: number;
	customer_name: string;
	order_total_amount: string;
	receipt_total_amount: string;
	order_unpaid_amount: string;
	current_ship_total: number;
}

export interface SalesCustomerBalanceSummary {
	order_total_amount: string;
	receipt_total_amount: string;
	order_unpaid_amount: string;
	current_ship_total: number;
}

export interface SalesCustomerBalanceListData {
	list: SalesCustomerBalanceListItem[];
	total: number;
	page: number;
	page_size: number;
	summary: SalesCustomerBalanceSummary;
}

export function useSalesCustomerBalanceApi() {
	return {
		getList: (data: SalesCustomerBalanceListParams) => {
			return request({
				url: '/sales/customer-balance/list',
				method: 'post',
				data,
			});
		},
	};
}
