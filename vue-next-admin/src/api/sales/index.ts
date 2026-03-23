import request from '/@/utils/request';

export interface SalesReportSummaryData {
	order_total: number;
	today_finished_total: number;
	production_total: number;
	completed_total: number;
}

export interface SalesOrderListParams {
	contract_no?: string;
	customer_name?: string;
	product_spec?: string;
	order_date?: string;
	page?: number;
	page_size?: number;
}

export interface SalesOrderReportItem {
	id: number;
	sales_order_id: number;
	audit_state: number;
	audit_status: string;
	contract_no: string;
	contract_text: string;
	customer_name: string;
	customer_info: string;
	order_date: string;
	delivery_date: string;
	product_name: string;
	specification: string;
	order_quantity: string;
	unit_price: string;
	ship_quantity: string;
	load_date: string;
}

export interface SalesOrderListData {
	list: SalesOrderReportItem[];
	total: number;
	page: number;
	page_size: number;
}

export function useSalesReportApi() {
	return {
		getSummary: () => {
			return request({
				url: '/sales/report/summary',
				method: 'post',
				data: {},
			});
		},
		getOrderList: (data: SalesOrderListParams) => {
			return request({
				url: '/sales/report/order-list',
				method: 'post',
				data,
			});
		},
	};
}
