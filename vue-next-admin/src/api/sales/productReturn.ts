import request from '/@/utils/request';

export interface SalesProductReturnCandidateOutbound {
	outbound_id: number;
	outbound_no: string;
	customer_name: string;
	contract_no: string;
	ship_date: string;
}

export interface SalesProductReturnBootstrapData {
	candidate_outbounds: SalesProductReturnCandidateOutbound[];
}

export interface SalesProductReturnListParams {
	customer_name?: string;
	product_name?: string;
	product_spec?: string;
	actual_stockin_date?: string;
	remark?: string;
	audit_state?: number | '';
	page?: number;
	page_size?: number;
}

export interface SalesProductReturnListItem {
	id: number;
	return_id: number;
	create_time: string;
	return_no: string;
	customer_name: string;
	return_type: number;
	return_type_text: string;
	product_name: string;
	product_spec: string;
	warehouse_name: string;
	quantity: string;
	unit_name: string;
	price: string;
	amount: string;
	total_amount: string;
	audit_state: number;
	audit_state_text: string;
	maker_user_name: string;
}

export interface SalesProductReturnDetailHeader {
	return_id: number;
	return_no: string;
	related_outbound_id: number;
	related_outbound_no: string;
	customer_id: number;
	customer_name: string;
	return_type: number;
	return_type_text: string;
	actual_stockin_date: string;
	total_quantity: string;
	total_amount: string;
	audit_state: number;
	audit_state_text: string;
	maker_user_name: string;
	audit_user_name: string;
	audit_time: string;
	remark: string;
}

export interface SalesProductReturnDetailItem {
	return_item_id: number;
	sales_order_item_id: number;
	product_code: string;
	product_name: string;
	product_spec: string;
	warehouse_name: string;
	unit_name: string;
	quantity: string;
	price: string;
	amount: string;
	remark: string;
}

export interface SalesProductReturnDetailData {
	header: SalesProductReturnDetailHeader;
	items: SalesProductReturnDetailItem[];
}

export interface SalesProductReturnSavePayload {
	return_id?: number;
	related_outbound_id: number;
	return_type: number;
	actual_stockin_date?: string;
	remark?: string;
	items: Array<{
		return_item_id?: number;
		sales_order_item_id: number;
		quantity: number | string;
		price?: number | string;
		remark?: string;
	}>;
}

export function useSalesProductReturnApi() {
	return {
		getBootstrap: () =>
			request({
				url: '/sales/product-return/bootstrap',
				method: 'post',
				data: {},
			}),
		getList: (data: SalesProductReturnListParams) =>
			request({
				url: '/sales/product-return/list',
				method: 'post',
				data,
			}),
		getDetail: (return_id: number) =>
			request({
				url: '/sales/product-return/detail',
				method: 'post',
				data: { return_id },
			}),
		save: (data: SalesProductReturnSavePayload) =>
			request({
				url: '/sales/product-return/save',
				method: 'post',
				data,
			}),
		auditPass: (return_id: number) =>
			request({
				url: '/sales/product-return/audit-pass',
				method: 'post',
				data: { return_id },
			}),
		reverseAudit: (return_id: number) =>
			request({
				url: '/sales/product-return/reverse-audit',
				method: 'post',
				data: { return_id },
			}),
		batchDelete: (return_ids: number[]) =>
			request({
				url: '/sales/product-return/batch-delete',
				method: 'post',
				data: { return_ids },
			}),
	};
}
