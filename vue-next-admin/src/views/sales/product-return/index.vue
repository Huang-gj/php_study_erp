<template>
	<div class="sales-product-return-page layout-padding">
		<section class="page-header">
			<div class="page-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>物料退货</span>
			</div>
			<div class="page-header__actions">
				<el-button @click="openCreateDialog(1)">添加入库退货</el-button>
				<el-button @click="openCreateDialog(2)">添加出库退货</el-button>
				<el-button type="primary" @click="onAuditPass()">审核通过</el-button>
				<el-button @click="onReverseAudit()">反审核</el-button>
				<el-button @click="onBatchDelete">批量删除</el-button>
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
				<el-form-item label="物料名称">
					<el-input v-model="filterForm.productName" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="物料规格">
					<el-input v-model="filterForm.productSpec" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="实际入库日期">
					<el-date-picker v-model="filterForm.actualStockinDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择" />
				</el-form-item>
				<el-form-item label="备注">
					<el-input v-model="filterForm.remark" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="审核状态">
					<el-select v-model="filterForm.auditState" placeholder="请选择" clearable>
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
				<el-table-column type="selection" width="48" fixed="left" />
				<el-table-column prop="id" label="ID" width="90" />
				<el-table-column prop="create_time" label="创建时间" width="170" />
				<el-table-column prop="return_no" label="退货单编号" min-width="180" />
				<el-table-column prop="customer_name" label="客户名称" min-width="180" show-overflow-tooltip />
				<el-table-column prop="return_type_text" label="退货类型" width="110" />
				<el-table-column prop="product_name" label="物料名称" min-width="180" show-overflow-tooltip />
				<el-table-column prop="product_spec" label="物料规格" width="120" show-overflow-tooltip />
				<el-table-column prop="warehouse_name" label="退货仓库名称" min-width="140" show-overflow-tooltip />
				<el-table-column prop="quantity" label="退货数量" width="110" align="right" />
				<el-table-column prop="unit_name" label="单位" width="80" />
				<el-table-column prop="price" label="单价" width="100" align="right" />
				<el-table-column prop="amount" label="总金额" width="110" align="right" />
				<el-table-column prop="total_amount" label="退货总价" width="110" align="right" />
				<el-table-column prop="audit_state_text" label="审核状态" width="100">
					<template #default="{ row }">
						<el-tag :type="auditTagType(row.audit_state)" size="small">{{ row.audit_state_text }}</el-tag>
					</template>
				</el-table-column>
				<el-table-column prop="maker_user_name" label="制单人" width="120" />
				<el-table-column label="操作" min-width="116" fixed="right">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button v-if="row.audit_state !== 1" type="success" size="small" @click="openEditDialog(row)">编辑</el-button>
							<el-button v-else type="success" size="small" @click="openDetailDialog(row)">查看</el-button>
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

		<el-dialog v-model="editDialog.visible" :title="editDialog.title" width="1120px" destroy-on-close>
			<el-form :model="editDialog.form" label-width="96px" class="edit-form">
				<div class="edit-form__grid">
					<el-form-item label="来源出库单">
						<el-select
							v-model="editDialog.form.relatedOutboundId"
							filterable
							placeholder="请选择"
							:disabled="editDialog.readonly || !!editDialog.form.returnId"
							@change="onCandidateOutboundChange"
						>
							<el-option
								v-for="item in bootstrap.candidateOutbounds"
								:key="item.outbound_id"
								:label="`${item.outbound_no} / ${item.customer_name}`"
								:value="item.outbound_id"
							/>
						</el-select>
					</el-form-item>
					<el-form-item label="退货类型">
						<el-select v-model="editDialog.form.returnType" :disabled="editDialog.readonly || !!editDialog.form.returnId">
							<el-option label="入库退货" :value="1" />
							<el-option label="出库退货" :value="2" />
						</el-select>
					</el-form-item>
					<el-form-item label="客户名称">
						<el-input v-model="editDialog.form.customerName" disabled />
					</el-form-item>
					<el-form-item label="实际入库日期">
						<el-date-picker
							v-model="editDialog.form.actualStockinDate"
							type="date"
							value-format="YYYY-MM-DD"
							placeholder="请选择"
							:disabled="editDialog.readonly"
						/>
					</el-form-item>
				</div>
				<el-form-item label="备注">
					<el-input v-model="editDialog.form.remark" type="textarea" :rows="2" :disabled="editDialog.readonly" />
				</el-form-item>
			</el-form>

			<el-table :data="editDialog.form.items" class="edit-items-table">
				<el-table-column prop="productCode" label="物料编码" width="140" />
				<el-table-column prop="productName" label="物料名称" min-width="180" show-overflow-tooltip />
				<el-table-column prop="productSpec" label="物料规格" width="120" show-overflow-tooltip />
				<el-table-column prop="warehouseName" label="退货仓库" width="140" show-overflow-tooltip />
				<el-table-column prop="unitName" label="单位" width="80" />
				<el-table-column label="退货数量" width="140">
					<template #default="{ row }">
						<el-input-number v-model="row.quantity" :min="0" :precision="4" controls-position="right" :disabled="editDialog.readonly" />
					</template>
				</el-table-column>
				<el-table-column label="单价" width="130">
					<template #default="{ row }">
						<el-input-number v-model="row.price" :min="0" :precision="4" controls-position="right" :disabled="editDialog.readonly" />
					</template>
				</el-table-column>
				<el-table-column label="金额" width="120" align="right">
					<template #default="{ row }">
						<span>{{ formatMoney(Number(row.quantity || 0) * Number(row.price || 0)) }}</span>
					</template>
				</el-table-column>
				<el-table-column label="备注" min-width="160">
					<template #default="{ row }">
						<el-input v-model="row.remark" :disabled="editDialog.readonly" />
					</template>
				</el-table-column>
			</el-table>

			<template #footer>
				<el-button @click="editDialog.visible = false">关闭</el-button>
				<el-button v-if="!editDialog.readonly" type="primary" :loading="editDialog.saving" @click="submitSave">保存</el-button>
			</template>
		</el-dialog>
	</div>
</template>

<script setup lang="ts" name="salesProductReturnPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
	useSalesProductReturnApi,
	type SalesProductReturnCandidateOutbound,
	type SalesProductReturnDetailData,
	type SalesProductReturnListItem,
} from '/@/api/sales/productReturn';
import { useSalesDeliveryApi, type SalesDeliveryDetailData } from '/@/api/sales/delivery';

type EditItem = {
	returnItemId?: number;
	salesOrderItemId: number;
	productCode: string;
	productName: string;
	productSpec: string;
	warehouseName: string;
	unitName: string;
	quantity: number;
	price: number;
	remark: string;
};

const salesProductReturnApi = useSalesProductReturnApi();
const salesDeliveryApi = useSalesDeliveryApi();

const bootstrap = reactive({
	candidateOutbounds: [] as SalesProductReturnCandidateOutbound[],
});

const filterForm = reactive({
	customerName: '',
	productName: '',
	productSpec: '',
	actualStockinDate: '',
	remark: '',
	auditState: '' as number | '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesProductReturnListItem[]>([]);
const selectedRows = ref<SalesProductReturnListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const createEmptyForm = () => ({
	returnId: 0,
	relatedOutboundId: null as number | null,
	relatedOutboundNo: '',
	customerName: '',
	returnType: 1,
	actualStockinDate: new Date().toISOString().slice(0, 10),
	remark: '',
	items: [] as EditItem[],
});

const editDialog = reactive({
	visible: false,
	readonly: false,
	saving: false,
	title: '添加退货单',
	form: createEmptyForm(),
});

const loadBootstrap = async () => {
	const response = await salesProductReturnApi.getBootstrap();
	bootstrap.candidateOutbounds = response.data.candidate_outbounds ?? [];
};

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesProductReturnApi.getList({
			customer_name: filterForm.customerName || undefined,
			product_name: filterForm.productName || undefined,
			product_spec: filterForm.productSpec || undefined,
			actual_stockin_date: filterForm.actualStockinDate || undefined,
			remark: filterForm.remark || undefined,
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

const onSelectionChange = (rows: SalesProductReturnListItem[]) => {
	selectedRows.value = rows;
};

const resetDialog = () => {
	editDialog.form = createEmptyForm();
	editDialog.readonly = false;
	editDialog.saving = false;
};

const mapDeliveryItems = (detail: SalesDeliveryDetailData) => {
	editDialog.form.customerName = detail.header.customer_name || '';
	editDialog.form.relatedOutboundNo = detail.header.outbound_no || '';
	editDialog.form.items = detail.items.map((item) => ({
		salesOrderItemId: item.sales_order_item_id,
		productCode: item.product_code,
		productName: item.product_name,
		productSpec: item.product_spec,
		warehouseName: item.warehouse_name,
		unitName: item.unit_name,
		quantity: Number(item.outbound_quantity || 0),
		price: Number(item.tax_price || item.price || 0),
		remark: item.remark || '',
	}));
};

const onCandidateOutboundChange = async (outboundId: number) => {
	if (!outboundId) return;
	const response = await salesDeliveryApi.getDetail(outboundId);
	mapDeliveryItems(response.data as SalesDeliveryDetailData);
};

const openCreateDialog = async (returnType: number) => {
	resetDialog();
	editDialog.title = returnType === 2 ? '添加出库退货' : '添加入库退货';
	editDialog.form.returnType = returnType;
	editDialog.visible = true;
	await loadBootstrap();
};

const openEditDialog = async (row: SalesProductReturnListItem) => {
	const response = await salesProductReturnApi.getDetail(row.return_id);
	const detail = response.data as SalesProductReturnDetailData;
	editDialog.title = '编辑退货单';
	editDialog.readonly = false;
	editDialog.form = {
		returnId: detail.header.return_id,
		relatedOutboundId: detail.header.related_outbound_id,
		relatedOutboundNo: detail.header.related_outbound_no,
		customerName: detail.header.customer_name,
		returnType: detail.header.return_type,
		actualStockinDate: detail.header.actual_stockin_date || '',
		remark: detail.header.remark || '',
		items: detail.items.map((item) => ({
			returnItemId: item.return_item_id,
			salesOrderItemId: item.sales_order_item_id,
			productCode: item.product_code,
			productName: item.product_name,
			productSpec: item.product_spec,
			warehouseName: item.warehouse_name,
			unitName: item.unit_name,
			quantity: Number(item.quantity || 0),
			price: Number(item.price || 0),
			remark: item.remark || '',
		})),
	};
	editDialog.visible = true;
};

const openDetailDialog = async (row: SalesProductReturnListItem) => {
	await openEditDialog(row);
	editDialog.title = '查看退货单';
	editDialog.readonly = true;
};

const validateEditForm = () => {
	if (!editDialog.form.relatedOutboundId) {
		ElMessage.warning('请选择来源出库单');
		return false;
	}
	if (editDialog.form.items.length === 0) {
		ElMessage.warning('请先带出退货明细');
		return false;
	}
	if (editDialog.form.items.some((item) => !item.salesOrderItemId || Number(item.quantity) <= 0)) {
		ElMessage.warning('退货数量必须大于 0');
		return false;
	}
	return true;
};

const submitSave = async () => {
	if (!validateEditForm()) return;
	editDialog.saving = true;
	try {
		await salesProductReturnApi.save({
			return_id: editDialog.form.returnId || undefined,
			related_outbound_id: editDialog.form.relatedOutboundId as number,
			return_type: editDialog.form.returnType,
			actual_stockin_date: editDialog.form.actualStockinDate || undefined,
			remark: editDialog.form.remark || undefined,
			items: editDialog.form.items.map((item) => ({
				return_item_id: item.returnItemId,
				sales_order_item_id: item.salesOrderItemId,
				quantity: item.quantity,
				price: item.price,
				remark: item.remark || undefined,
			})),
		});
		ElMessage.success('保存成功');
		editDialog.visible = false;
		await loadList();
	} finally {
		editDialog.saving = false;
	}
};

const resolveTargetRow = (row?: SalesProductReturnListItem) => row ?? selectedRows.value[0];

const onAuditPass = async (row?: SalesProductReturnListItem) => {
	const target = resolveTargetRow(row);
	if (!target) {
		ElMessage.warning('请选择一条退货单');
		return;
	}
	await salesProductReturnApi.auditPass(target.return_id);
	ElMessage.success('审核通过成功');
	await loadList();
};

const onReverseAudit = async (row?: SalesProductReturnListItem) => {
	const target = resolveTargetRow(row);
	if (!target) {
		ElMessage.warning('请选择一条退货单');
		return;
	}
	await salesProductReturnApi.reverseAudit(target.return_id);
	ElMessage.success('反审核成功');
	await loadList();
};

const onBatchDelete = async () => {
	if (selectedRows.value.length === 0) {
		ElMessage.warning('请先勾选退货单');
		return;
	}
	await ElMessageBox.confirm(`确认删除已选中的 ${selectedRows.value.length} 条退货单吗？`, '批量删除', { type: 'warning' });
	await salesProductReturnApi.batchDelete(selectedRows.value.map((item) => item.return_id));
	ElMessage.success('删除成功');
	await loadList();
};

const auditTagType = (state: number) => {
	if (state === 1) return 'success';
	if (state === 2) return 'warning';
	return 'danger';
};

const formatMoney = (value: number) => value.toFixed(2);

onMounted(async () => {
	try {
		await Promise.all([loadBootstrap(), loadList()]);
	} catch (error) {
		ElMessage.error('退货数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-product-return-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.page-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;

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
			flex-wrap: wrap;
			justify-content: flex-end;
			gap: 10px;
		}
	}

	.filter-card,
	.table-card {
		border: 1px solid #e7ebf1;
		box-shadow: none;
	}

	:deep(.filter-card .el-card__body) {
		padding-top: 10px;
	}

	:deep(.table-card .el-card__body) {
		padding: 0 10px 12px;
	}

	.card-title {
		font-size: 16px;
		font-weight: 600;
		color: #344054;
	}

	.filter-form {
		display: grid;
		grid-template-columns: repeat(6, minmax(0, 1fr));
		gap: 6px 14px;

		&__action {
			display: flex;
			align-items: flex-end;
		}
	}

	.table-actions {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 8px;
		white-space: nowrap;
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

	.edit-form {
		margin-bottom: 14px;

		&__grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 0 12px;
		}
	}
}

@media (max-width: 1200px) {
	.sales-product-return-page {
		.filter-form {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}

		.edit-form__grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
}

@media (max-width: 768px) {
	.sales-product-return-page {
		.page-header {
			flex-direction: column;
			align-items: flex-start;
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
