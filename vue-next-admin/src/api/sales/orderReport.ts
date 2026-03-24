import request from '/@/utils/request';

export interface SalesOrderReportListParams {
	customer_name?: string;
	stat_date?: string;
	page?: number;
	page_size?: number;
}

export interface SalesOrderReportListItem {
	id: number;
	report_customer_day_id: number;
	stat_date: string;
	customer_id: number;
	customer_name: string;
	water_workshop_quantity: string;
	oil_workshop_quantity: string;
	other_quantity: string;
	received_amount: string;
	unpaid_amount: string;
	remark: string;
}

export interface SalesOrderReportSummaryData {
	total_water_workshop_quantity: string;
	total_oil_workshop_quantity: string;
	total_other_quantity: string;
	total_received_amount: string;
	total_unpaid_amount: string;
}

export interface SalesOrderReportListData {
	list: SalesOrderReportListItem[];
	total: number;
	page: number;
	page_size: number;
	summary: SalesOrderReportSummaryData;
}

export function useSalesOrderReportApi() {
	return {
		getList: (data: SalesOrderReportListParams) => {
			return request({
				url: '/sales/order-report/list',
				method: 'post',
				data,
			});
		},
	};
}
