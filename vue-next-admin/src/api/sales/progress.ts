import request from '/@/utils/request';

export interface SalesProgressListParams {
	contract_no?: string;
	customer_name?: string;
	product_name?: string;
	product_spec?: string;
	order_date?: string;
	page?: number;
	page_size?: number;
}

export interface SalesProgressStepItem {
	step_code: number;
	step_name: string;
	step_label: string;
	step_state: number;
}

export interface SalesProgressListItem {
	id: number;
	sales_order_id: number;
	contract_no: string;
	customer_name: string;
	order_date: string;
	delivery_date: string;
	sales_total_price: string;
	drawer_user_name: string;
	remark: string;
	audit_state: number;
	audit_status: string;
	order_state: number;
	order_state_text: string;
	reconcile_state: number;
	reconcile_state_text: string;
	current_step: number;
	steps: SalesProgressStepItem[];
}

export interface SalesProgressListData {
	list: SalesProgressListItem[];
	total: number;
	page: number;
	page_size: number;
}

export interface SalesProgressDetailLog {
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

export interface SalesProgressDetailData {
	header: {
		sales_order_id: number;
		contract_no: string;
		customer_name: string;
		order_date: string;
		delivery_date: string;
		sales_total_price: string;
		drawer_user_name: string;
		audit_status: string;
		order_state_text: string;
		reconcile_state_text: string;
		remark: string;
	};
	logs: SalesProgressDetailLog[];
}

export function useSalesProgressApi() {
	return {
		getList: (data: SalesProgressListParams) => {
			return request({
				url: '/sales/progress/list',
				method: 'post',
				data,
			});
		},
		getDetail: (sales_order_id: number) => {
			return request({
				url: '/sales/progress/detail',
				method: 'post',
				data: { sales_order_id },
			});
		},
	};
}
