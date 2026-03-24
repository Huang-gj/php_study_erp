<template>
	<div class="sales-customer-analysis-page layout-padding">
		<section class="analysis-header">
			<div class="analysis-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>客户分析列表</span>
			</div>
			<el-button plain class="analysis-header__export" @click="onExportExcel">导出excel</el-button>
		</section>

		<el-card shadow="never" class="filter-card">
			<template #header>
				<div class="card-title">条件搜索</div>
			</template>
			<el-form :model="filterForm" class="filter-form" label-position="top">
				<el-form-item label="年份">
					<el-select v-model="filterForm.statYear" placeholder="请选择年份" clearable>
						<el-option v-for="item in yearOptions" :key="item" :label="`${item}`" :value="item" />
					</el-select>
				</el-form-item>
				<el-form-item label="月份">
					<el-select v-model="filterForm.statMonth" placeholder="请选择月份" clearable>
						<el-option v-for="item in monthOptions" :key="item.value" :label="item.label" :value="item.value" />
					</el-select>
				</el-form-item>
				<el-form-item label="客户名称">
					<el-input v-model="filterForm.customerName" placeholder="请输入" clearable />
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
				<el-table-column prop="id" label="序号" width="80" />
				<el-table-column prop="customer_name" label="客户名称" min-width="320" show-overflow-tooltip />
				<el-table-column prop="total_order_quantity" label="累计订单数量" min-width="170" align="right" />
				<el-table-column prop="total_ship_quantity" label="累计发货数量" min-width="170" align="right" />
				<el-table-column prop="total_order_count" label="累计订单量" min-width="150" align="right" />
				<el-table-column prop="total_amount" label="总金额" min-width="150" align="right" />
				<el-table-column prop="closing_debt_amount" label="结存欠款" min-width="110" align="right" />
				<el-table-column prop="current_debt_amount" label="当前欠款" min-width="110" align="right" />
				<el-table-column label="操作" width="150" fixed="right">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button type="primary" size="small" plain @click="showPrintTip(row)">打印</el-button>
							<el-button type="success" size="small" @click="openDetail(row)">查看</el-button>
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

		<el-drawer v-model="detailDrawer.visible" size="40%" title="客户分析详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="1" border>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="累计订单数量">{{ detailDrawer.data.total_order_quantity }}</el-descriptions-item>
					<el-descriptions-item label="累计发货数量">{{ detailDrawer.data.total_ship_quantity }}</el-descriptions-item>
					<el-descriptions-item label="累计订单量">{{ detailDrawer.data.total_order_count }}</el-descriptions-item>
					<el-descriptions-item label="总金额">{{ detailDrawer.data.total_amount }}</el-descriptions-item>
					<el-descriptions-item label="结存欠款">{{ detailDrawer.data.closing_debt_amount }}</el-descriptions-item>
					<el-descriptions-item label="当前欠款">{{ detailDrawer.data.current_debt_amount }}</el-descriptions-item>
					<el-descriptions-item label="备注">{{ detailDrawer.data.remark || '-' }}</el-descriptions-item>
				</el-descriptions>
			</div>
		</el-drawer>
	</div>
</template>

<script setup lang="ts" name="salesCustomerAnalysisPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { exportExcel } from '/@/utils/exportExcel';
import { useSalesCustomerAnalysisApi, type SalesCustomerAnalysisDetailData, type SalesCustomerAnalysisListItem } from '/@/api/sales/customerAnalysis';

const salesCustomerAnalysisApi = useSalesCustomerAnalysisApi();

const yearOptions = [2024, 2025, 2026, 2027];
const monthOptions = [
	{ label: '一月', value: 1 },
	{ label: '二月', value: 2 },
	{ label: '三月', value: 3 },
	{ label: '四月', value: 4 },
	{ label: '五月', value: 5 },
	{ label: '六月', value: 6 },
	{ label: '七月', value: 7 },
	{ label: '八月', value: 8 },
	{ label: '九月', value: 9 },
	{ label: '十月', value: 10 },
	{ label: '十一月', value: 11 },
	{ label: '十二月', value: 12 },
];

const filterForm = reactive({
	statYear: '' as number | '',
	statMonth: '' as number | '',
	customerName: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesCustomerAnalysisListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesCustomerAnalysisDetailData | null,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesCustomerAnalysisApi.getList({
			stat_year: filterForm.statYear === '' ? undefined : filterForm.statYear,
			stat_month: filterForm.statMonth === '' ? undefined : filterForm.statMonth,
			customer_name: filterForm.customerName || undefined,
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

const openDetail = async (row: SalesCustomerAnalysisListItem) => {
	const response = await salesCustomerAnalysisApi.getDetail(row.report_customer_month_id);
	detailDrawer.data = response.data as SalesCustomerAnalysisDetailData;
	detailDrawer.visible = true;
};

const onExportExcel = () => {
	exportExcel({
		filename: '客户分析表',
		columns: [
			{ label: '客户名称', prop: 'customer_name' },
			{ label: '累计订单数量', prop: 'total_order_quantity' },
			{ label: '累计发货数量', prop: 'total_ship_quantity' },
			{ label: '累计订单量', prop: 'total_order_count' },
			{ label: '总金额', prop: 'total_amount' },
			{ label: '结存欠款', prop: 'closing_debt_amount' },
			{ label: '当前欠款', prop: 'current_debt_amount' },
			{ label: '备注', prop: 'remark' },
		],
		data: tableRows.value,
	});
};

const showPrintTip = (row: SalesCustomerAnalysisListItem) => {
	ElMessage.success(`已触发打印预览：${row.customer_name}`);
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('客户分析数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-customer-analysis-page {
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
		grid-template-columns: repeat(3, minmax(0, 290px)) auto;
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
		:deep(.el-select__wrapper) {
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

	.table-actions {
		display: flex;
		flex-wrap: nowrap;
		align-items: center;
		gap: 8px;

		:deep(.el-button) {
			margin-left: 0;
			min-width: 52px;
			height: 30px;
			padding: 0 10px;
		}
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
</style>
