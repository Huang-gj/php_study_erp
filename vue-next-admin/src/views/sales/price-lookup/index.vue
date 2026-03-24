<template>
	<div class="sales-price-lookup-page layout-padding">
		<section class="page-header">
			<div class="page-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>价格速查</span>
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
				<el-form-item label="产品名称">
					<el-input v-model="filterForm.productName" placeholder="请输入" clearable />
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
			<el-table :data="tableRows" v-loading="tableLoading" class="lookup-table">
				<el-table-column label="产品名称" min-width="320" fixed="left">
					<template #default="{ row }">
						<div class="product-cell">
							<div class="product-cell__name">{{ row.product_name }}</div>
							<div v-if="row.product_spec && row.product_spec !== '--'" class="product-cell__spec">{{ row.product_spec }}</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column
					v-for="customer in customerColumns"
					:key="customer.customer_id"
					:label="customer.customer_name"
					min-width="110"
					align="center"
				>
					<template #default="{ row }">
						{{ row.prices[String(customer.customer_id)] || '--' }}
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

<script setup lang="ts" name="salesPriceLookupPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesPriceLookupApi, type SalesPriceLookupCustomerColumn, type SalesPriceLookupRow } from '/@/api/sales/priceLookup';

const salesPriceLookupApi = useSalesPriceLookupApi();

const filterForm = reactive({
	customerName: '',
	productName: '',
});

const tableLoading = ref(false);
const customerColumns = ref<SalesPriceLookupCustomerColumn[]>([]);
const tableRows = ref<SalesPriceLookupRow[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 20,
	total: 0,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesPriceLookupApi.getList({
			customer_name: filterForm.customerName || undefined,
			product_name: filterForm.productName || undefined,
			page: pagination.page,
			page_size: pagination.pageSize,
		});
		customerColumns.value = response.data.customers ?? [];
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
		ElMessage.error('价格速查数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-price-lookup-page {
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

		:deep(.el-input__wrapper) {
			min-height: 40px;
		}
	}

	.product-cell {
		display: flex;
		flex-direction: column;
		gap: 4px;

		&__name {
			color: #606266;
		}

		&__spec {
			font-size: 12px;
			color: #909399;
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
	.sales-price-lookup-page .filter-form {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 768px) {
	.sales-price-lookup-page {
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
