import request from '/@/utils/request';

export interface SalesBusinessOption {
	label: string;
	value: number;
}

export interface SalesBusinessCustomerOption {
	customer_id: number;
	customer_name: string;
	tax_no: string;
	default_tax_rate: string;
	default_payment_method: number;
}

export interface SalesBusinessProductOption {
	product_id: number;
	product_code: string;
	product_name: string;
	product_spec: string;
	unit_name: string;
	default_tax_rate: string;
	default_price: string;
	default_tax_price: string;
}

export interface SalesBusinessBootstrapData {
	customers: SalesBusinessCustomerOption[];
	products: SalesBusinessProductOption[];
	audit_state_options: SalesBusinessOption[];
	convert_state_options: SalesBusinessOption[];
}

export interface SalesBusinessListParams {
	customer_name?: string;
	order_date?: string;
	audit_state?: number | '';
	convert_state?: number | '';
	page?: number;
	page_size?: number;
}

export interface SalesBusinessListItem {
	id: number;
	business_order_id: number;
	business_order_no: string;
	customer_name: string;
	order_date: string;
	delivery_date: string;
	tax_rate: string;
	audit_state: number;
	audit_state_text: string;
	convert_state: number;
	convert_state_text: string;
	product_code: string;
	product_name: string;
	product_spec: string;
	unit_name: string;
	quantity: string;
	tax_price: string;
	tax_amount: string;
	maker_user_name: string;
	audit_user_name: string;
	create_time: string;
}

export interface SalesBusinessListData {
	list: SalesBusinessListItem[];
	total: number;
	page: number;
	page_size: number;
}

export interface SalesBusinessDetailItem {
	business_order_item_id: number;
	line_no: number;
	product_id: number;
	product_code: string;
	product_name: string;
	product_spec: string;
	unit_name: string;
	quantity: string;
	tax_rate: string;
	price: string;
	tax_price: string;
	amount: string;
	tax_amount: string;
	remark: string;
}

export interface SalesBusinessDetailData {
	header: {
		business_order_id: number;
		business_order_no: string;
		customer_id: number;
		customer_name: string;
		order_date: string;
		delivery_date: string;
		tax_rate: string;
		item_count: number;
		total_quantity: string;
		total_amount: string;
		total_tax_amount: string;
		audit_state: number;
		audit_state_text: string;
		convert_state: number;
		convert_state_text: string;
		maker_user_name: string;
		audit_user_name: string;
		create_time: string;
		audit_time: string;
		remark: string;
	};
	items: SalesBusinessDetailItem[];
}

export interface SalesBusinessCreateItemPayload {
	product_id: number | null;
	quantity: number | string;
	price?: number | string;
	tax_price?: number | string;
	remark?: string;
}

export interface SalesBusinessCreatePayload {
	business_order_no?: string;
	customer_id: number | null;
	order_date: string;
	delivery_date?: string;
	tax_rate?: number | string;
	remark?: string;
	items: SalesBusinessCreateItemPayload[];
}

export interface SalesBusinessOperatePayload {
	business_order_id: number;
}

export interface SalesBusinessBatchDeletePayload {
	business_order_ids: number[];
}

export function useSalesBusinessApi() {
	return {
		getBootstrap: () => {
			return request({
				url: '/sales/business/bootstrap',
				method: 'post',
				data: {},
			});
		},
		getList: (data: SalesBusinessListParams) => {
			return request({
				url: '/sales/business/list',
				method: 'post',
				data,
			});
		},
		getDetail: (business_order_id: number) => {
			return request({
				url: '/sales/business/detail',
				method: 'post',
				data: { business_order_id },
			});
		},
		create: (data: SalesBusinessCreatePayload) => {
			return request({
				url: '/sales/business/create',
				method: 'post',
				data,
			});
		},
		batchDelete: (data: SalesBusinessBatchDeletePayload) => {
			return request({
				url: '/sales/business/batch-delete',
				method: 'post',
				data,
			});
		},
		generateSalesOrder: (data: SalesBusinessOperatePayload) => {
			return request({
				url: '/sales/business/generate-sales-order',
				method: 'post',
				data,
			});
		},
	};
}
