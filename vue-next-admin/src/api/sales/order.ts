import request from '/@/utils/request';

export interface SalesOrderOption {
	label: string;
	value: number;
}

export interface SalesOrderCustomerOption {
	customer_id: number;
	customer_name: string;
	tax_no: string;
	default_tax_rate: string;
	default_payment_method: number;
}

export interface SalesOrderProductOption {
	product_id: number;
	product_code: string;
	product_name: string;
	product_spec: string;
	unit_name: string;
	workshop_type: number;
	default_tax_rate: string;
	default_price: string;
	default_tax_price: string;
	current_stock_quantity: string;
}

export interface SalesOrderBootstrapData {
	customers: SalesOrderCustomerOption[];
	products: SalesOrderProductOption[];
	order_type_options: SalesOrderOption[];
	ship_state_options: SalesOrderOption[];
	payment_method_options: SalesOrderOption[];
}

export interface SalesOrderListParams {
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

export interface SalesOrderListItem {
	id: number;
	sales_order_id: number;
	contract_no: string;
	customer_name: string;
	order_date: string;
	delivery_date: string;
	order_type: number;
	order_type_text: string;
	audit_state: number;
	audit_status: string;
	order_state: number;
	order_state_text: string;
	ship_state: number;
	ship_state_text: string;
	product_code: string;
	product_name: string;
	product_spec: string;
	quantity: string;
	tax_price: string;
	price: string;
	total_tax_amount: string;
	expected_stock_quantity: string;
}

export interface SalesOrderListData {
	list: SalesOrderListItem[];
	total: number;
	page: number;
	page_size: number;
}

export interface SalesOrderDetailItem {
	sales_order_item_id: number;
	line_no: number;
	product_id: number;
	product_code: string;
	product_name: string;
	product_spec: string;
	unit_name: string;
	quantity: string;
	price: string;
	tax_price: string;
	amount: string;
	tax_amount: string;
	expected_stock_quantity: string;
	shipped_quantity: string;
	remark: string;
}

export interface SalesOrderProgressLog {
	progress_log_id: number;
	step_code: number;
	step_name: string;
	step_state: number;
	step_state_text: string;
	start_time: string;
	finish_time: string;
	operator_user_name: string;
	related_no: string;
	remark: string;
}

export interface SalesOrderDetailData {
	header: {
		sales_order_id: number;
		contract_no: string;
		customer_name: string;
		customer_tax_no: string;
		order_type: number;
		order_type_text: string;
		order_date: string;
		delivery_date: string;
		audit_state: number;
		audit_status: string;
		order_state: number;
		order_state_text: string;
		ship_state: number;
		ship_state_text: string;
		payment_method: number;
		payment_method_text: string;
		invoice_required: number;
		total_quantity: string;
		total_amount: string;
		total_tax_amount: string;
		logistics_fee: string;
		other_fee: string;
		remark: string;
	};
	items: SalesOrderDetailItem[];
	progress_logs: SalesOrderProgressLog[];
}

export interface SalesOrderCreateItemPayload {
	product_id: number | null;
	quantity: number | string;
	tax_price?: number | string;
	price?: number | string;
	expected_stock_quantity?: number | string;
	remark?: string;
}

export interface SalesOrderCreatePayload {
	contract_no?: string;
	customer_id: number | null;
	order_type: number;
	order_date: string;
	delivery_date?: string;
	payment_method?: number;
	invoice_required?: number;
	tax_rate?: number | string;
	logistics_fee?: number | string;
	other_fee?: number | string;
	remark?: string;
	items: SalesOrderCreateItemPayload[];
}

export function useSalesOrderApi() {
	return {
		getBootstrap: () => {
			return request({
				url: '/sales/order/bootstrap',
				method: 'post',
				data: {},
			});
		},
		getList: (data: SalesOrderListParams) => {
			return request({
				url: '/sales/order/list',
				method: 'post',
				data,
			});
		},
		getDetail: (sales_order_id: number) => {
			return request({
				url: '/sales/order/detail',
				method: 'post',
				data: { sales_order_id },
			});
		},
		create: (data: SalesOrderCreatePayload) => {
			return request({
				url: '/sales/order/create',
				method: 'post',
				data,
			});
		},
		auditPass: (sales_order_id: number) => {
			return request({
				url: '/sales/order/audit-pass',
				method: 'post',
				data: { sales_order_id },
			});
		},
		shipInvoice: (sales_order_id: number) => {
			return request({
				url: '/sales/order/ship-invoice',
				method: 'post',
				data: { sales_order_id },
			});
		},
	};
}
