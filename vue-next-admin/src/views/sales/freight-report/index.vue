<template>
	<div class="sales-freight-report-page layout-padding">
		<section class="page-header">
			<div class="page-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>运费报表</span>
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
				<el-form-item label="单据日期">
					<el-date-picker v-model="filterForm.documentDate" type="date" value-format="YYYY-MM-DD" placeholder="请选择日期" />
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
			<el-table :data="tableRows" v-loading="tableLoading" class="report-table">
				<el-table-column prop="id" label="ID" width="86" />
				<el-table-column prop="customer_name" label="客户名称" min-width="220" show-overflow-tooltip />
				<el-table-column prop="outbound_no" label="单据编号" min-width="180">
					<template #default="{ row }">
						<span class="outbound-no">{{ row.outbound_no }}</span>
					</template>
				</el-table-column>
				<el-table-column prop="document_date" label="单据日期" width="130" />
				<el-table-column prop="express_no" label="快递单号" min-width="160" show-overflow-tooltip />
				<el-table-column prop="logistics_fee" label="运输费用" min-width="140" show-overflow-tooltip />
				<el-table-column prop="driver_name" label="司机姓名" width="120" />
				<el-table-column prop="vehicle_no" label="车牌号" width="140" />
				<el-table-column prop="ship_date" label="发货日期" width="130" />
				<el-table-column prop="maker_user_name" label="制单人" width="110" />
				<el-table-column prop="create_time" label="创建时间" width="130" />
				<el-table-column label="操作" width="90" fixed="right">
					<template #default="{ row }">
						<div class="table-actions">
							<el-button type="success" size="small" @click="openEditDialog(row)">编辑</el-button>
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

		<el-dialog v-model="editDialog.visible" title="编辑运费信息" width="540px" destroy-on-close>
			<el-form :model="editDialog.form" label-width="96px">
				<el-form-item label="单据编号">
					<el-input v-model="editDialog.form.outboundNo" disabled />
				</el-form-item>
				<el-form-item label="客户名称">
					<el-input v-model="editDialog.form.customerName" disabled />
				</el-form-item>
				<el-form-item label="快递单号">
					<el-input v-model="editDialog.form.expressNo" />
				</el-form-item>
				<el-form-item label="运输费用">
					<el-input v-model="editDialog.form.logisticsFee" placeholder="请输入运输费用" />
				</el-form-item>
				<el-form-item label="司机姓名">
					<el-input v-model="editDialog.form.driverName" />
				</el-form-item>
				<el-form-item label="车牌号">
					<el-input v-model="editDialog.form.vehicleNo" />
				</el-form-item>
			</el-form>
			<template #footer>
				<el-button @click="editDialog.visible = false">取消</el-button>
				<el-button type="primary" :loading="editDialog.saving" @click="submitUpdate">保存</el-button>
			</template>
		</el-dialog>
	</div>
</template>

<script setup lang="ts" name="salesFreightReportPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesFreightReportApi, type SalesFreightReportListItem } from '/@/api/sales/freightReport';

const salesFreightReportApi = useSalesFreightReportApi();

const filterForm = reactive({
	outboundNo: '',
	customerName: '',
	documentDate: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesFreightReportListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 20,
	total: 0,
});

const editDialog = reactive({
	visible: false,
	saving: false,
	form: {
		outboundId: 0,
		outboundNo: '',
		customerName: '',
		expressNo: '',
		logisticsFee: '',
		driverName: '',
		vehicleNo: '',
	},
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesFreightReportApi.getList({
			outbound_no: filterForm.outboundNo || undefined,
			customer_name: filterForm.customerName || undefined,
			document_date: filterForm.documentDate || undefined,
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

const openEditDialog = (row: SalesFreightReportListItem) => {
	editDialog.form = {
		outboundId: row.outbound_id,
		outboundNo: row.outbound_no,
		customerName: row.customer_name,
		expressNo: row.express_no || '',
		logisticsFee: row.logistics_fee || '',
		driverName: row.driver_name || '',
		vehicleNo: row.vehicle_no || '',
	};
	editDialog.visible = true;
};

const submitUpdate = async () => {
	editDialog.saving = true;
	try {
		await salesFreightReportApi.update({
			outbound_id: editDialog.form.outboundId,
			express_no: editDialog.form.expressNo,
			logistics_fee: editDialog.form.logisticsFee,
			driver_name: editDialog.form.driverName,
			vehicle_no: editDialog.form.vehicleNo,
		});
		ElMessage.success('保存成功');
		editDialog.visible = false;
		await loadList();
	} finally {
		editDialog.saving = false;
	}
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('运费报表数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-freight-report-page {
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
		grid-template-columns: minmax(0, 320px) minmax(0, 320px) minmax(0, 320px) auto;
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
		:deep(.el-date-editor.el-input) {
			height: 40px;
		}
	}

	.outbound-no {
		color: #2d7df4;
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
	.sales-freight-report-page .filter-form {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 768px) {
	.sales-freight-report-page {
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
