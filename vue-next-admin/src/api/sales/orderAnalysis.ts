import request from '/@/utils/request';

export interface SalesOrderAnalysisListParams {
	stat_year?: number | '';
	stat_month?: number | '';
	page?: number;
	page_size?: number;
}

export interface SalesOrderAnalysisListItem {
	id: number;
	report_order_month_id: number;
	stat_year: number;
	stat_month: number;
	month_text: string;
	total_product_quantity: string;
	total_ship_quantity: string;
	total_order_count: number;
	total_amount: string;
	remark: string;
}

export interface SalesOrderAnalysisListData {
	list: SalesOrderAnalysisListItem[];
	total: number;
	page: number;
	page_size: number;
}

export interface SalesOrderAnalysisDetailData {
	report_order_month_id: number;
	stat_year: number;
	stat_month: number;
	month_text: string;
	total_product_quantity: string;
	total_ship_quantity: string;
	total_order_count: number;
	total_amount: string;
	remark: string;
}

export function useSalesOrderAnalysisApi() {
	return {
		getList: (data: SalesOrderAnalysisListParams) => {
			return request({
				url: '/sales/order-analysis/list',
				method: 'post',
				data,
			});
		},
		getDetail: (report_order_month_id: number) => {
			return request({
				url: '/sales/order-analysis/detail',
				method: 'post',
				data: { report_order_month_id },
			});
		},
	};
}
