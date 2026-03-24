<template>
	<div class="sales-complaint-page layout-padding">
		<section class="page-header">
			<div class="page-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>客户反馈列表</span>
			</div>
		</section>

		<el-card shadow="never" class="filter-card">
			<template #header>
				<div class="card-title">条件搜索</div>
			</template>
			<el-form :model="filterForm" class="filter-form" label-position="top">
				<el-form-item label="合同编号">
					<el-input v-model="filterForm.contractNo" placeholder="请输入" clearable />
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
			<el-table :data="tableRows" v-loading="tableLoading">
				<el-table-column type="selection" width="48" />
				<el-table-column prop="id" label="ID" width="90" />
				<el-table-column prop="contract_no" label="合同编号" min-width="180" />
				<el-table-column prop="customer_name" label="客户名称" min-width="220" show-overflow-tooltip />
				<el-table-column prop="complaint_count" label="投诉记录" width="120" align="center" />
				<el-table-column label="评价" min-width="420">
					<template #default="{ row }">
						<div class="score-list">
							<div v-for="item in scoreRows(row.scores)" :key="item.key" class="score-list__row">
								<span class="score-list__label">{{ item.label }}：</span>
								<el-rate :model-value="item.value" disabled text-color="#ff9900" />
							</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column prop="order_date" label="订单日期" width="130" />
				<el-table-column prop="delivery_date" label="交货日期" width="130" />
				<el-table-column prop="drawer_user_name" label="开单员" width="110" />
				<el-table-column label="操作" width="110" fixed="right">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button type="primary" size="small" @click="copyFeedbackLink(row)">复制链接</el-button>
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
	</div>
</template>

<script setup lang="ts" name="salesComplaintPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesComplaintApi, type SalesComplaintListItem, type SalesComplaintScoreData } from '/@/api/sales/complaint';

const salesComplaintApi = useSalesComplaintApi();

const filterForm = reactive({
	contractNo: '',
	customerName: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesComplaintListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesComplaintApi.getList({
			contract_no: filterForm.contractNo || undefined,
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

const scoreRows = (scores: SalesComplaintScoreData) => [
	{ key: 'product_quality', label: '产品质量', value: scores.product_quality || 0 },
	{ key: 'delivery_response', label: '交付和响应时间', value: scores.delivery_response || 0 },
	{ key: 'pre_after_service', label: '售前与售后服务', value: scores.pre_after_service || 0 },
	{ key: 'price_performance', label: '价格和性价比', value: scores.price_performance || 0 },
	{ key: 'customization', label: '定制化能力', value: scores.customization || 0 },
	{ key: 'cooperation_relation', label: '合作关系', value: scores.cooperation_relation || 0 },
];

const copyFeedbackLink = async (row: SalesComplaintListItem) => {
	const link = `${window.location.origin}/feedback/${row.feedback_token}`;
	try {
		await navigator.clipboard.writeText(link);
		ElMessage.success('反馈链接已复制');
	} catch (error) {
		ElMessage.error('复制失败，请检查浏览器权限');
	}
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('客户反馈数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-complaint-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.page-header__title {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 17px;
		font-weight: 600;
		color: #4a4f5c;
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
		grid-template-columns: minmax(0, 320px) minmax(0, 320px) auto;
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

		:deep(.el-input__wrapper) {
			height: 40px;
		}
	}

	.score-list {
		display: flex;
		flex-direction: column;
		gap: 4px;
		padding: 10px 0;

		&__row {
			display: flex;
			align-items: center;
			gap: 8px;
		}

		&__label {
			width: 112px;
			flex-shrink: 0;
			color: #606266;
		}
	}

	.table-actions {
		display: flex;
		align-items: center;
		justify-content: center;
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
	.sales-complaint-page .filter-form {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 768px) {
	.sales-complaint-page {
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
