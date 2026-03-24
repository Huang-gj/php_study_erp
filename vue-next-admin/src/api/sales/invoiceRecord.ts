import request from '/@/utils/request';

export interface SalesInvoiceRecordListParams {
	invoice_no?: string;
	customer_name?: string;
	invoice_date?: string;
	page?: number;
	page_size?: number;
}

export interface SalesInvoiceRecordListItem {
	id: number;
	invoice_id: number;
	invoice_no: string;
	customer_name: string;
	invoice_amount: string;
	drawer_user_name: string;
	invoice_date: string;
	create_time: string;
	audit_state: number;
	audit_state_text: string;
}

export interface SalesInvoiceRecordDetailBindItem {
	invoice_bind_id: number;
	sales_order_id: number;
	contract_no: string;
	outbound_id: number;
	outbound_no: string;
	bind_amount: string;
	remark: string;
}

export interface SalesInvoiceRecordDetailData {
	invoice_id: number;
	invoice_no: string;
	customer_name: string;
	buyer_tax_no: string;
	invoice_type_text: string;
	invoice_date: string;
	untaxed_amount: string;
	tax_amount: string;
	invoice_amount: string;
	drawer_user_name: string;
	audit_state: number;
	audit_state_text: string;
	audit_user_name: string;
	audit_time: string;
	create_time: string;
	remark: string;
	bind_list: SalesInvoiceRecordDetailBindItem[];
}

export interface SalesInvoiceCandidateOrderItem {
	sales_order_id: number;
	contract_no: string;
	customer_name: string;
	order_date: string;
	delivery_date: string;
	total_tax_amount: string;
	drawer_user_name: string;
}

export function useSalesInvoiceRecordApi() {
	return {
		getList: (data: SalesInvoiceRecordListParams) => {
			return request({
				url: '/sales/invoice-record/list',
				method: 'post',
				data,
			});
		},
		getDetail: (invoice_id: number) => {
			return request({
				url: '/sales/invoice-record/detail',
				method: 'post',
				data: { invoice_id },
			});
		},
		getBootstrap: () => {
			return request({
				url: '/sales/invoice-record/bootstrap',
				method: 'post',
				data: {},
			});
		},
		create: (sales_order_id: number) => {
			return request({
				url: '/sales/invoice-record/create',
				method: 'post',
				data: { sales_order_id },
			});
		},
		reverseAudit: (invoice_id: number) => {
			return request({
				url: '/sales/invoice-record/reverse-audit',
				method: 'post',
				data: { invoice_id },
			});
		},
	};
}
