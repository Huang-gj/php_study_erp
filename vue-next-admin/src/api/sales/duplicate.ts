import request from '/@/utils/request';

export interface SalesDuplicateListParams {
	contract_no?: string;
	customer_name?: string;
	create_date?: string;
	page?: number;
	page_size?: number;
}

export interface SalesDuplicateListItem {
	id: number;
	sales_order_id: number;
	contract_no: string;
	customer_name: string;
	order_date: string;
	delivery_date: string;
	product_name: string;
	product_spec: string;
	product_quantity: string;
	price: string;
	sales_total_price: string;
	logistics_fee: string;
	discount_rate: string;
	invoice_required: number;
	invoice_required_text: string;
	customer_tax_no: string;
	remark: string;
}

export interface SalesDuplicateListData {
	list: SalesDuplicateListItem[];
	total: number;
	page: number;
	page_size: number;
}

export function useSalesDuplicateApi() {
	return {
		getList: (data: SalesDuplicateListParams) => {
			return request({
				url: '/sales/duplicate/list',
				method: 'post',
				data,
			});
		},
		createDuplicate: (sales_order_id: number) => {
			return request({
				url: '/sales/duplicate/create',
				method: 'post',
				data: { sales_order_id },
			});
		},
	};
}
