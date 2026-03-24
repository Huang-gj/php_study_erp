<template>
	<div class="sales-progress-page layout-padding">
		<section class="progress-header">
			<div class="progress-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>销售单列表</span>
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
				<el-form-item class="filter-form__action">
					<el-button type="primary" :loading="tableLoading" @click="onSearch">
						<el-icon><ele-Search /></el-icon>
						搜索
					</el-button>
				</el-form-item>
			</el-form>
		</el-card>

		<el-card shadow="never" class="table-card">
			<el-table :data="tableRows" v-loading="tableLoading" class="progress-table">
				<el-table-column label="操作" width="92" fixed="left">
					<template #default="{ row }">
						<el-button type="success" size="small" class="view-btn" @click="openDetail(row)">查看</el-button>
					</template>
				</el-table-column>
				<el-table-column prop="id" label="ID" width="80" />
				<el-table-column label="审核状态" width="140">
					<template #default="{ row }">
						<div class="audit-status-cell">
							<el-tag :type="tagTypeByOrderState(row.order_state)" effect="light">{{ row.audit_status }}</el-tag>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="合同编号" min-width="106">
					<template #default="{ row }">
						<div class="contract-cell">
							<div>{{ row.contract_no }}</div>
							<div v-if="row.reconcile_state === 1" class="contract-cell__flag">已对账</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column prop="customer_name" label="客户名称" min-width="240" show-overflow-tooltip />
				<el-table-column prop="order_date" label="订单日期" width="132" />
				<el-table-column prop="delivery_date" label="交货日期" width="132">
					<template #default="{ row }">
						<span class="delivery-date">{{ row.delivery_date || '-' }}</span>
					</template>
				</el-table-column>
				<el-table-column prop="sales_total_price" label="销售总价" width="112" />
				<el-table-column prop="drawer_user_name" label="开单员" width="122" />
				<el-table-column prop="remark" label="备注" min-width="220" show-overflow-tooltip />
				<el-table-column label="进度" min-width="520" fixed="right">
					<template #default="{ row }">
						<div class="progress-track">
							<div v-for="(step, index) in row.steps" :key="step.step_code" class="progress-track__item">
								<div class="progress-track__line" v-if="index > 0"></div>
								<div class="progress-track__circle" :class="circleClass(step)">
									<template v-if="step.step_state === 2 && step.step_code === 1">
										<el-icon><ele-Check /></el-icon>
									</template>
									<template v-else>{{ step.step_code }}</template>
								</div>
								<div class="progress-track__label">{{ step.step_label }}</div>
							</div>
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

		<el-drawer v-model="detailDrawer.visible" size="56%" title="销售进度详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="2" border>
					<el-descriptions-item label="合同编号">{{ detailDrawer.data.header.contract_no }}</el-descriptions-item>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.header.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="订单日期">{{ detailDrawer.data.header.order_date }}</el-descriptions-item>
					<el-descriptions-item label="交货日期">{{ detailDrawer.data.header.delivery_date || '-' }}</el-descriptions-item>
					<el-descriptions-item label="销售总价">{{ detailDrawer.data.header.sales_total_price }}</el-descriptions-item>
					<el-descriptions-item label="开单员">{{ detailDrawer.data.header.drawer_user_name || '-' }}</el-descriptions-item>
					<el-descriptions-item label="审核状态">{{ detailDrawer.data.header.audit_status }}</el-descriptions-item>
					<el-descriptions-item label="订单状态">{{ detailDrawer.data.header.order_state_text }}</el-descriptions-item>
					<el-descriptions-item label="对账状态">{{ detailDrawer.data.header.reconcile_state_text }}</el-descriptions-item>
					<el-descriptions-item label="备注" :span="2">{{ detailDrawer.data.header.remark || '-' }}</el-descriptions-item>
				</el-descriptions>

				<el-divider content-position="left">进度日志</el-divider>
				<el-timeline v-if="detailDrawer.data.logs.length > 0" class="detail-timeline">
					<el-timeline-item
						v-for="log in detailDrawer.data.logs"
						:key="log.progress_log_id"
						:timestamp="log.finish_time || log.start_time || '待更新'"
						:type="timelineType(log.step_state)"
					>
						<div class="timeline-item">
							<div class="timeline-item__head">
								<strong>{{ log.step_name }}</strong>
								<el-tag size="small" effect="plain">{{ log.step_state_text }}</el-tag>
							</div>
							<div class="timeline-item__meta">操作人：{{ log.operator_user_name || '系统' }}</div>
							<div class="timeline-item__meta" v-if="log.related_no">关联单号：{{ log.related_no }}</div>
							<div class="timeline-item__remark">{{ log.remark || '暂无备注' }}</div>
						</div>
					</el-timeline-item>
				</el-timeline>
				<el-empty v-else description="暂无进度记录" />
			</div>
		</el-drawer>
	</div>
</template>

<script setup lang="ts" name="salesProgressPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesProgressApi, type SalesProgressDetailData, type SalesProgressListItem, type SalesProgressStepItem } from '/@/api/sales/progress';

const salesProgressApi = useSalesProgressApi();

const filterForm = reactive({
	contractNo: '',
	customerName: '',
	productName: '',
	productSpec: '',
	orderDate: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesProgressListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesProgressDetailData | null,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesProgressApi.getList({
			contract_no: filterForm.contractNo || undefined,
			customer_name: filterForm.customerName || undefined,
			product_name: filterForm.productName || undefined,
			product_spec: filterForm.productSpec || undefined,
			order_date: filterForm.orderDate || undefined,
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

const openDetail = async (row: SalesProgressListItem) => {
	const response = await salesProgressApi.getDetail(row.sales_order_id);
	detailDrawer.data = response.data as SalesProgressDetailData;
	detailDrawer.visible = true;
};

const tagTypeByOrderState = (orderState: number) => {
	if (orderState === 7) {
		return 'success';
	}
	if (orderState === 5 || orderState === 6) {
		return 'warning';
	}
	if (orderState === 8) {
		return 'danger';
	}
	return 'info';
};

const circleClass = (step: SalesProgressStepItem) => {
	if (step.step_state === 2) {
		return step.step_code === 5 ? 'is-current is-final' : 'is-completed';
	}
	if (step.step_state === 1) {
		return 'is-current';
	}
	return 'is-pending';
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
		await loadList();
	} catch (error) {
		ElMessage.error('销售进度数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-progress-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.progress-header {
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

	.progress-table {
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
			padding: 14px 0;
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

	.audit-status-cell {
		display: flex;
		align-items: center;
	}

	.contract-cell {
		display: flex;
		flex-direction: column;
		gap: 8px;

		&__flag {
			display: inline-flex;
			width: fit-content;
			padding: 3px 8px;
			border: 1px solid #ef5a5a;
			border-radius: 4px;
			color: #ef5a5a;
			font-weight: 600;
			line-height: 1;
		}
	}

	.delivery-date {
		color: #ff5757;
	}

	.progress-track {
		display: flex;
		align-items: flex-start;
		padding: 0 14px 0 8px;

		&__item {
			position: relative;
			flex: 1;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 8px;
		}

		&__line {
			position: absolute;
			top: 18px;
			right: calc(50% + 18px);
			width: calc(100% - 36px);
			height: 1px;
			background: #20b7b1;
		}

		&__circle {
			position: relative;
			z-index: 1;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 36px;
			height: 36px;
			border-radius: 50%;
			border: 1px solid #20b7b1;
			background: #fff;
			color: #20b7b1;
			font-size: 16px;
			font-weight: 700;

			&.is-completed,
			&.is-current {
				background: #13a79f;
				border-color: #13a79f;
				color: #fff;
			}

			&.is-final {
				background: #13a79f;
				border-color: #13a79f;
				color: #fff;
			}
		}

		&__label {
			font-size: 14px;
			font-weight: 600;
			color: #4f5666;
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

	.detail-timeline {
		padding: 10px 8px;
	}

	.timeline-item {
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
}

@media (max-width: 1480px) {
	.sales-progress-page {
		.filter-form {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
	}
}

@media (max-width: 900px) {
	.sales-progress-page {
		.table-footer,
		.filter-form {
			grid-template-columns: 1fr;
		}

		.table-footer {
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}
	}
}
</style>
