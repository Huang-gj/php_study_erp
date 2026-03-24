<template>
	<div class="sales-business-page layout-padding">
		<section class="sales-business-header">
			<div class="sales-business-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>业务单记录</span>
			</div>
			<div class="sales-business-header__actions">
				<el-button @click="openCreateDialog">添加业务单</el-button>
				<el-button @click="onBatchDelete">批量删除</el-button>
			</div>
		</section>

		<el-card shadow="never" class="filter-card">
			<template #header>
				<div class="card-title">条件搜索</div>
			</template>
			<el-form :model="filterForm" class="filter-form" label-position="top">
				<el-form-item label="公司名称">
					<el-input v-model="filterForm.customerName" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="订单日期">
					<el-date-picker v-model="filterForm.orderDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
				</el-form-item>
				<el-form-item label="审核状态">
					<el-select v-model="filterForm.auditState" placeholder="全部" clearable>
						<el-option v-for="item in bootstrap.auditStateOptions" :key="item.value" :label="item.label" :value="item.value" />
					</el-select>
				</el-form-item>
				<el-form-item label="转单状态">
					<el-select v-model="filterForm.convertState" placeholder="全部" clearable>
						<el-option v-for="item in bootstrap.convertStateOptions" :key="item.value" :label="item.label" :value="item.value" />
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
			<el-table :data="tableRows" v-loading="tableLoading" class="sales-business-table" @selection-change="onSelectionChange">
				<el-table-column type="selection" width="46" fixed="left" />
				<el-table-column prop="id" label="ID" width="72" />
				<el-table-column prop="audit_state_text" label="审核状态" width="110">
					<template #default="{ row }">
						<el-tag :type="tagTypeByAuditState(row.audit_state)" effect="light">{{ row.audit_state_text }}</el-tag>
					</template>
				</el-table-column>
				<el-table-column prop="business_order_no" label="订单编号" min-width="170" />
				<el-table-column prop="customer_name" label="客户名称" min-width="200" show-overflow-tooltip />
				<el-table-column prop="order_date" label="订单日期" width="118" />
				<el-table-column prop="tax_rate" label="税率" width="90" align="right" />
				<el-table-column prop="product_code" label="存货编码" width="132" />
				<el-table-column prop="product_name" label="产品名称" min-width="180" show-overflow-tooltip />
				<el-table-column prop="product_spec" label="规格" width="110" show-overflow-tooltip />
				<el-table-column prop="unit_name" label="单位" width="80" />
				<el-table-column prop="quantity" label="数量" width="96" align="right" />
				<el-table-column prop="tax_price" label="含税单价" width="110" align="right" />
				<el-table-column prop="tax_amount" label="含税金额" width="118" align="right" />
				<el-table-column prop="maker_user_name" label="制单人" width="110" />
				<el-table-column prop="audit_user_name" label="审核人" width="110" />
				<el-table-column prop="create_time" label="创建日期" width="120" />
				<el-table-column label="操作" min-width="220" fixed="right">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button size="small" type="success" @click="openDetail(row)">查看</el-button>
							<el-button size="small" type="warning" @click="onGenerateSalesOrder(row)">生成销售单</el-button>
							<el-button size="small" type="primary" plain @click="showPrintTip(row)">打印</el-button>
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

		<el-drawer v-model="detailDrawer.visible" size="60%" title="业务单详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="2" border>
					<el-descriptions-item label="订单编号">{{ detailDrawer.data.header.business_order_no }}</el-descriptions-item>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.header.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="订单日期">{{ detailDrawer.data.header.order_date }}</el-descriptions-item>
					<el-descriptions-item label="交付日期">{{ detailDrawer.data.header.delivery_date || '-' }}</el-descriptions-item>
					<el-descriptions-item label="审核状态">{{ detailDrawer.data.header.audit_state_text }}</el-descriptions-item>
					<el-descriptions-item label="转单状态">{{ detailDrawer.data.header.convert_state_text }}</el-descriptions-item>
					<el-descriptions-item label="税率">{{ detailDrawer.data.header.tax_rate }}</el-descriptions-item>
					<el-descriptions-item label="明细行数">{{ detailDrawer.data.header.item_count }}</el-descriptions-item>
					<el-descriptions-item label="总数量">{{ detailDrawer.data.header.total_quantity }}</el-descriptions-item>
					<el-descriptions-item label="未税金额">{{ detailDrawer.data.header.total_amount }}</el-descriptions-item>
					<el-descriptions-item label="含税金额">{{ detailDrawer.data.header.total_tax_amount }}</el-descriptions-item>
					<el-descriptions-item label="制单人">{{ detailDrawer.data.header.maker_user_name || '-' }}</el-descriptions-item>
					<el-descriptions-item label="审核人">{{ detailDrawer.data.header.audit_user_name || '-' }}</el-descriptions-item>
					<el-descriptions-item label="创建时间">{{ detailDrawer.data.header.create_time || '-' }}</el-descriptions-item>
					<el-descriptions-item label="审核时间">{{ detailDrawer.data.header.audit_time || '-' }}</el-descriptions-item>
					<el-descriptions-item label="备注" :span="2">{{ detailDrawer.data.header.remark || '-' }}</el-descriptions-item>
				</el-descriptions>

				<el-divider content-position="left">产品明细</el-divider>
				<el-table :data="detailDrawer.data.items" class="detail-table">
					<el-table-column prop="line_no" label="行号" width="70" />
					<el-table-column prop="product_code" label="存货编码" width="132" />
					<el-table-column prop="product_name" label="产品名称" min-width="180" />
					<el-table-column prop="product_spec" label="规格" width="120" />
					<el-table-column prop="unit_name" label="单位" width="80" />
					<el-table-column prop="quantity" label="数量" width="90" align="right" />
					<el-table-column prop="tax_rate" label="税率" width="90" align="right" />
					<el-table-column prop="tax_price" label="含税单价" width="110" align="right" />
					<el-table-column prop="tax_amount" label="含税金额" width="110" align="right" />
					<el-table-column prop="remark" label="备注" min-width="160" show-overflow-tooltip />
				</el-table>
			</div>
		</el-drawer>

		<el-dialog v-model="createDialog.visible" width="980px" title="添加业务单" destroy-on-close>
			<el-form :model="createForm" label-width="90px" class="create-form">
				<div class="create-form__grid">
					<el-form-item label="订单编号">
						<el-input v-model="createForm.businessOrderNo" placeholder="留空则自动生成" />
					</el-form-item>
					<el-form-item label="客户名称">
						<el-select v-model="createForm.customerId" filterable placeholder="请选择客户" @change="onCustomerChange">
							<el-option v-for="item in bootstrap.customers" :key="item.customer_id" :label="item.customer_name" :value="item.customer_id" />
						</el-select>
					</el-form-item>
					<el-form-item label="订单日期">
						<el-date-picker v-model="createForm.orderDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
					</el-form-item>
					<el-form-item label="交付日期">
						<el-date-picker v-model="createForm.deliveryDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
					</el-form-item>
					<el-form-item label="税率">
						<el-input-number v-model="createForm.taxRate" :min="0" :max="100" :precision="2" controls-position="right" />
					</el-form-item>
				</div>
				<el-form-item label="备注">
					<el-input v-model="createForm.remark" type="textarea" :rows="2" placeholder="请输入备注" />
				</el-form-item>
			</el-form>

			<div class="create-items">
				<div class="create-items__head">
					<span>产品明细</span>
					<el-button type="primary" plain @click="addCreateItem">
						<el-icon><ele-Plus /></el-icon>
						新增明细
					</el-button>
				</div>
				<el-table :data="createForm.items" class="create-items__table">
					<el-table-column label="产品" min-width="220">
						<template #default="{ row }">
							<el-select v-model="row.productId" filterable placeholder="请选择产品" @change="() => onProductChange(row)">
								<el-option
									v-for="item in bootstrap.products"
									:key="item.product_id"
									:label="`${item.product_name} (${item.product_code})`"
									:value="item.product_id"
								/>
							</el-select>
						</template>
					</el-table-column>
					<el-table-column label="编码" width="130">
						<template #default="{ row }">{{ row.productCode || '-' }}</template>
					</el-table-column>
					<el-table-column label="规格" width="120">
						<template #default="{ row }">{{ row.productSpec || '-' }}</template>
					</el-table-column>
					<el-table-column label="单位" width="80">
						<template #default="{ row }">{{ row.unitName || '-' }}</template>
					</el-table-column>
					<el-table-column label="数量" width="110">
						<template #default="{ row }">
							<el-input-number v-model="row.quantity" :min="0.0001" :precision="4" controls-position="right" />
						</template>
					</el-table-column>
					<el-table-column label="含税单价" width="130">
						<template #default="{ row }">
							<el-input-number v-model="row.taxPrice" :min="0" :precision="4" controls-position="right" />
						</template>
					</el-table-column>
					<el-table-column label="未税单价" width="130">
						<template #default="{ row }">
							<el-input-number v-model="row.price" :min="0" :precision="4" controls-position="right" />
						</template>
					</el-table-column>
					<el-table-column label="备注" min-width="160">
						<template #default="{ row }">
							<el-input v-model="row.remark" placeholder="请输入备注" />
						</template>
					</el-table-column>
					<el-table-column label="操作" width="90" fixed="right">
						<template #default="{ $index }">
							<el-button type="danger" link @click="removeCreateItem($index)">删除</el-button>
						</template>
					</el-table-column>
				</el-table>
			</div>

			<template #footer>
				<el-button @click="createDialog.visible = false">取消</el-button>
				<el-button type="primary" :loading="createDialog.loading" @click="submitCreate">保存业务单</el-button>
			</template>
		</el-dialog>
	</div>
</template>

<script setup lang="ts" name="salesBusinessPage">
import { computed, onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
	useSalesBusinessApi,
	type SalesBusinessCreatePayload,
	type SalesBusinessCustomerOption,
	type SalesBusinessDetailData,
	type SalesBusinessListItem,
	type SalesBusinessOption,
	type SalesBusinessProductOption,
} from '/@/api/sales/business';

type CreateItemRow = {
	productId: number | null;
	productCode: string;
	productName: string;
	productSpec: string;
	unitName: string;
	quantity: number;
	price: number;
	taxPrice: number;
	remark: string;
};

const salesBusinessApi = useSalesBusinessApi();

const bootstrap = reactive({
	customers: [] as SalesBusinessCustomerOption[],
	products: [] as SalesBusinessProductOption[],
	auditStateOptions: [] as SalesBusinessOption[],
	convertStateOptions: [] as SalesBusinessOption[],
});

const filterForm = reactive({
	customerName: '',
	orderDate: '',
	auditState: '' as number | '',
	convertState: '' as number | '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesBusinessListItem[]>([]);
const selectedRows = ref<SalesBusinessListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesBusinessDetailData | null,
});

const createDialog = reactive({
	visible: false,
	loading: false,
});

const createForm = reactive({
	businessOrderNo: '',
	customerId: null as number | null,
	orderDate: '',
	deliveryDate: '',
	taxRate: 13,
	remark: '',
	items: [] as CreateItemRow[],
});

const productMap = computed(() => {
	return bootstrap.products.reduce((acc, item) => {
		acc[item.product_id] = item;
		return acc;
	}, {} as Record<number, SalesBusinessProductOption>);
});

const customerMap = computed(() => {
	return bootstrap.customers.reduce((acc, item) => {
		acc[item.customer_id] = item;
		return acc;
	}, {} as Record<number, SalesBusinessCustomerOption>);
});

const createEmptyItem = (): CreateItemRow => ({
	productId: null,
	productCode: '',
	productName: '',
	productSpec: '',
	unitName: '',
	quantity: 1,
	price: 0,
	taxPrice: 0,
	remark: '',
});

const resetCreateForm = () => {
	createForm.businessOrderNo = '';
	createForm.customerId = null;
	createForm.orderDate = new Date().toISOString().slice(0, 10);
	createForm.deliveryDate = '';
	createForm.taxRate = 13;
	createForm.remark = '';
	createForm.items = [createEmptyItem()];
};

const loadBootstrap = async () => {
	const response = await salesBusinessApi.getBootstrap();
	bootstrap.customers = response.data.customers ?? [];
	bootstrap.products = response.data.products ?? [];
	bootstrap.auditStateOptions = response.data.audit_state_options ?? [];
	bootstrap.convertStateOptions = response.data.convert_state_options ?? [];
};

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesBusinessApi.getList({
			customer_name: filterForm.customerName || undefined,
			order_date: filterForm.orderDate || undefined,
			audit_state: filterForm.auditState === '' ? undefined : filterForm.auditState,
			convert_state: filterForm.convertState === '' ? undefined : filterForm.convertState,
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

const onSelectionChange = (rows: SalesBusinessListItem[]) => {
	selectedRows.value = rows;
};

const loadDetail = async (businessOrderId: number) => {
	const response = await salesBusinessApi.getDetail(businessOrderId);
	return response.data as SalesBusinessDetailData;
};

const openDetail = async (row: SalesBusinessListItem) => {
	detailDrawer.data = await loadDetail(row.business_order_id);
	detailDrawer.visible = true;
};

const openCreateDialog = () => {
	resetCreateForm();
	createDialog.visible = true;
};

const addCreateItem = () => {
	createForm.items.push(createEmptyItem());
};

const removeCreateItem = (index: number) => {
	if (createForm.items.length === 1) {
		ElMessage.warning('至少保留一条产品明细');
		return;
	}

	createForm.items.splice(index, 1);
};

const onCustomerChange = (customerId: number) => {
	const customer = customerMap.value[customerId];
	if (!customer) {
		return;
	}

	createForm.taxRate = Number(customer.default_tax_rate || 13);
};

const onProductChange = (row: CreateItemRow) => {
	if (!row.productId) {
		return;
	}

	const product = productMap.value[row.productId];
	if (!product) {
		return;
	}

	row.productCode = product.product_code;
	row.productName = product.product_name;
	row.productSpec = product.product_spec;
	row.unitName = product.unit_name;
	row.price = Number(product.default_price || 0);
	row.taxPrice = Number(product.default_tax_price || 0);
};

const submitCreate = async () => {
	if (!createForm.customerId) {
		ElMessage.warning('请选择客户');
		return;
	}

	if (!createForm.orderDate) {
		ElMessage.warning('请选择订单日期');
		return;
	}

	if (createForm.items.some((item) => !item.productId || Number(item.quantity) <= 0)) {
		ElMessage.warning('请完善产品明细中的产品和数量');
		return;
	}

	createDialog.loading = true;
	try {
		const payload: SalesBusinessCreatePayload = {
			business_order_no: createForm.businessOrderNo || undefined,
			customer_id: createForm.customerId,
			order_date: createForm.orderDate,
			delivery_date: createForm.deliveryDate || undefined,
			tax_rate: createForm.taxRate,
			remark: createForm.remark || undefined,
			items: createForm.items.map((item) => ({
				product_id: item.productId,
				quantity: item.quantity,
				price: item.price,
				tax_price: item.taxPrice,
				remark: item.remark || undefined,
			})),
		};
		await salesBusinessApi.create(payload);
		ElMessage.success('业务单创建成功');
		createDialog.visible = false;
		await loadList();
	} finally {
		createDialog.loading = false;
	}
};

const onBatchDelete = async () => {
	if (selectedRows.value.length === 0) {
		ElMessage.warning('请先选择要删除的业务单');
		return;
	}

	await ElMessageBox.confirm(`确认删除选中的 ${selectedRows.value.length} 条业务单吗？`, '删除确认', {
		type: 'warning',
	});
	await salesBusinessApi.batchDelete({
		business_order_ids: selectedRows.value.map((item) => item.business_order_id),
	});
	ElMessage.success('业务单删除成功');
	selectedRows.value = [];
	await loadList();
};

const onGenerateSalesOrder = async (row: SalesBusinessListItem) => {
	await ElMessageBox.confirm(`确认根据业务单 ${row.business_order_no} 生成销售单吗？`, '生成销售单', {
		type: 'warning',
	});
	const response = await salesBusinessApi.generateSalesOrder({ business_order_id: row.business_order_id });
	ElMessage.success(`已生成销售单：${response.data.contract_no}`);
	await loadList();
};

const showPrintTip = (row: SalesBusinessListItem) => {
	ElMessage.success(`已触发打印预览：${row.business_order_no}`);
};

const tagTypeByAuditState = (auditState: number) => {
	if (auditState === 1) {
		return 'success';
	}
	if (auditState === 2) {
		return 'warning';
	}
	if (auditState === 3) {
		return 'danger';
	}
	return 'info';
};

onMounted(async () => {
	try {
		await loadBootstrap();
		resetCreateForm();
		await loadList();
	} catch (error) {
		ElMessage.error('业务单模块初始化失败');
	}
});
</script>

<style scoped lang="scss">
.sales-business-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.sales-business-header {
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
			gap: 10px;
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
		grid-template-columns: repeat(4, minmax(0, 320px)) auto;
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

	.sales-business-table {
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
			padding: 10px 0;
			color: #606978;
			font-size: 13px;
			vertical-align: top;
		}
	}

	.table-actions {
		display: flex;
		flex-wrap: nowrap;
		align-items: center;
		gap: 8px;

		:deep(.el-button) {
			margin-left: 0;
			min-width: 72px;
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

	.detail-panel {
		display: flex;
		flex-direction: column;
		gap: 18px;
	}

	.create-form {
		&__grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 0 14px;
		}
	}

	.create-items {
		margin-top: 8px;

		&__head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 10px;
			font-size: 14px;
			font-weight: 600;
			color: #4b5565;
		}
	}
}

@media (max-width: 1360px) {
	.sales-business-page {
		.filter-form {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
}

@media (max-width: 900px) {
	.sales-business-page {
		.sales-business-header,
		.table-footer {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}

		.filter-form,
		.create-form__grid {
			grid-template-columns: 1fr;
		}
	}
}
</style>
