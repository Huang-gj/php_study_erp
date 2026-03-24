<template>
	<div class="sales-order-report-page layout-padding">
		<section class="report-header">
			<div class="report-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>销售报表</span>
			</div>
			<el-button plain class="report-header__export" @click="onExportExcel">导出excel</el-button>
		</section>

		<el-card shadow="never" class="filter-card">
			<template #header>
				<div class="card-title">条件搜索</div>
			</template>
			<el-form :model="filterForm" class="filter-form" label-position="top">
				<el-form-item label="客户名称">
					<el-input v-model="filterForm.customerName" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="日期">
					<el-date-picker v-model="filterForm.statDate" type="date" placeholder="请选择" value-format="YYYY-MM-DD" />
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
			<div class="summary-row">
				<div class="summary-row__label">合集</div>
				<div class="summary-row__item">{{ summary.total_water_workshop_quantity }}</div>
				<div class="summary-row__item">{{ summary.total_oil_workshop_quantity }}</div>
				<div class="summary-row__item">{{ summary.total_other_quantity }}</div>
				<div class="summary-row__item">{{ summary.total_received_amount }}</div>
				<div class="summary-row__item">{{ summary.total_unpaid_amount }}</div>
			</div>

			<el-table :data="tableRows" v-loading="tableLoading" class="report-table" empty-text="没有记录哦">
				<el-table-column prop="stat_date" label="日期" width="130" />
				<el-table-column prop="customer_name" label="客户名称" min-width="240" show-overflow-tooltip />
				<el-table-column prop="water_workshop_quantity" label="水性车间数量" min-width="150" align="right" />
				<el-table-column prop="oil_workshop_quantity" label="油性车间数量" min-width="150" align="right" />
				<el-table-column prop="other_quantity" label="其他数量" min-width="130" align="right" />
				<el-table-column prop="received_amount" label="已收款金额" min-width="140" align="right" />
				<el-table-column prop="unpaid_amount" label="未收款金额" min-width="140" align="right" />
				<el-table-column prop="remark" label="备注" min-width="220" show-overflow-tooltip />
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
	</div>
</template>

<script setup lang="ts" name="salesOrderReportPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { exportExcel } from '/@/utils/exportExcel';
import { useSalesOrderReportApi, type SalesOrderReportListItem, type SalesOrderReportSummaryData } from '/@/api/sales/orderReport';

const salesOrderReportApi = useSalesOrderReportApi();

const filterForm = reactive({
	customerName: '',
	statDate: '2026-03-24',
});

const tableLoading = ref(false);
const tableRows = ref<SalesOrderReportListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const summary = reactive<SalesOrderReportSummaryData>({
	total_water_workshop_quantity: '0',
	total_oil_workshop_quantity: '0',
	total_other_quantity: '0',
	total_received_amount: '0.00',
	total_unpaid_amount: '0.00',
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesOrderReportApi.getList({
			customer_name: filterForm.customerName || undefined,
			stat_date: filterForm.statDate || undefined,
			page: pagination.page,
			page_size: pagination.pageSize,
		});
		tableRows.value = response.data.list ?? [];
		pagination.total = response.data.total ?? 0;
		pagination.page = response.data.page ?? pagination.page;
		pagination.pageSize = response.data.page_size ?? pagination.pageSize;
		Object.assign(summary, response.data.summary ?? {});
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

const onExportExcel = () => {
	exportExcel({
		filename: '销售报表',
		columns: [
			{ label: '日期', prop: 'stat_date' },
			{ label: '客户名称', prop: 'customer_name' },
			{ label: '水性车间数量', prop: 'water_workshop_quantity' },
			{ label: '油性车间数量', prop: 'oil_workshop_quantity' },
			{ label: '其他数量', prop: 'other_quantity' },
			{ label: '已收款金额', prop: 'received_amount' },
			{ label: '未收款金额', prop: 'unpaid_amount' },
			{ label: '备注', prop: 'remark' },
		],
		data: tableRows.value,
	});
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('销售报表数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-order-report-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.report-header {
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

		&__export {
			height: 36px;
			padding: 0 16px;
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
		padding: 0 0 12px;
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
		grid-template-columns: minmax(0, 320px) minmax(0, 280px) auto;
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

	.summary-row {
		display: grid;
		grid-template-columns: 1.2fr repeat(5, 1fr);
		border-bottom: 1px solid #ebeef5;
		background: #fff;

		&__label,
		&__item {
			display: flex;
			align-items: center;
			min-height: 52px;
			padding: 0 20px;
			border-right: 1px solid #ebeef5;
			font-size: 14px;
			color: #606266;
		}

		&__label {
			font-weight: 600;
		}

		&__item:last-child {
			border-right: none;
		}
	}

	.report-table {
		:deep(.el-table__cell) {
			padding: 14px 0;
		}
	}

	.table-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px 12px 0;

		&__total {
			font-size: 13px;
			color: #606266;
		}
	}
}

@media (max-width: 1200px) {
	.sales-order-report-page {
		.filter-form {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.summary-row {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
	}
}

@media (max-width: 768px) {
	.sales-order-report-page {
		.report-header {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}

		.filter-form {
			grid-template-columns: 1fr;
		}

		.summary-row {
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
