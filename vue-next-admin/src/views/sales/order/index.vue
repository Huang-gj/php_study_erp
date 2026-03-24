<template>
	<div class="sales-order-page layout-padding">
		<section class="sales-order-header">
			<div class="sales-order-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>销售单列表</span>
			</div>
			<div class="sales-order-header__actions">
				<el-button plain @click="onExportExcel">导出excel</el-button>
				<el-button @click="openCreateDialog">
					<el-icon><ele-Plus /></el-icon>
					添加销售单
				</el-button>
				<el-button @click="onShipInvoice">
					<el-icon><ele-Box /></el-icon>
					发货开票
				</el-button>
				<el-button type="primary" @click="onAuditPass">
					<el-icon><ele-Select /></el-icon>
					审核通过
				</el-button>
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
					<el-select v-model="filterForm.orderType" placeholder="全部" clearable>
						<el-option v-for="item in bootstrap.orderTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
					</el-select>
				</el-form-item>
				<el-form-item label="发货状态">
					<el-select v-model="filterForm.shipState" placeholder="全部" clearable>
						<el-option v-for="item in bootstrap.shipStateOptions" :key="item.value" :label="item.label" :value="item.value" />
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
			<el-table :data="tableRows" v-loading="tableLoading" class="sales-order-table" @selection-change="onSelectionChange">
				<el-table-column type="selection" width="46" fixed="left" />
				<el-table-column label="操作" min-width="220" fixed="left">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button size="small" type="success" @click="openDetail(row)">查看</el-button>
							<el-button size="small" type="danger" plain @click="showAfterSaleTip">售后</el-button>
							<el-button size="small" type="primary" plain @click="showPrintTip(row)">打印</el-button>
							<el-button size="small" type="info" plain @click="openProgress(row)">进度</el-button>
						</div>
					</template>
				</el-table-column>
				<el-table-column prop="id" label="ID" width="78" />
				<el-table-column prop="audit_status" label="审核状态" width="132">
					<template #default="{ row }">
						<el-tag :type="tagTypeByAuditState(row.audit_state)" effect="light">{{ row.audit_status }}</el-tag>
					</template>
				</el-table-column>
				<el-table-column prop="contract_no" label="合同编号" min-width="170" />
				<el-table-column prop="customer_name" label="客户名称" min-width="190" show-overflow-tooltip />
				<el-table-column prop="order_date" label="订单日期" width="118" />
				<el-table-column prop="product_code" label="产品编码" width="136" />
				<el-table-column prop="product_name" label="名称" min-width="220" show-overflow-tooltip />
				<el-table-column prop="product_spec" label="规格" width="120" show-overflow-tooltip />
				<el-table-column prop="quantity" label="数量" width="92" align="right" />
				<el-table-column prop="tax_price" label="含税单价" width="110" align="right" />
				<el-table-column prop="price" label="单价" width="96" align="right" />
				<el-table-column prop="total_tax_amount" label="销售总价" width="118" align="right" />
				<el-table-column prop="expected_stock_quantity" label="预计库存" width="118" align="right" />
				<el-table-column prop="ship_state_text" label="发货状态" width="108" />
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

		<el-drawer v-model="detailDrawer.visible" size="58%" title="销售单详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="2" border>
					<el-descriptions-item label="合同编号">{{ detailDrawer.data.header.contract_no }}</el-descriptions-item>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.header.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="订单类型">{{ detailDrawer.data.header.order_type_text }}</el-descriptions-item>
					<el-descriptions-item label="审核状态">{{ detailDrawer.data.header.audit_status }}</el-descriptions-item>
					<el-descriptions-item label="订单状态">{{ detailDrawer.data.header.order_state_text }}</el-descriptions-item>
					<el-descriptions-item label="发货状态">{{ detailDrawer.data.header.ship_state_text }}</el-descriptions-item>
					<el-descriptions-item label="订单日期">{{ detailDrawer.data.header.order_date }}</el-descriptions-item>
					<el-descriptions-item label="交付日期">{{ detailDrawer.data.header.delivery_date || '-' }}</el-descriptions-item>
					<el-descriptions-item label="付款方式">{{ detailDrawer.data.header.payment_method_text }}</el-descriptions-item>
					<el-descriptions-item label="是否开票">{{ detailDrawer.data.header.invoice_required === 1 ? '是' : '否' }}</el-descriptions-item>
					<el-descriptions-item label="总数量">{{ detailDrawer.data.header.total_quantity }}</el-descriptions-item>
					<el-descriptions-item label="含税总金额">{{ detailDrawer.data.header.total_tax_amount }}</el-descriptions-item>
					<el-descriptions-item label="物流费用">{{ detailDrawer.data.header.logistics_fee }}</el-descriptions-item>
					<el-descriptions-item label="其他费用">{{ detailDrawer.data.header.other_fee }}</el-descriptions-item>
					<el-descriptions-item label="备注" :span="2">{{ detailDrawer.data.header.remark || '-' }}</el-descriptions-item>
				</el-descriptions>

				<el-divider content-position="left">产品明细</el-divider>
				<el-table :data="detailDrawer.data.items" class="detail-table">
					<el-table-column prop="line_no" label="行号" width="72" />
					<el-table-column prop="product_code" label="产品编码" width="136" />
					<el-table-column prop="product_name" label="产品名称" min-width="180" />
					<el-table-column prop="product_spec" label="规格" width="120" />
					<el-table-column prop="unit_name" label="单位" width="80" />
					<el-table-column prop="quantity" label="数量" width="90" align="right" />
					<el-table-column prop="tax_price" label="含税单价" width="110" align="right" />
					<el-table-column prop="tax_amount" label="含税金额" width="110" align="right" />
					<el-table-column prop="expected_stock_quantity" label="预计库存" width="110" align="right" />
				</el-table>
			</div>
		</el-drawer>

		<el-dialog v-model="progressDialog.visible" width="720px" title="销售进度" destroy-on-close>
			<el-timeline v-if="progressDialog.logs.length > 0" class="progress-timeline">
				<el-timeline-item
					v-for="item in progressDialog.logs"
					:key="item.progress_log_id"
					:timestamp="item.finish_time || item.start_time || '待更新'"
					:type="timelineType(item.step_state)"
				>
					<div class="progress-item">
						<div class="progress-item__head">
							<strong>{{ item.step_name }}</strong>
							<el-tag size="small" effect="plain">{{ item.step_state_text }}</el-tag>
						</div>
						<div class="progress-item__meta">操作人：{{ item.operator_user_name || '系统' }}</div>
						<div class="progress-item__meta" v-if="item.related_no">关联单号：{{ item.related_no }}</div>
						<div class="progress-item__remark">{{ item.remark || '暂无备注' }}</div>
					</div>
				</el-timeline-item>
			</el-timeline>
			<el-empty v-else description="暂无进度记录" />
		</el-dialog>

		<el-dialog v-model="createDialog.visible" width="980px" title="添加销售单" destroy-on-close>
			<el-form :model="createForm" label-width="96px" class="create-form">
				<div class="create-form__grid">
					<el-form-item label="合同编号">
						<el-input v-model="createForm.contractNo" placeholder="留空则自动生成" />
					</el-form-item>
					<el-form-item label="客户">
						<el-select v-model="createForm.customerId" filterable placeholder="请选择客户" @change="onCustomerChange">
							<el-option v-for="item in bootstrap.customers" :key="item.customer_id" :label="item.customer_name" :value="item.customer_id" />
						</el-select>
					</el-form-item>
					<el-form-item label="订单类型">
						<el-select v-model="createForm.orderType">
							<el-option v-for="item in bootstrap.orderTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
						</el-select>
					</el-form-item>
					<el-form-item label="订单日期">
						<el-date-picker v-model="createForm.orderDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
					</el-form-item>
					<el-form-item label="交付日期">
						<el-date-picker v-model="createForm.deliveryDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
					</el-form-item>
					<el-form-item label="付款方式">
						<el-select v-model="createForm.paymentMethod">
							<el-option v-for="item in bootstrap.paymentMethodOptions" :key="item.value" :label="item.label" :value="item.value" />
						</el-select>
					</el-form-item>
					<el-form-item label="税率">
						<el-input-number v-model="createForm.taxRate" :min="0" :max="100" :precision="2" controls-position="right" />
					</el-form-item>
					<el-form-item label="是否开票">
						<el-switch v-model="createForm.invoiceRequired" :active-value="1" :inactive-value="0" />
					</el-form-item>
					<el-form-item label="物流费">
						<el-input-number v-model="createForm.logisticsFee" :min="0" :precision="2" controls-position="right" />
					</el-form-item>
					<el-form-item label="其他费用">
						<el-input-number v-model="createForm.otherFee" :min="0" :precision="2" controls-position="right" />
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
					<el-table-column label="产品" min-width="230">
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
					<el-table-column label="预计库存" width="130">
						<template #default="{ row }">
							<el-input-number v-model="row.expectedStockQuantity" :min="0" :precision="4" controls-position="right" />
						</template>
					</el-table-column>
					<el-table-column label="备注" min-width="180">
						<template #default="{ row }">
							<el-input v-model="row.remark" placeholder="备注" />
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
				<el-button type="primary" :loading="createDialog.loading" @click="submitCreate">保存销售单</el-button>
			</template>
		</el-dialog>
	</div>
</template>

<script setup lang="ts" name="salesOrderPage">
import { computed, onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { exportExcel } from '/@/utils/exportExcel';
import {
	useSalesOrderApi,
	type SalesOrderCustomerOption,
	type SalesOrderCreatePayload,
	type SalesOrderDetailData,
	type SalesOrderListItem,
	type SalesOrderOption,
	type SalesOrderProductOption,
	type SalesOrderProgressLog,
} from '/@/api/sales/order';

type CreateItemRow = {
	productId: number | null;
	productCode: string;
	productName: string;
	productSpec: string;
	quantity: number;
	taxPrice: number;
	price: number;
	expectedStockQuantity: number;
	remark: string;
};

const salesOrderApi = useSalesOrderApi();

const bootstrap = reactive({
	customers: [] as SalesOrderCustomerOption[],
	products: [] as SalesOrderProductOption[],
	orderTypeOptions: [] as SalesOrderOption[],
	shipStateOptions: [] as SalesOrderOption[],
	paymentMethodOptions: [] as SalesOrderOption[],
});

const filterForm = reactive({
	contractNo: '',
	customerName: '',
	productName: '',
	productSpec: '',
	orderDate: '',
	orderType: '' as number | '',
	shipState: '' as number | '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesOrderListItem[]>([]);
const selectedRows = ref<SalesOrderListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesOrderDetailData | null,
});

const progressDialog = reactive({
	visible: false,
	logs: [] as SalesOrderProgressLog[],
});

const createDialog = reactive({
	visible: false,
	loading: false,
});

const createForm = reactive({
	contractNo: '',
	customerId: null as number | null,
	orderType: 1,
	orderDate: '',
	deliveryDate: '',
	paymentMethod: 0,
	invoiceRequired: 1,
	taxRate: 13,
	logisticsFee: 0,
	otherFee: 0,
	remark: '',
	items: [] as CreateItemRow[],
});

const productMap = computed(() => {
	return bootstrap.products.reduce((acc, item) => {
		acc[item.product_id] = item;
		return acc;
	}, {} as Record<number, SalesOrderProductOption>);
});

const customerMap = computed(() => {
	return bootstrap.customers.reduce((acc, item) => {
		acc[item.customer_id] = item;
		return acc;
	}, {} as Record<number, SalesOrderCustomerOption>);
});

const createEmptyItem = (): CreateItemRow => ({
	productId: null,
	productCode: '',
	productName: '',
	productSpec: '',
	quantity: 1,
	taxPrice: 0,
	price: 0,
	expectedStockQuantity: 0,
	remark: '',
});

const resetCreateForm = () => {
	createForm.contractNo = '';
	createForm.customerId = null;
	createForm.orderType = 1;
	createForm.orderDate = new Date().toISOString().slice(0, 10);
	createForm.deliveryDate = '';
	createForm.paymentMethod = 0;
	createForm.invoiceRequired = 1;
	createForm.taxRate = 13;
	createForm.logisticsFee = 0;
	createForm.otherFee = 0;
	createForm.remark = '';
	createForm.items = [createEmptyItem()];
};

const loadBootstrap = async () => {
	const response = await salesOrderApi.getBootstrap();
	bootstrap.customers = response.data.customers ?? [];
	bootstrap.products = response.data.products ?? [];
	bootstrap.orderTypeOptions = response.data.order_type_options ?? [];
	bootstrap.shipStateOptions = response.data.ship_state_options ?? [];
	bootstrap.paymentMethodOptions = response.data.payment_method_options ?? [];
};

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesOrderApi.getList({
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

const onSelectionChange = (rows: SalesOrderListItem[]) => {
	selectedRows.value = rows;
};

const getSingleSelectedRow = () => {
	if (selectedRows.value.length !== 1) {
		ElMessage.warning('请选择一条销售单记录');
		return null;
	}

	return selectedRows.value[0];
};

const loadDetail = async (salesOrderId: number) => {
	const response = await salesOrderApi.getDetail(salesOrderId);
	return response.data as SalesOrderDetailData;
};

const openDetail = async (row: SalesOrderListItem) => {
	detailDrawer.data = await loadDetail(row.sales_order_id);
	detailDrawer.visible = true;
};

const openProgress = async (row: SalesOrderListItem) => {
	const detail = await loadDetail(row.sales_order_id);
	progressDialog.logs = detail.progress_logs ?? [];
	progressDialog.visible = true;
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
	createForm.paymentMethod = customer.default_payment_method ?? 0;
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
	row.taxPrice = Number(product.default_tax_price || 0);
	row.price = Number(product.default_price || 0);
	row.expectedStockQuantity = Number(product.current_stock_quantity || 0);
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
		const payload: SalesOrderCreatePayload = {
			contract_no: createForm.contractNo || undefined,
			customer_id: createForm.customerId,
			order_type: createForm.orderType,
			order_date: createForm.orderDate,
			delivery_date: createForm.deliveryDate || undefined,
			payment_method: createForm.paymentMethod,
			invoice_required: createForm.invoiceRequired,
			tax_rate: createForm.taxRate,
			logistics_fee: createForm.logisticsFee,
			other_fee: createForm.otherFee,
			remark: createForm.remark || undefined,
			items: createForm.items.map((item) => ({
				product_id: item.productId,
				quantity: item.quantity,
				tax_price: item.taxPrice,
				price: item.price,
				expected_stock_quantity: item.expectedStockQuantity,
				remark: item.remark,
			})),
		};

		await salesOrderApi.create(payload);
		ElMessage.success('销售单创建成功');
		createDialog.visible = false;
		await loadList();
	} finally {
		createDialog.loading = false;
	}
};

const onAuditPass = async () => {
	const row = getSingleSelectedRow();
	if (!row) {
		return;
	}

	await ElMessageBox.confirm(`确认审核通过销售单 ${row.contract_no} 吗？`, '审核确认', {
		type: 'warning',
	});
	await salesOrderApi.auditPass(row.sales_order_id);
	ElMessage.success('审核通过成功');
	await loadList();
};

const onShipInvoice = async () => {
	const row = getSingleSelectedRow();
	if (!row) {
		return;
	}

	await ElMessageBox.confirm(`确认对销售单 ${row.contract_no} 执行发货开票吗？`, '发货开票', {
		type: 'warning',
	});
	await salesOrderApi.shipInvoice(row.sales_order_id);
	ElMessage.success('发货开票完成');
	await loadList();
};

const onExportExcel = () => {
	exportExcel({
		filename: '销售单列表',
		columns: [
			{ label: '合同编号', prop: 'contract_no' },
			{ label: '客户名称', prop: 'customer_name' },
			{ label: '订单日期', prop: 'order_date' },
			{ label: '产品编码', prop: 'product_code' },
			{ label: '产品名称', prop: 'product_name' },
			{ label: '规格', prop: 'product_spec' },
			{ label: '数量', prop: 'quantity' },
			{ label: '含税单价', prop: 'tax_price' },
			{ label: '单价', prop: 'price' },
			{ label: '销售总价', prop: 'total_tax_amount' },
			{ label: '预计库存', prop: 'expected_stock_quantity' },
			{ label: '审核状态', prop: 'audit_status' },
			{ label: '发货状态', prop: 'ship_state_text' },
		],
		data: tableRows.value,
	});
};

const showAfterSaleTip = () => {
	ElMessage.info('售后入口已预留，后续可继续接入售后模块');
};

const showPrintTip = (row: SalesOrderListItem) => {
	ElMessage.success(`已触发打印预览：${row.contract_no}`);
};

const tagTypeByAuditState = (auditState: number) => {
	if (auditState === 1) {
		return 'success';
	}
	if (auditState === 3) {
		return 'danger';
	}
	if (auditState === 2) {
		return 'warning';
	}
	return 'info';
};

const timelineType = (stepState: number) => {
	if (stepState === 2) {
		return 'success';
	}
	if (stepState === 1) {
		return 'primary';
	}
	return 'info';
};

onMounted(async () => {
	try {
		await loadBootstrap();
		resetCreateForm();
		await loadList();
	} catch (error) {
		ElMessage.error('销售单模块初始化失败');
	}
});
</script>

<style scoped lang="scss">
.sales-order-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.sales-order-header {
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

	:deep(.filter-card .el-card__header),
	:deep(.table-card .el-card__header) {
		padding: 12px 16px;
	}

	:deep(.filter-card .el-card__body) {
		padding: 12px 16px 10px;
	}

	:deep(.table-card .el-card__body) {
		padding: 8px 10px 12px;
	}

	.card-title {
		font-size: 14px;
		font-weight: 600;
		color: #596273;
	}

	.filter-form {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
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

	.sales-order-table {
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
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 72px));
		gap: 8px;
		align-items: stretch;

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

	.progress-timeline {
		padding: 10px 8px;
	}

	.progress-item {
		display: flex;
		flex-direction: column;
		gap: 6px;

		&__head {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		&__meta {
			color: #667085;
			font-size: 13px;
		}

		&__remark {
			color: #344054;
			font-size: 13px;
		}
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

@media (max-width: 1280px) {
	.sales-order-page {
		.filter-form,
		.create-form__grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
}

@media (max-width: 900px) {
	.sales-order-page {
		.sales-order-header,
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
