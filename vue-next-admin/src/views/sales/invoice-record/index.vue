<template>
	<div class="sales-invoice-record-page layout-padding">
		<section class="record-header">
			<div class="record-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>发货开票记录</span>
			</div>
			<el-button plain class="record-header__create" @click="openCreateDialog">发货开票</el-button>
		</section>

		<el-card shadow="never" class="filter-card">
			<template #header>
				<div class="card-title">条件搜索</div>
			</template>
			<el-form :model="filterForm" class="filter-form" label-position="top">
				<el-form-item label="发票号">
					<el-input v-model="filterForm.invoiceNo" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="客户名称">
					<el-input v-model="filterForm.customerName" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="开票日期">
					<el-date-picker v-model="filterForm.invoiceDate" type="date" placeholder="请选择" value-format="YYYY-MM-DD" />
				</el-form-item>
				<el-form-item class="filter-form__action">
					<el-button type="primary" :loading="tableLoading" @click="onSearch">
						<el-icon><ele-Search /></el-icon>
						搜索
					</el-button>
				</el-form-item>
			</el-form>
		</el-card>

		<el-card shadow="never" class="table-card">
			<el-table :data="tableRows" v-loading="tableLoading" class="record-table">
				<el-table-column prop="id" label="ID" width="96" />
				<el-table-column prop="invoice_no" label="发票号" min-width="260" show-overflow-tooltip />
				<el-table-column prop="customer_name" label="客户名称" min-width="280" show-overflow-tooltip />
				<el-table-column prop="invoice_amount" label="开票金额" width="140" align="right" />
				<el-table-column prop="drawer_user_name" label="开单员" width="120" />
				<el-table-column prop="invoice_date" label="开票日期" width="140" />
				<el-table-column prop="create_time" label="创建日期" width="180" />
				<el-table-column label="操作" width="166" fixed="right">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button type="success" size="small" @click="openDetail(row)">查看</el-button>
							<el-button type="warning" size="small" plain @click="onReverseAudit(row)">反审核</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>

			<div class="table-footer">
				<div class="table-footer__total">总记录：{{ pagination.total }}</div>
				<el-pagination
					background
					layout="prev, pager, next"
					:page-size="pagination.pageSize"
					:total="pagination.total"
					:model-value="pagination.page"
					@current-change="onPageChange"
				/>
			</div>
		</el-card>

		<el-drawer v-model="detailDrawer.visible" size="42%" title="开票详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="1" border>
					<el-descriptions-item label="发票号">{{ detailDrawer.data.invoice_no }}</el-descriptions-item>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="购方税号">{{ detailDrawer.data.buyer_tax_no || '-' }}</el-descriptions-item>
					<el-descriptions-item label="发票类型">{{ detailDrawer.data.invoice_type_text }}</el-descriptions-item>
					<el-descriptions-item label="开票日期">{{ detailDrawer.data.invoice_date }}</el-descriptions-item>
					<el-descriptions-item label="未税金额">{{ detailDrawer.data.untaxed_amount }}</el-descriptions-item>
					<el-descriptions-item label="税额">{{ detailDrawer.data.tax_amount }}</el-descriptions-item>
					<el-descriptions-item label="开票金额">{{ detailDrawer.data.invoice_amount }}</el-descriptions-item>
					<el-descriptions-item label="开票人">{{ detailDrawer.data.drawer_user_name }}</el-descriptions-item>
					<el-descriptions-item label="审核状态">{{ detailDrawer.data.audit_state_text }}</el-descriptions-item>
					<el-descriptions-item label="审核人">{{ detailDrawer.data.audit_user_name || '-' }}</el-descriptions-item>
					<el-descriptions-item label="审核时间">{{ detailDrawer.data.audit_time || '-' }}</el-descriptions-item>
					<el-descriptions-item label="创建时间">{{ detailDrawer.data.create_time }}</el-descriptions-item>
					<el-descriptions-item label="备注">{{ detailDrawer.data.remark || '-' }}</el-descriptions-item>
				</el-descriptions>

				<el-table :data="detailDrawer.data.bind_list" class="bind-table">
					<el-table-column prop="contract_no" label="合同编号" min-width="150" />
					<el-table-column prop="outbound_no" label="出库单号" min-width="150" />
					<el-table-column prop="bind_amount" label="绑定金额" min-width="110" align="right" />
					<el-table-column prop="remark" label="备注" min-width="160" show-overflow-tooltip />
				</el-table>
			</div>
		</el-drawer>

		<el-dialog v-model="createDialog.visible" title="发货开票" width="760px" destroy-on-close>
			<div class="candidate-tip">请选择一张已审核且未完成开票的销售单执行发货开票。</div>
			<el-table :data="createDialog.candidates" v-loading="createDialog.loading" max-height="380">
				<el-table-column prop="contract_no" label="合同编号" min-width="160" />
				<el-table-column prop="customer_name" label="客户名称" min-width="220" show-overflow-tooltip />
				<el-table-column prop="order_date" label="订单日期" width="120" />
				<el-table-column prop="delivery_date" label="交货日期" width="120" />
				<el-table-column prop="total_tax_amount" label="订单金额" width="120" align="right" />
				<el-table-column prop="drawer_user_name" label="开单员" width="110" />
				<el-table-column label="操作" width="92" fixed="right">
					<template #default="{ row }">
						<el-button type="primary" size="small" :loading="createDialog.submittingId === row.sales_order_id" @click="submitCreate(row.sales_order_id)">
							执行
						</el-button>
					</template>
				</el-table-column>
			</el-table>
		</el-dialog>
	</div>
</template>

<script setup lang="ts" name="salesInvoiceRecordPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
	useSalesInvoiceRecordApi,
	type SalesInvoiceCandidateOrderItem,
	type SalesInvoiceRecordDetailData,
	type SalesInvoiceRecordListItem,
} from '/@/api/sales/invoiceRecord';

const salesInvoiceRecordApi = useSalesInvoiceRecordApi();

const filterForm = reactive({
	invoiceNo: '',
	customerName: '',
	invoiceDate: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesInvoiceRecordListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesInvoiceRecordDetailData | null,
});

const createDialog = reactive({
	visible: false,
	loading: false,
	submittingId: 0,
	candidates: [] as SalesInvoiceCandidateOrderItem[],
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesInvoiceRecordApi.getList({
			invoice_no: filterForm.invoiceNo || undefined,
			customer_name: filterForm.customerName || undefined,
			invoice_date: filterForm.invoiceDate || undefined,
			page: pagination.page,
			page_size: pagination.pageSize,
		});
		tableRows.value = response.data.list ?? [];
		pagination.total = response.data.total ?? 0;
		pagination.page = response.data.page ?? pagination.page;
		pagination.pageSize = response.data.page_size ?? pagination.pageSize;
	} finally {
		tableLoading.value = false;
	}
};

const onSearch = () => {
	pagination.page = 1;
	loadList();
};

const onPageChange = (page: number) => {
	pagination.page = page;
	loadList();
};

const openDetail = async (row: SalesInvoiceRecordListItem) => {
	const response = await salesInvoiceRecordApi.getDetail(row.invoice_id);
	detailDrawer.data = response.data as SalesInvoiceRecordDetailData;
	detailDrawer.visible = true;
};

const openCreateDialog = async () => {
	createDialog.visible = true;
	createDialog.loading = true;
	try {
		const response = await salesInvoiceRecordApi.getBootstrap();
		createDialog.candidates = response.data.candidate_orders ?? [];
	} finally {
		createDialog.loading = false;
	}
};

const submitCreate = async (salesOrderId: number) => {
	createDialog.submittingId = salesOrderId;
	try {
		await salesInvoiceRecordApi.create(salesOrderId);
		ElMessage.success('发货开票完成');
		createDialog.visible = false;
		await loadList();
	} finally {
		createDialog.submittingId = 0;
	}
};

const onReverseAudit = async (row: SalesInvoiceRecordListItem) => {
	await ElMessageBox.confirm(`确认对发票 ${row.invoice_no} 执行反审核吗？`, '反审核', {
		type: 'warning',
	});
	await salesInvoiceRecordApi.reverseAudit(row.invoice_id);
	ElMessage.success('反审核完成');
	await loadList();
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('开票记录数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-invoice-record-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.record-header {
		display: flex;
		align-items: center;
		justify-content: space-between;

		&__title {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 17px;
			font-weight: 600;
			color: #4a4f5c;
		}

		&__create {
			height: 40px;
			padding: 0 18px;
			border-color: #d8dee9;
			color: #606a78;
			font-size: 16px;
		}
	}

	.filter-card,
	.table-card {
		border: 1px solid #e7ebf1;
		box-shadow: none;
	}

	:deep(.filter-card .el-card__header) {
		padding: 0;
		border-bottom: none;
	}

	:deep(.filter-card .el-card__body) {
		padding: 16px;
	}

	:deep(.table-card .el-card__body) {
		padding: 0 10px 12px;
	}

	.card-title {
		display: inline-flex;
		align-items: center;
		height: 34px;
		padding: 0 16px;
		background: #334761;
		color: #fff;
		font-size: 14px;
		font-weight: 600;
	}

	.filter-form {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 320px)) auto;
		gap: 10px 14px;
		align-items: end;

		:deep(.el-form-item) {
			margin-bottom: 0;
		}

		:deep(.el-form-item__label) {
			padding-bottom: 4px;
			font-size: 13px;
			color: #6d7584;
		}

		:deep(.el-input__wrapper),
		:deep(.el-date-editor.el-input) {
			height: 40px;
		}
	}

	.table-actions {
		display: flex;
		align-items: center;
		gap: 8px;
		white-space: nowrap;

		:deep(.el-button) {
			min-width: 56px;
			padding-inline: 12px;
		}
	}

	.table-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding-top: 16px;

		&__total {
			font-size: 13px;
			color: #606266;
		}
	}

	.detail-panel {
		display: flex;
		flex-direction: column;
		gap: 16px;
	}

	.bind-table {
		border-top: 1px solid #ebeef5;
		padding-top: 4px;
	}

	.candidate-tip {
		margin-bottom: 12px;
		color: #606266;
		font-size: 13px;
	}
}

@media (max-width: 1200px) {
	.sales-invoice-record-page {
		.filter-form {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
}

@media (max-width: 768px) {
	.sales-invoice-record-page {
		.record-header {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}

		.filter-form {
			grid-template-columns: 1fr;
		}

		.table-footer {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}
	}
}
</style>
