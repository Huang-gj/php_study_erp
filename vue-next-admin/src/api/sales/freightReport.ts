import request from '/@/utils/request';

export interface SalesFreightReportListParams {
	outbound_no?: string;
	customer_name?: string;
	document_date?: string;
	page?: number;
	page_size?: number;
}

export interface SalesFreightReportListItem {
	id: number;
	outbound_id: number;
	customer_name: string;
	outbound_no: string;
	document_date: string;
	express_no: string;
	logistics_fee: string;
	driver_name: string;
	vehicle_no: string;
	ship_date: string;
	maker_user_name: string;
	create_time: string;
}

export interface SalesFreightReportUpdatePayload {
	outbound_id: number;
	express_no?: string;
	logistics_fee?: string;
	driver_name?: string;
	vehicle_no?: string;
}

export function useSalesFreightReportApi() {
	return {
		getList: (data: SalesFreightReportListParams) =>
			request({
				url: '/sales/freight-report/list',
				method: 'post',
				data,
			}),
		update: (data: SalesFreightReportUpdatePayload) =>
			request({
				url: '/sales/freight-report/update',
				method: 'post',
				data,
			}),
	};
}
