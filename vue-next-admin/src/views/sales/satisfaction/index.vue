<template>
	<div class="sales-satisfaction-page layout-padding">
		<section class="page-header">
			<div class="page-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>客户反馈统计</span>
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
				<el-table-column prop="id" label="ID" width="90" />
				<el-table-column prop="customer_name" label="客户名称" min-width="260" show-overflow-tooltip />
				<el-table-column prop="complaint_count" label="投诉记录" width="120" align="center" />
				<el-table-column label="评价" min-width="520">
					<template #default="{ row }">
						<div class="score-list">
							<div v-for="item in scoreRows(row.scores)" :key="item.key" class="score-list__row">
								<span class="score-list__label">{{ item.label }}：</span>
								<el-rate :model-value="item.value" disabled text-color="#ffb400" />
								<span class="score-list__percent">{{ toPercent(item.value) }}</span>
							</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="总体评价" min-width="220">
					<template #default="{ row }">
						<div class="overall-score">
							<el-rate :model-value="row.overall_score" disabled allow-half text-color="#ffb400" />
							<span class="overall-score__percent">{{ toPercent(row.overall_score) }}</span>
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

<script setup lang="ts" name="salesSatisfactionPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesSatisfactionApi, type SalesSatisfactionListItem, type SalesSatisfactionScoreData } from '/@/api/sales/satisfaction';

const salesSatisfactionApi = useSalesSatisfactionApi();

const filterForm = reactive({
	customerName: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesSatisfactionListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesSatisfactionApi.getList({
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

const scoreRows = (scores: SalesSatisfactionScoreData) => [
	{ key: 'product_quality', label: '产品质量', value: scores.product_quality || 0 },
	{ key: 'delivery_response', label: '交付和响应时间', value: scores.delivery_response || 0 },
	{ key: 'pre_after_service', label: '售前与售后服务', value: scores.pre_after_service || 0 },
	{ key: 'price_performance', label: '价格和性价比', value: scores.price_performance || 0 },
	{ key: 'customization', label: '定制化能力', value: scores.customization || 0 },
	{ key: 'cooperation_relation', label: '合作关系', value: scores.cooperation_relation || 0 },
];

const toPercent = (score: number) => `${((score / 5) * 100).toFixed(2)}%`;

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('客户满意度数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-satisfaction-page {
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
		grid-template-columns: minmax(0, 320px) auto;
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
			width: 116px;
			flex-shrink: 0;
			color: #606266;
		}

		&__percent {
			color: #606266;
		}
	}

	.overall-score {
		display: flex;
		align-items: center;
		gap: 10px;

		&__percent {
			color: #606266;
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

@media (max-width: 768px) {
	.sales-satisfaction-page {
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
