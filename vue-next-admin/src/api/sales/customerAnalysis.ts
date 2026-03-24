import request from '/@/utils/request';

export interface SalesCustomerAnalysisListParams {
	stat_year?: number | '';
	stat_month?: number | '';
	customer_name?: string;
	page?: number;
	page_size?: number;
}

export interface SalesCustomerAnalysisListItem {
	id: number;
	report_customer_month_id: number;
	stat_year: number;
	stat_month: number;
	customer_id: number;
	customer_name: string;
	total_order_quantity: string;
	total_ship_quantity: string;
	total_order_count: number;
	total_amount: string;
	closing_debt_amount: string;
	current_debt_amount: string;
	remark: string;
}

export interface SalesCustomerAnalysisListData {
	list: SalesCustomerAnalysisListItem[];
	total: number;
	page: number;
	page_size: number;
}

export interface SalesCustomerAnalysisDetailData {
	report_customer_month_id: number;
	stat_year: number;
	stat_month: number;
	customer_id: number;
	customer_name: string;
	total_order_quantity: string;
	total_ship_quantity: string;
	total_order_count: number;
	total_amount: string;
	closing_debt_amount: string;
	current_debt_amount: string;
	remark: string;
}

export function useSalesCustomerAnalysisApi() {
	return {
		getList: (data: SalesCustomerAnalysisListParams) => {
			return request({
				url: '/sales/customer-analysis/list',
				method: 'post',
				data,
			});
		},
		getDetail: (report_customer_month_id: number) => {
			return request({
				url: '/sales/customer-analysis/detail',
				method: 'post',
				data: { report_customer_month_id },
			});
		},
	};
}
