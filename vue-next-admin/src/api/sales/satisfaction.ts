import request from '/@/utils/request';

export interface SalesSatisfactionListParams {
	customer_name?: string;
	page?: number;
	page_size?: number;
}

export interface SalesSatisfactionScoreData {
	product_quality: number;
	delivery_response: number;
	pre_after_service: number;
	price_performance: number;
	customization: number;
	cooperation_relation: number;
}

export interface SalesSatisfactionListItem {
	id: number;
	customer_id: number;
	customer_name: string;
	complaint_count: number;
	scores: SalesSatisfactionScoreData;
	overall_score: number;
}

export function useSalesSatisfactionApi() {
	return {
		getList: (data: SalesSatisfactionListParams) =>
			request({
				url: '/sales/satisfaction/list',
				method: 'post',
				data,
			}),
	};
}
