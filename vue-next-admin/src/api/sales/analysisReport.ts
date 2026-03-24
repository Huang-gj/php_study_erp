import request from '/@/utils/request';

export interface SalesAnalysisReportListParams {
	customer_name?: string;
	date_range?: string[];
	page?: number;
	page_size?: number;
}

export interface SalesAnalysisReportListItem {
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

export interface SalesAnalysisReportListData {
	list: SalesAnalysisReportListItem[];
	total: number;
	page: number;
	page_size: number;
}

export interface SalesAnalysisReportDetailData {
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

export function useSalesAnalysisReportApi() {
	return {
		getList: (data: SalesAnalysisReportListParams) => {
			return request({
				url: '/sales/analysis-report/list',
				method: 'post',
				data,
			});
		},
		getDetail: (report_customer_day_id: number) => {
			return request({
				url: '/sales/analysis-report/detail',
				method: 'post',
				data: { report_customer_day_id },
			});
		},
	};
}
