<template>
	<div class="sales-customer-reconciliation-page layout-padding">
		<section class="page-header">
			<div class="page-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>客户对账统计</span>
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
				<el-form-item label="订单时间">
					<el-date-picker
						v-model="filterForm.dateRange"
						type="daterange"
						range-separator="~"
						start-placeholder="请选择"
						end-placeholder="请选择"
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
			<el-table :data="tableRows" v-loading="tableLoading" class="reconciliation-table">
				<el-table-column prop="id" label="ID" width="90" />
				<el-table-column prop="customer_name" label="客户名称" min-width="260" show-overflow-tooltip />
				<el-table-column prop="total_tax_amount" label="总价" min-width="140" align="right" />
				<el-table-column prop="discount_amount" label="优惠金额" min-width="140" align="right" />
				<el-table-column prop="logistics_fee" label="物流费用" min-width="140" align="right" />
				<el-table-column prop="order_date" label="订单日期" width="130" />
				<el-table-column prop="delivery_date" label="交货日期" width="130" />
				<el-table-column prop="payment_method_text" label="收款方式" width="120" />
				<el-table-column prop="invoice_required_text" label="是否开票" width="110" />
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

<script setup lang="ts" name="salesCustomerReconciliationPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesCustomerReconciliationApi, type SalesCustomerReconciliationListItem } from '/@/api/sales/customerReconciliation';

const salesCustomerReconciliationApi = useSalesCustomerReconciliationApi();

const filterForm = reactive({
	customerName: '',
	dateRange: [] as string[],
});

const tableLoading = ref(false);
const tableRows = ref<SalesCustomerReconciliationListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesCustomerReconciliationApi.getList({
			customer_name: filterForm.customerName || undefined,
			date_range: filterForm.dateRange.length === 2 ? filterForm.dateRange : undefined,
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

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('客户对账统计数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-customer-reconciliation-page {
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

		:deep(.el-input__wrapper),
		:deep(.el-date-editor.el-input),
		:deep(.el-date-editor.el-range-editor) {
			min-height: 40px;
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
}

@media (max-width: 1200px) {
	.sales-customer-reconciliation-page .filter-form {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 768px) {
	.sales-customer-reconciliation-page {
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
