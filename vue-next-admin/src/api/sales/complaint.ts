import request from '/@/utils/request';

export interface SalesComplaintListParams {
	contract_no?: string;
	customer_name?: string;
	page?: number;
	page_size?: number;
}

export interface SalesComplaintScoreData {
	product_quality: number;
	delivery_response: number;
	pre_after_service: number;
	price_performance: number;
	customization: number;
	cooperation_relation: number;
}

export interface SalesComplaintListItem {
	id: number;
	feedback_id: number;
	sales_order_id: number;
	contract_no: string;
	customer_name: string;
	complaint_count: number;
	scores: SalesComplaintScoreData;
	order_date: string;
	delivery_date: string;
	drawer_user_name: string;
	feedback_token: string;
}

export function useSalesComplaintApi() {
	return {
		getList: (data: SalesComplaintListParams) =>
			request({
				url: '/sales/complaint/list',
				method: 'post',
				data,
			}),
	};
}
