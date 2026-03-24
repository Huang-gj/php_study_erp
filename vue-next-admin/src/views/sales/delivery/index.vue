<template>
	<div class="sales-delivery-page layout-padding">
		<section class="delivery-header">
			<div class="delivery-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>产品出库</span>
			</div>
			<div class="delivery-header__actions">
				<el-button @click="openCreateDialog">添加</el-button>
				<el-button type="primary" @click="onAuditPass">审核通过</el-button>
				<el-button @click="onReverseAudit">反审核</el-button>
				<el-button @click="onBatchDelete">批量删除</el-button>
				<el-button plain @click="onExportExcel">导出excel</el-button>
			</div>
		</section>

		<el-card shadow="never" class="filter-card">
			<template #header>
				<div class="card-title">条件搜索</div>
			</template>
			<el-form :model="filterForm" class="filter-form" label-position="top">
				<el-form-item label="单据编号">
					<el-input v-model="filterForm.outboundNo" placeholder="请输入" clearable />
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
				<el-form-item label="出库日期">
					<el-date-picker v-model="filterForm.shipDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择出库日期" />
				</el-form-item>
				<el-form-item label="是否开票">
					<el-select v-model="filterForm.invoiceRequired" placeholder="全部" clearable>
						<el-option label="全部" value="" />
						<el-option label="是" :value="1" />
						<el-option label="否" :value="0" />
					</el-select>
				</el-form-item>
				<el-form-item label="审核状态">
					<el-select v-model="filterForm.auditState" placeholder="全部" clearable>
						<el-option label="待审核" :value="0" />
						<el-option label="已审核" :value="1" />
						<el-option label="反审核" :value="2" />
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
			<el-table :data="tableRows" v-loading="tableLoading" @selection-change="onSelectionChange">
				<el-table-column label="操作" min-width="250" fixed="left">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button size="small" type="success" @click="openEditDialog(row)">编辑</el-button>
							<el-button size="small" type="warning" @click="onAuditPass(row)">审核</el-button>
							<el-button size="small" type="danger" plain @click="onDeleteRow(row)">删除</el-button>
							<el-button size="small" type="primary" plain @click="onPrint(row, false)">打印</el-button>
							<el-button size="small" type="info" plain @click="onPrint(row, true)">打印(不含单价)</el-button>
						</div>
					</template>
				</el-table-column>
				<el-table-column type="selection" width="46" fixed="left" />
				<el-table-column prop="id" label="ID" width="90" />
				<el-table-column prop="ship_date" label="出库日期" width="120" />
				<el-table-column prop="customer_name" label="客户名称" min-width="220" show-overflow-tooltip />
				<el-table-column prop="outbound_no" label="单据编号" min-width="160" />
				<el-table-column prop="product_code" label="产品编码" width="140" />
				<el-table-column prop="product_name" label="产品名称" min-width="240" show-overflow-tooltip />
				<el-table-column prop="product_spec" label="产品规格" width="120" show-overflow-tooltip />
				<el-table-column prop="unit_name" label="单位" width="80" />
				<el-table-column prop="outbound_quantity" label="已出库数量" width="120" align="right" />
				<el-table-column prop="invoice_required_text" label="是否开票" width="100" />
				<el-table-column prop="audit_state_text" label="审核状态" width="100" />
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

		<el-dialog v-model="editDialog.visible" :title="editDialog.form.outboundId ? '编辑发货单' : '添加发货单'" width="1080px" destroy-on-close>
			<el-form :model="editDialog.form" label-width="96px" class="edit-form">
				<div class="edit-form__grid">
					<el-form-item label="来源订单">
						<el-select v-model="editDialog.form.salesOrderId" filterable placeholder="请选择订单" :disabled="!!editDialog.form.outboundId" @change="onCandidateOrderChange">
							<el-option v-for="item in bootstrap.candidateOrders" :key="item.sales_order_id" :label="`${item.contract_no} / ${item.customer_name}`" :value="item.sales_order_id" />
						</el-select>
					</el-form-item>
					<el-form-item label="单据日期">
						<el-date-picker v-model="editDialog.form.documentDate" type="date" value-format="YYYY-MM-DD" />
					</el-form-item>
					<el-form-item label="出库日期">
						<el-date-picker v-model="editDialog.form.shipDate" type="date" value-format="YYYY-MM-DD" />
					</el-form-item>
					<el-form-item label="是否开票">
						<el-switch v-model="editDialog.form.invoiceRequired" :active-value="1" :inactive-value="0" />
					</el-form-item>
					<el-form-item label="物流费用">
						<el-input-number v-model="editDialog.form.logisticsFee" :min="0" :precision="2" controls-position="right" />
					</el-form-item>
					<el-form-item label="快递单号">
						<el-input v-model="editDialog.form.expressNo" />
					</el-form-item>
					<el-form-item label="司机姓名">
						<el-input v-model="editDialog.form.driverName" />
					</el-form-item>
					<el-form-item label="车牌号">
						<el-input v-model="editDialog.form.vehicleNo" />
					</el-form-item>
					<el-form-item label="收货人">
						<el-input v-model="editDialog.form.receiverName" />
					</el-form-item>
					<el-form-item label="收货电话">
						<el-input v-model="editDialog.form.receiverPhone" />
					</el-form-item>
				</div>
				<el-form-item label="收货地址">
					<el-input v-model="editDialog.form.receiverAddress" />
				</el-form-item>
				<el-form-item label="备注">
					<el-input v-model="editDialog.form.remark" type="textarea" :rows="2" />
				</el-form-item>
			</el-form>

			<el-table :data="editDialog.form.items" class="edit-items-table">
				<el-table-column prop="productCode" label="产品编码" width="140" />
				<el-table-column prop="productName" label="产品名称" min-width="180" />
				<el-table-column prop="productSpec" label="产品规格" width="120" />
				<el-table-column prop="unitName" label="单位" width="80" />
				<el-table-column label="出库数量" width="130">
					<template #default="{ row }">
						<el-input-number v-model="row.outboundQuantity" :min="0" :precision="4" controls-position="right" />
					</template>
				</el-table-column>
				<el-table-column label="单价" width="130">
					<template #default="{ row }">
						<el-input-number v-model="row.price" :min="0" :precision="4" controls-position="right" />
					</template>
				</el-table-column>
				<el-table-column label="含税单价" width="130">
					<template #default="{ row }">
						<el-input-number v-model="row.taxPrice" :min="0" :precision="4" controls-position="right" />
					</template>
				</el-table-column>
				<el-table-column label="备注" min-width="160">
					<template #default="{ row }">
						<el-input v-model="row.remark" />
					</template>
				</el-table-column>
			</el-table>

			<template #footer>
				<el-button @click="editDialog.visible = false">取消</el-button>
				<el-button type="primary" :loading="editDialog.saving" @click="submitSave">保存</el-button>
			</template>
		</el-dialog>
	</div>
</template>

<script setup lang="ts" name="salesDeliveryPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { exportExcel } from '/@/utils/exportExcel';
import { useSalesDeliveryApi, type SalesDeliveryCandidateOrder, type SalesDeliveryDetailData, type SalesDeliveryListItem } from '/@/api/sales/delivery';

const salesDeliveryApi = useSalesDeliveryApi();

const bootstrap = reactive({
	candidateOrders: [] as SalesDeliveryCandidateOrder[],
});

const filterForm = reactive({
	outboundNo: '',
	customerName: '',
	productName: '',
	productSpec: '',
	shipDate: '',
	invoiceRequired: '' as number | '',
	auditState: '' as number | '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesDeliveryListItem[]>([]);
const selectedRows = ref<SalesDeliveryListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const emptyEditForm = () => ({
	outboundId: 0,
	salesOrderId: null as number | null,
	documentDate: new Date().toISOString().slice(0, 10),
	shipDate: '',
	invoiceRequired: 1,
	logisticsFee: 0,
	expressNo: '',
	driverName: '',
	vehicleNo: '',
	receiverName: '',
	receiverPhone: '',
	receiverAddress: '',
	remark: '',
	items: [] as Array<any>,
});

const editDialog = reactive({
	visible: false,
	saving: false,
	form: emptyEditForm(),
});

const loadBootstrap = async () => {
	const response = await salesDeliveryApi.getBootstrap();
	bootstrap.candidateOrders = response.data.candidate_orders ?? [];
};

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesDeliveryApi.getList({
			outbound_no: filterForm.outboundNo || undefined,
			customer_name: filterForm.customerName || undefined,
			product_name: filterForm.productName || undefined,
			product_spec: filterForm.productSpec || undefined,
			ship_date: filterForm.shipDate || undefined,
			invoice_required: filterForm.invoiceRequired,
			audit_state: filterForm.auditState,
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

const onSelectionChange = (rows: SalesDeliveryListItem[]) => {
	selectedRows.value = rows;
};

const openCreateDialog = async () => {
	editDialog.form = emptyEditForm();
	editDialog.visible = true;
	await loadBootstrap();
};

const onCandidateOrderChange = async (salesOrderId: number) => {
	const response = await salesDeliveryApi.getBootstrap();
	bootstrap.candidateOrders = response.data.candidate_orders ?? [];
	const candidate = bootstrap.candidateOrders.find((item) => item.sales_order_id === salesOrderId);
	if (!candidate) return;
	editDialog.form.invoiceRequired = candidate.invoice_required ?? 1;
	editDialog.form.shipDate = candidate.delivery_date || '';

	const orderDetailApi = (await import('/@/api/sales/order')).useSalesOrderApi();
	const detailResponse = await orderDetailApi.getDetail(salesOrderId);
	const header = detailResponse.data.header;
	const items = detailResponse.data.items ?? [];
	editDialog.form.receiverName = header.customer_name || '';
	editDialog.form.receiverAddress = '';
	editDialog.form.items = items.map((item: any) => ({
		salesOrderItemId: item.sales_order_item_id,
		productCode: item.product_code,
		productName: item.product_name,
		productSpec: item.product_spec,
		unitName: item.unit_name,
		outboundQuantity: Number(item.quantity || 0),
		price: Number(item.price || 0),
		taxPrice: Number(item.tax_price || 0),
		remark: item.remark || '',
	}));
	bootstrap.candidateOrders = response.data.candidate_orders ?? [];
};

const openEditDialog = async (row: SalesDeliveryListItem) => {
	const response = await salesDeliveryApi.getDetail(row.outbound_id);
	const detail = response.data as SalesDeliveryDetailData;
	editDialog.form = {
		outboundId: detail.header.outbound_id,
		salesOrderId: detail.header.sales_order_id,
		documentDate: detail.header.document_date,
		shipDate: detail.header.ship_date,
		invoiceRequired: detail.header.invoice_required,
		logisticsFee: Number(detail.header.logistics_fee || 0),
		expressNo: detail.header.express_no || '',
		driverName: detail.header.driver_name || '',
		vehicleNo: detail.header.vehicle_no || '',
		receiverName: detail.header.receiver_name || '',
		receiverPhone: detail.header.receiver_phone || '',
		receiverAddress: detail.header.receiver_address || '',
		remark: detail.header.remark || '',
		items: detail.items.map((item) => ({
			salesOrderItemId: item.outbound_item_id ? undefined : undefined,
			outboundItemId: item.outbound_item_id,
			productCode: item.product_code,
			productName: item.product_name,
			productSpec: item.product_spec,
			unitName: item.unit_name,
			outboundQuantity: Number(item.outbound_quantity || 0),
			price: Number(item.price || 0),
			taxPrice: Number(item.tax_price || 0),
			remark: item.remark || '',
		})),
	};
	// reload order detail so save payload can carry sales_order_item_id
	const orderDetailApi = (await import('/@/api/sales/order')).useSalesOrderApi();
	const orderDetailResponse = await orderDetailApi.getDetail(detail.header.sales_order_id);
	const orderItems = orderDetailResponse.data.items ?? [];
	editDialog.form.items = editDialog.form.items.map((item, index) => ({
		...item,
		salesOrderItemId: orderItems[index]?.sales_order_item_id,
	}));
	editDialog.visible = true;
};

const submitSave = async () => {
	if (!editDialog.form.salesOrderId) {
		ElMessage.warning('请选择来源订单');
		return;
	}
	editDialog.saving = true;
	try {
		await salesDeliveryApi.save({
			outbound_id: editDialog.form.outboundId || undefined,
			sales_order_id: editDialog.form.salesOrderId,
			document_date: editDialog.form.documentDate,
			ship_date: editDialog.form.shipDate || undefined,
			invoice_required: editDialog.form.invoiceRequired,
			logistics_fee: editDialog.form.logisticsFee,
			express_no: editDialog.form.expressNo,
			driver_name: editDialog.form.driverName,
			vehicle_no: editDialog.form.vehicleNo,
			receiver_name: editDialog.form.receiverName,
			receiver_phone: editDialog.form.receiverPhone,
			receiver_address: editDialog.form.receiverAddress,
			remark: editDialog.form.remark,
			items: editDialog.form.items.map((item) => ({
				outbound_item_id: item.outboundItemId,
				sales_order_item_id: item.salesOrderItemId,
				outbound_quantity: item.outboundQuantity,
				price: item.price,
				tax_price: item.taxPrice,
				remark: item.remark,
			})),
		});
		ElMessage.success('保存成功');
		editDialog.visible = false;
		await Promise.all([loadBootstrap(), loadList()]);
	} finally {
		editDialog.saving = false;
	}
};

const resolveTargetRow = (row?: SalesDeliveryListItem) => row ?? selectedRows.value[0];

const onAuditPass = async (row?: SalesDeliveryListItem) => {
	const target = resolveTargetRow(row);
	if (!target) {
		ElMessage.warning('请选择一条发货单');
		return;
	}
	await salesDeliveryApi.auditPass(target.outbound_id);
	ElMessage.success('审核通过成功');
	await loadList();
};

const onReverseAudit = async (row?: SalesDeliveryListItem) => {
	const target = resolveTargetRow(row);
	if (!target) {
		ElMessage.warning('请选择一条发货单');
		return;
	}
	await salesDeliveryApi.reverseAudit(target.outbound_id);
	ElMessage.success('反审核成功');
	await loadList();
};

const onDeleteRow = async (row: SalesDeliveryListItem) => {
	await ElMessageBox.confirm(`确认删除发货单 ${row.outbound_no} 吗？`, '删除', { type: 'warning' });
	await salesDeliveryApi.batchDelete([row.outbound_id]);
	ElMessage.success('删除成功');
	await loadList();
};

const onBatchDelete = async () => {
	if (selectedRows.value.length === 0) {
		ElMessage.warning('请选择要删除的发货单');
		return;
	}
	await salesDeliveryApi.batchDelete(selectedRows.value.map((item) => item.outbound_id));
	ElMessage.success('批量删除成功');
	await loadList();
};

const onPrint = async (row: SalesDeliveryListItem, withoutPrice: boolean) => {
	await salesDeliveryApi.print(row.outbound_id, withoutPrice ? 1 : 0);
	ElMessage.success(withoutPrice ? '已记录不含单价打印' : '已记录打印');
};

const onExportExcel = () => {
	exportExcel({
		filename: '产品出库',
		columns: [
			{ label: '出库日期', prop: 'ship_date' },
			{ label: '客户名称', prop: 'customer_name' },
			{ label: '单据编号', prop: 'outbound_no' },
			{ label: '产品编码', prop: 'product_code' },
			{ label: '产品名称', prop: 'product_name' },
			{ label: '产品规格', prop: 'product_spec' },
			{ label: '单位', prop: 'unit_name' },
			{ label: '已出库数量', prop: 'outbound_quantity' },
			{ label: '是否开票', prop: 'invoice_required_text' },
			{ label: '审核状态', prop: 'audit_state_text' },
		],
		data: tableRows.value,
	});
};

onMounted(async () => {
	try {
		await Promise.all([loadBootstrap(), loadList()]);
	} catch (error) {
		ElMessage.error('发货数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-delivery-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.delivery-header {
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
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
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
		grid-template-columns: repeat(5, minmax(0, 1fr));
		gap: 10px 14px;
		align-items: end;

		:deep(.el-form-item) {
			margin-bottom: 0;
		}
	}

	.table-actions {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, max-content));
		gap: 8px;
		align-items: center;

		:deep(.el-button) {
			min-width: 74px;
			margin: 0;
		}
	}

	.edit-form__grid {
		display: grid;
		grid-template-columns: repeat(5, minmax(0, 1fr));
		gap: 2px 14px;
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
	.sales-delivery-page {
		.filter-form,
		.edit-form__grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
}

@media (max-width: 768px) {
	.sales-delivery-page {
		.delivery-header {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}

		.filter-form,
		.edit-form__grid {
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
