<template>
	<div class="sales-product-list-page layout-padding">
		<section class="product-header">
			<div class="product-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>销售产品清单</span>
			</div>
			<el-button plain class="product-header__export" @click="onExportExcel">导出excel</el-button>
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
				<el-form-item label="产品名称">
					<el-input v-model="filterForm.productName" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="产品规格">
					<el-input v-model="filterForm.productSpec" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="订单日期">
					<el-date-picker v-model="filterForm.orderDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
				</el-form-item>
				<el-form-item label="订单类型">
					<el-select v-model="filterForm.orderType" placeholder="销售单" clearable>
						<el-option label="销售单" :value="1" />
						<el-option label="翻单" :value="2" />
					</el-select>
				</el-form-item>
				<el-form-item label="发货状态">
					<el-select v-model="filterForm.shipState" placeholder="全部" clearable>
						<el-option label="全部" :value="''" />
						<el-option label="未发货" :value="0" />
						<el-option label="部分发货" :value="1" />
						<el-option label="全部发货" :value="2" />
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
			<el-table :data="tableRows" v-loading="tableLoading" class="product-table">
				<el-table-column label="操作" width="92" fixed="left">
					<template #default="{ row }">
						<el-button type="success" size="small" class="view-btn" @click="openDetail(row)">查看</el-button>
					</template>
				</el-table-column>
				<el-table-column prop="id" label="ID" width="80" />
				<el-table-column prop="contract_no" label="合同编号" min-width="180" />
				<el-table-column prop="customer_name" label="客户名称" min-width="240" show-overflow-tooltip />
				<el-table-column prop="order_date" label="订单日期" width="132" />
				<el-table-column prop="product_name" label="产品名称" min-width="260" show-overflow-tooltip />
				<el-table-column prop="product_spec" label="产品规格" width="120" show-overflow-tooltip />
				<el-table-column prop="quantity" label="产品数量" width="110" align="right" />
				<el-table-column prop="price" label="单价" width="90" align="right" />
				<el-table-column prop="amount" label="金额" width="90" align="right" />
				<el-table-column prop="remark" label="备注" min-width="320" show-overflow-tooltip />
				<el-table-column prop="drawer_user_name" label="开单员" width="120" />
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

		<el-drawer v-model="detailDrawer.visible" size="48%" title="销售产品详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="2" border>
					<el-descriptions-item label="合同编号">{{ detailDrawer.data.header.contract_no }}</el-descriptions-item>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.header.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="订单日期">{{ detailDrawer.data.header.order_date }}</el-descriptions-item>
					<el-descriptions-item label="交付日期">{{ detailDrawer.data.header.delivery_date || '-' }}</el-descriptions-item>
					<el-descriptions-item label="订单类型">{{ detailDrawer.data.header.order_type_text }}</el-descriptions-item>
					<el-descriptions-item label="发货状态">{{ detailDrawer.data.header.ship_state_text }}</el-descriptions-item>
					<el-descriptions-item label="开单员">{{ detailDrawer.data.header.drawer_user_name }}</el-descriptions-item>
					<el-descriptions-item label="备注" :span="2">{{ detailDrawer.data.header.remark || '-' }}</el-descriptions-item>
				</el-descriptions>

				<el-divider content-position="left">产品信息</el-divider>
				<el-descriptions :column="2" border>
					<el-descriptions-item label="产品编码">{{ detailDrawer.data.item.product_code }}</el-descriptions-item>
					<el-descriptions-item label="产品名称">{{ detailDrawer.data.item.product_name }}</el-descriptions-item>
					<el-descriptions-item label="产品规格">{{ detailDrawer.data.item.product_spec || '-' }}</el-descriptions-item>
					<el-descriptions-item label="单位">{{ detailDrawer.data.item.unit_name || '-' }}</el-descriptions-item>
					<el-descriptions-item label="数量">{{ detailDrawer.data.item.quantity }}</el-descriptions-item>
					<el-descriptions-item label="单价">{{ detailDrawer.data.item.price }}</el-descriptions-item>
					<el-descriptions-item label="含税单价">{{ detailDrawer.data.item.tax_price }}</el-descriptions-item>
					<el-descriptions-item label="金额">{{ detailDrawer.data.item.amount }}</el-descriptions-item>
					<el-descriptions-item label="含税金额">{{ detailDrawer.data.item.tax_amount }}</el-descriptions-item>
				</el-descriptions>
			</div>
		</el-drawer>
	</div>
</template>

<script setup lang="ts" name="salesProductListPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { exportExcel } from '/@/utils/exportExcel';
import { useSalesProductListApi, type SalesProductListDetailData, type SalesProductListItem } from '/@/api/sales/productList';

const salesProductListApi = useSalesProductListApi();

const filterForm = reactive({
	contractNo: '',
	customerName: '',
	productName: '',
	productSpec: '',
	orderDate: '',
	orderType: 1 as number | '',
	shipState: '' as number | '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesProductListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesProductListDetailData | null,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesProductListApi.getList({
			contract_no: filterForm.contractNo || undefined,
			customer_name: filterForm.customerName || undefined,
			product_name: filterForm.productName || undefined,
			product_spec: filterForm.productSpec || undefined,
			order_date: filterForm.orderDate || undefined,
			order_type: filterForm.orderType === '' ? undefined : filterForm.orderType,
			ship_state: filterForm.shipState === '' ? undefined : filterForm.shipState,
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

const openDetail = async (row: SalesProductListItem) => {
	const response = await salesProductListApi.getDetail(row.sales_order_item_id);
	detailDrawer.data = response.data as SalesProductListDetailData;
	detailDrawer.visible = true;
};

const onExportExcel = () => {
	exportExcel({
		filename: '销售产品清单',
		columns: [
			{ label: '合同编号', prop: 'contract_no' },
			{ label: '客户名称', prop: 'customer_name' },
			{ label: '订单日期', prop: 'order_date' },
			{ label: '产品名称', prop: 'product_name' },
			{ label: '产品规格', prop: 'product_spec' },
			{ label: '产品数量', prop: 'quantity' },
			{ label: '单价', prop: 'price' },
			{ label: '金额', prop: 'amount' },
			{ label: '备注', prop: 'remark' },
			{ label: '开单员', prop: 'drawer_user_name' },
		],
		data: tableRows.value,
	});
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('销售产品清单加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-product-list-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.product-header {
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
		grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
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

	.product-table {
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

	.detail-panel {
		display: flex;
		flex-direction: column;
		gap: 18px;
	}
}

@media (max-width: 1500px) {
	.sales-product-list-page {
		.filter-form {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
	}
}

@media (max-width: 900px) {
	.sales-product-list-page {
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
