<template>
	<div class="sales-duplicate-page layout-padding">
		<section class="duplicate-header">
			<div class="duplicate-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>可翻单列表</span>
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
				<el-form-item label="添加时间">
					<el-date-picker v-model="filterForm.createDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
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
			<el-table :data="tableRows" v-loading="tableLoading" class="duplicate-table">
				<el-table-column label="操作" width="92" fixed="left">
					<template #default="{ row }">
						<el-button type="success" size="small" class="duplicate-btn" @click="onDuplicate(row)">翻单</el-button>
					</template>
				</el-table-column>
				<el-table-column prop="id" label="ID" width="80" />
				<el-table-column prop="contract_no" label="合同编号" min-width="182" />
				<el-table-column prop="customer_name" label="客户名称" min-width="240" show-overflow-tooltip />
				<el-table-column prop="order_date" label="订单日期" width="132" />
				<el-table-column prop="delivery_date" label="交货日期" width="132">
					<template #default="{ row }">
						<span class="delivery-date">{{ row.delivery_date || '-' }}</span>
					</template>
				</el-table-column>
				<el-table-column prop="product_name" label="产品名称" min-width="290" show-overflow-tooltip />
				<el-table-column prop="product_spec" label="产品规格" width="120" show-overflow-tooltip />
				<el-table-column prop="product_quantity" label="产品数量" width="110" align="right" />
				<el-table-column prop="price" label="单价" width="90" align="right" />
				<el-table-column prop="sales_total_price" label="销售总价" width="110" align="right" />
				<el-table-column prop="logistics_fee" label="物流费用" width="106" align="right" />
				<el-table-column prop="discount_rate" label="折扣率" width="92" align="right" />
				<el-table-column prop="invoice_required_text" label="是否开票" width="106" />
				<el-table-column prop="customer_tax_no" label="税号" width="120" show-overflow-tooltip />
				<el-table-column prop="remark" label="备注" min-width="120" show-overflow-tooltip />
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

<script setup lang="ts" name="salesDuplicatePage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { useSalesDuplicateApi, type SalesDuplicateListItem } from '/@/api/sales/duplicate';

const salesDuplicateApi = useSalesDuplicateApi();

const filterForm = reactive({
	contractNo: '',
	customerName: '',
	createDate: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesDuplicateListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesDuplicateApi.getList({
			contract_no: filterForm.contractNo || undefined,
			customer_name: filterForm.customerName || undefined,
			create_date: filterForm.createDate || undefined,
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

const onDuplicate = async (row: SalesDuplicateListItem) => {
	await ElMessageBox.confirm(`确认根据销售单 ${row.contract_no} 生成翻单吗？`, '翻单确认', {
		type: 'warning',
	});
	const response = await salesDuplicateApi.createDuplicate(row.sales_order_id);
	ElMessage.success(`翻单成功：${response.data.contract_no}`);
	await loadList();
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('翻单模块初始化失败');
	}
});
</script>

<style scoped lang="scss">
.sales-duplicate-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.duplicate-header {
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
		:deep(.el-select__wrapper),
		:deep(.el-date-editor.el-input) {
			box-shadow: none;
			border: 1px solid #dfe5ee;
		}

		&__action {
			display: flex;
			align-items: flex-end;
		}
	}

	.duplicate-table {
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

	.duplicate-btn {
		min-width: 52px;
		height: 30px;
		margin-left: 0;
	}

	.delivery-date {
		color: #ff5757;
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

@media (max-width: 1280px) {
	.sales-duplicate-page {
		.filter-form {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
}

@media (max-width: 900px) {
	.sales-duplicate-page {
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
