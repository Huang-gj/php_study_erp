import request from '/@/utils/request';

export interface SalesPriceLookupListParams {
	customer_name?: string;
	product_name?: string;
	page?: number;
	page_size?: number;
}

export interface SalesPriceLookupCustomerColumn {
	customer_id: number;
	customer_name: string;
}

export interface SalesPriceLookupRow {
	product_id: number;
	product_name: string;
	product_spec: string;
	prices: Record<string, string>;
}

export interface SalesPriceLookupListData {
	customers: SalesPriceLookupCustomerColumn[];
	list: SalesPriceLookupRow[];
	total: number;
	page: number;
	page_size: number;
}

export function useSalesPriceLookupApi() {
	return {
		getList: (data: SalesPriceLookupListParams) => {
			return request({
				url: '/sales/price-lookup/list',
				method: 'post',
				data,
			});
		},
	};
}
