import request from '/@/utils/request';

export interface SalesArrearsListParams {
	page?: number;
	page_size?: number;
}

export interface SalesArrearsListItem {
	rank_no: number;
	customer_id: number;
	customer_name: string;
}

export interface SalesArrearsDetailItem {
	sales_order_id: number;
	contract_no: string;
	order_date: string;
	delivery_date: string;
	total_tax_amount: string;
	received_amount: string;
	unpaid_amount: string;
}

export interface SalesArrearsDetailData {
	customer_id: number;
	customer_name: string;
	total_unpaid_amount: string;
	order_list: SalesArrearsDetailItem[];
}

export function useSalesArrearsApi() {
	return {
		getList: (data: SalesArrearsListParams) => {
			return request({
				url: '/sales/arrears/list',
				method: 'post',
				data,
			});
		},
		getDetail: (customer_id: number) => {
			return request({
				url: '/sales/arrears/detail',
				method: 'post',
				data: { customer_id },
			});
		},
	};
}
