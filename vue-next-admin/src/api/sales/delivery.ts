import request from '/@/utils/request';

export interface SalesDeliveryCandidateOrder {
	sales_order_id: number;
	contract_no: string;
	customer_name: string;
	order_date: string;
	delivery_date: string;
	invoice_required: number;
}

export interface SalesDeliveryBootstrapData {
	candidate_orders: SalesDeliveryCandidateOrder[];
}

export interface SalesDeliveryListParams {
	outbound_no?: string;
	customer_name?: string;
	product_name?: string;
	product_spec?: string;
	ship_date?: string;
	invoice_required?: number | '';
	audit_state?: number | '';
	page?: number;
	page_size?: number;
}

export interface SalesDeliveryListItem {
	id: number;
	outbound_id: number;
	outbound_no: string;
	document_date: string;
	ship_date: string;
	customer_name: string;
	contract_no: string;
	product_code: string;
	product_name: string;
	product_spec: string;
	unit_name: string;
	outbound_quantity: string;
	invoice_required: number;
	invoice_required_text: string;
	audit_state: number;
	audit_state_text: string;
}

export interface SalesDeliveryDetailHeader {
	outbound_id: number;
	outbound_no: string;
	sales_order_id: number;
	contract_no: string;
	customer_id: number;
	customer_name: string;
	document_date: string;
	ship_date: string;
	order_date: string;
	audit_state: number;
	audit_state_text: string;
	invoice_required: number;
	invoice_required_text: string;
	total_quantity: string;
	total_amount: string;
	logistics_fee: string;
	express_no: string;
	driver_name: string;
	vehicle_no: string;
	receiver_name: string;
	receiver_phone: string;
	receiver_address: string;
	print_count: number;
	print_without_price_count: number;
	remark: string;
}

export interface SalesDeliveryDetailItem {
	outbound_item_id: number;
	sales_order_item_id: number;
	line_no: number;
	product_id: number;
	product_code: string;
	product_name: string;
	product_spec: string;
	warehouse_name: string;
	unit_name: string;
	outbound_quantity: string;
	price: string;
	tax_price: string;
	amount: string;
	tax_amount: string;
	remark: string;
}

export interface SalesDeliveryDetailData {
	header: SalesDeliveryDetailHeader;
	items: SalesDeliveryDetailItem[];
}

export interface SalesDeliverySavePayload {
	outbound_id?: number;
	sales_order_id: number;
	document_date: string;
	ship_date?: string;
	invoice_required: number;
	logistics_fee?: number | string;
	express_no?: string;
	driver_name?: string;
	vehicle_no?: string;
	receiver_name?: string;
	receiver_phone?: string;
	receiver_address?: string;
	remark?: string;
	items: Array<{
		outbound_item_id?: number;
		sales_order_item_id?: number;
		product_id?: number;
		outbound_quantity: number | string;
		price?: number | string;
		tax_price?: number | string;
		remark?: string;
	}>;
}

export function useSalesDeliveryApi() {
	return {
		getBootstrap: () =>
			request({
				url: '/sales/delivery/bootstrap',
				method: 'post',
				data: {},
			}),
		getList: (data: SalesDeliveryListParams) =>
			request({
				url: '/sales/delivery/list',
				method: 'post',
				data,
			}),
		getDetail: (outbound_id: number) =>
			request({
				url: '/sales/delivery/detail',
				method: 'post',
				data: { outbound_id },
			}),
		save: (data: SalesDeliverySavePayload) =>
			request({
				url: '/sales/delivery/save',
				method: 'post',
				data,
			}),
		auditPass: (outbound_id: number) =>
			request({
				url: '/sales/delivery/audit-pass',
				method: 'post',
				data: { outbound_id },
			}),
		reverseAudit: (outbound_id: number) =>
			request({
				url: '/sales/delivery/reverse-audit',
				method: 'post',
				data: { outbound_id },
			}),
		batchDelete: (outbound_ids: number[]) =>
			request({
				url: '/sales/delivery/batch-delete',
				method: 'post',
				data: { outbound_ids },
			}),
		print: (outbound_id: number, without_price = 0) =>
			request({
				url: '/sales/delivery/print',
				method: 'post',
				data: { outbound_id, without_price },
			}),
	};
}
