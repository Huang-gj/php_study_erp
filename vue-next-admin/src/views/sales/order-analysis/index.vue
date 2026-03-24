<template>
	<div class="sales-order-analysis-page layout-padding">
		<section class="analysis-header">
			<div class="analysis-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>订单分析列表</span>
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
				<el-table-column prop="month_text" label="月份" min-width="220" />
				<el-table-column prop="total_product_quantity" label="累计产品数量" min-width="190" align="right" />
				<el-table-column prop="total_ship_quantity" label="累计发货数量" min-width="190" align="right" />
				<el-table-column prop="total_order_count" label="累计订单量" min-width="170" align="right" />
				<el-table-column prop="total_amount" label="总金额" min-width="170" align="right" />
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

		<el-drawer v-model="detailDrawer.visible" size="40%" title="订单分析详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="1" border>
					<el-descriptions-item label="月份">{{ detailDrawer.data.month_text }}</el-descriptions-item>
					<el-descriptions-item label="累计产品数量">{{ detailDrawer.data.total_product_quantity }}</el-descriptions-item>
					<el-descriptions-item label="累计发货数量">{{ detailDrawer.data.total_ship_quantity }}</el-descriptions-item>
					<el-descriptions-item label="累计订单量">{{ detailDrawer.data.total_order_count }}</el-descriptions-item>
					<el-descriptions-item label="总金额">{{ detailDrawer.data.total_amount }}</el-descriptions-item>
					<el-descriptions-item label="备注">{{ detailDrawer.data.remark || '-' }}</el-descriptions-item>
				</el-descriptions>
			</div>
		</el-drawer>
	</div>
</template>

<script setup lang="ts" name="salesOrderAnalysisPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesOrderAnalysisApi, type SalesOrderAnalysisDetailData, type SalesOrderAnalysisListItem } from '/@/api/sales/orderAnalysis';
import { exportExcel } from '/@/utils/exportExcel';

const salesOrderAnalysisApi = useSalesOrderAnalysisApi();

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
});

const tableLoading = ref(false);
const tableRows = ref<SalesOrderAnalysisListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesOrderAnalysisDetailData | null,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesOrderAnalysisApi.getList({
			stat_year: filterForm.statYear === '' ? undefined : filterForm.statYear,
			stat_month: filterForm.statMonth === '' ? undefined : filterForm.statMonth,
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

const openDetail = async (row: SalesOrderAnalysisListItem) => {
	const response = await salesOrderAnalysisApi.getDetail(row.report_order_month_id);
	detailDrawer.data = response.data as SalesOrderAnalysisDetailData;
	detailDrawer.visible = true;
};

const onExportExcel = () => {
	exportExcel({
		filename: '订单分析表',
		columns: [
			{ label: '月份', prop: 'month_text' },
			{ label: '累计产品数量', prop: 'total_product_quantity' },
			{ label: '累计发货数量', prop: 'total_ship_quantity' },
			{ label: '累计订单量', prop: 'total_order_count' },
			{ label: '总金额', prop: 'total_amount' },
			{ label: '备注', prop: 'remark' },
		],
		data: tableRows.value,
	});
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('订单分析数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-order-analysis-page {
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
		grid-template-columns: repeat(2, minmax(0, 290px)) auto;
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
</style>
