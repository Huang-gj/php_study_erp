import request from '/@/utils/request';

export interface SalesProductListParams {
	contract_no?: string;
	customer_name?: string;
	product_name?: string;
	product_spec?: string;
	order_date?: string;
	order_type?: number | '';
	ship_state?: number | '';
	page?: number;
	page_size?: number;
}

export interface SalesProductListItem {
	id: number;
	sales_order_id: number;
	sales_order_item_id: number;
	contract_no: string;
	customer_name: string;
	order_date: string;
	product_name: string;
	product_spec: string;
	quantity: string;
	price: string;
	amount: string;
	remark: string;
	drawer_user_name: string;
	order_type: number;
	order_type_text: string;
	ship_state: number;
	ship_state_text: string;
}

export interface SalesProductListData {
	list: SalesProductListItem[];
	total: number;
	page: number;
	page_size: number;
}

export interface SalesProductListDetailData {
	header: {
		sales_order_id: number;
		sales_order_item_id: number;
		contract_no: string;
		customer_name: string;
		order_date: string;
		delivery_date: string;
		drawer_user_name: string;
		order_type_text: string;
		ship_state_text: string;
		remark: string;
	};
	item: {
		product_code: string;
		product_name: string;
		product_spec: string;
		unit_name: string;
		quantity: string;
		price: string;
		tax_price: string;
		amount: string;
		tax_amount: string;
	};
}

export function useSalesProductListApi() {
	return {
		getList: (data: SalesProductListParams) => {
			return request({
				url: '/sales/product-list/list',
				method: 'post',
				data,
			});
		},
		getDetail: (sales_order_item_id: number) => {
			return request({
				url: '/sales/product-list/detail',
				method: 'post',
				data: { sales_order_item_id },
			});
		},
	};
}
