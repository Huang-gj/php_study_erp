<template>
	<div class="sales-analysis-report-page layout-padding">
		<section class="analysis-header">
			<div class="analysis-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>销售分析列表</span>
			</div>
			<div class="analysis-header__actions">
				<el-button plain class="analysis-header__export" @click="onExportExcel">导出excel</el-button>
				<el-button plain class="analysis-header__cost" @click="showUnitCostTip">填写单位成本</el-button>
			</div>
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
					<el-date-picker
						v-model="filterForm.dateRange"
						type="daterange"
						range-separator="~"
						start-placeholder="开始日期"
						end-placeholder="结束日期"
						value-format="YYYY-MM-DD"
					/>
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
			<el-table :data="tableRows" v-loading="tableLoading" class="analysis-table">
				<el-table-column prop="stat_date" label="日期" width="130" />
				<el-table-column prop="customer_name" label="客户名称" min-width="260" show-overflow-tooltip />
				<el-table-column prop="water_workshop_quantity" label="水性车间数量" min-width="160" align="right" />
				<el-table-column prop="oil_workshop_quantity" label="油性车间数量" min-width="160" align="right" />
				<el-table-column prop="other_quantity" label="其他数量" min-width="140" align="right" />
				<el-table-column prop="received_amount" label="已收款金额" min-width="140" align="right" />
				<el-table-column prop="unpaid_amount" label="未收款金额" min-width="140" align="right" />
				<el-table-column label="操作" width="92" fixed="right">
					<template #default="{ row }">
						<el-button type="success" size="small" class="view-btn" @click="openDetail(row)">查看</el-button>
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

		<el-drawer v-model="detailDrawer.visible" size="40%" title="销售分析详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="1" border>
					<el-descriptions-item label="日期">{{ detailDrawer.data.stat_date }}</el-descriptions-item>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="水性车间数量">{{ detailDrawer.data.water_workshop_quantity }}</el-descriptions-item>
					<el-descriptions-item label="油性车间数量">{{ detailDrawer.data.oil_workshop_quantity }}</el-descriptions-item>
					<el-descriptions-item label="其他数量">{{ detailDrawer.data.other_quantity }}</el-descriptions-item>
					<el-descriptions-item label="已收款金额">{{ detailDrawer.data.received_amount }}</el-descriptions-item>
					<el-descriptions-item label="未收款金额">{{ detailDrawer.data.unpaid_amount }}</el-descriptions-item>
					<el-descriptions-item label="备注">{{ detailDrawer.data.remark || '-' }}</el-descriptions-item>
				</el-descriptions>
			</div>
		</el-drawer>
	</div>
</template>

<script setup lang="ts" name="salesAnalysisReportPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { exportExcel } from '/@/utils/exportExcel';
import { useSalesAnalysisReportApi, type SalesAnalysisReportDetailData, type SalesAnalysisReportListItem } from '/@/api/sales/analysisReport';

const salesAnalysisReportApi = useSalesAnalysisReportApi();

const filterForm = reactive({
	customerName: '',
	dateRange: ['2026-02-01', '2026-02-28'] as string[],
});

const tableLoading = ref(false);
const tableRows = ref<SalesAnalysisReportListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesAnalysisReportDetailData | null,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesAnalysisReportApi.getList({
			customer_name: filterForm.customerName || undefined,
			date_range: filterForm.dateRange?.length === 2 ? filterForm.dateRange : undefined,
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

const openDetail = async (row: SalesAnalysisReportListItem) => {
	const response = await salesAnalysisReportApi.getDetail(row.report_customer_day_id);
	detailDrawer.data = response.data as SalesAnalysisReportDetailData;
	detailDrawer.visible = true;
};

const onExportExcel = () => {
	exportExcel({
		filename: '销售分析表',
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

const showUnitCostTip = () => {
	ElMessage.info('单位成本录入入口已预留，后续可继续接入成本维护功能');
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('销售分析数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-analysis-report-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.analysis-header {
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

		&__actions {
			display: flex;
			gap: 12px;
		}

		&__export,
		&__cost {
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
		grid-template-columns: minmax(0, 320px) minmax(0, 420px) auto;
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
		:deep(.el-date-editor.el-input__wrapper),
		:deep(.el-range-editor.el-input__wrapper) {
			box-shadow: none;
			border: 1px solid #dfe5ee;
		}

		&__action {
			display: flex;
			align-items: flex-end;
		}
	}

	.analysis-table {
		:deep(.el-table__inner-wrapper::before) {
			display: none;
		}

		:deep(.el-table__header th) {
			background: #f6f7f9;
			color: #576171;
			font-size: 14px;
			font-weight: 600;
		}

		:deep(.el-table__cell) {
			padding: 12px 0;
			color: #606978;
			font-size: 13px;
			vertical-align: middle;
		}
	}

	.view-btn {
		min-width: 52px;
		height: 30px;
		margin-left: 0;
	}

	.table-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding-top: 12px;

		&__total {
			font-size: 15px;
			color: #505766;
		}
	}
}

@media (max-width: 1200px) {
	.sales-analysis-report-page {
		.filter-form {
			grid-template-columns: 1fr;
		}

		.analysis-header {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}
	}
}
</style>
