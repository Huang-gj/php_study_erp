<template>
	<div class="sales-arrears-page layout-padding">
		<section class="page-header">
			<div class="page-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>客户欠款统计</span>
			</div>
		</section>

		<el-card shadow="never" class="table-card">
			<el-table :data="tableRows" v-loading="tableLoading" class="arrears-table">
				<el-table-column prop="rank_no" label="序号" width="90" />
				<el-table-column prop="customer_name" label="用户" min-width="320" show-overflow-tooltip />
				<el-table-column label="操作" width="110" fixed="right">
					<template #default="{ row }">
						<el-button type="success" size="small" @click="openDetail(row)">查看</el-button>
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

		<el-drawer v-model="detailDrawer.visible" size="46%" title="欠款详情" destroy-on-close>
			<div v-if="detailDrawer.data" class="detail-panel">
				<el-descriptions :column="1" border>
					<el-descriptions-item label="客户名称">{{ detailDrawer.data.customer_name }}</el-descriptions-item>
					<el-descriptions-item label="欠款总额">{{ detailDrawer.data.total_unpaid_amount }}</el-descriptions-item>
				</el-descriptions>

				<el-table :data="detailDrawer.data.order_list" class="detail-table">
					<el-table-column prop="contract_no" label="合同编号" min-width="150" />
					<el-table-column prop="order_date" label="订单日期" width="120" />
					<el-table-column prop="delivery_date" label="交货日期" width="120" />
					<el-table-column prop="total_tax_amount" label="订单总价" width="110" align="right" />
					<el-table-column prop="received_amount" label="已收金额" width="110" align="right" />
					<el-table-column prop="unpaid_amount" label="欠款金额" width="110" align="right" />
				</el-table>
			</div>
		</el-drawer>
	</div>
</template>

<script setup lang="ts" name="salesArrearsPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesArrearsApi, type SalesArrearsDetailData, type SalesArrearsListItem } from '/@/api/sales/arrears';

const salesArrearsApi = useSalesArrearsApi();

const tableLoading = ref(false);
const tableRows = ref<SalesArrearsListItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const detailDrawer = reactive({
	visible: false,
	data: null as SalesArrearsDetailData | null,
});

const loadList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesArrearsApi.getList({
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

const onPageChange = (page: number) => {
	pagination.page = page;
	loadList();
};

const openDetail = async (row: SalesArrearsListItem) => {
	const response = await salesArrearsApi.getDetail(row.customer_id);
	detailDrawer.data = response.data as SalesArrearsDetailData;
	detailDrawer.visible = true;
};

onMounted(async () => {
	try {
		await loadList();
	} catch (error) {
		ElMessage.error('欠款统计数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-arrears-page {
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

	.table-card {
		border: 1px solid #e7ebf1;
		box-shadow: none;
	}

	:deep(.table-card .el-card__body) {
		padding: 0 10px 12px;
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

	.detail-panel {
		display: flex;
		flex-direction: column;
		gap: 16px;
	}
}

@media (max-width: 768px) {
	.sales-arrears-page .table-footer {
		flex-direction: column;
		align-items: flex-start;
		gap: 10px;
	}
}
</style>
