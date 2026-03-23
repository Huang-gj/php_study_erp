<template>
	<div class="sales-report-page layout-padding">
		<section class="report-header">
			<div class="report-header__title">
				<el-icon><ele-DArrowRight /></el-icon>
				<span>订单情况列表</span>
			</div>
			<el-button plain class="report-header__export">导出excel</el-button>
		</section>

		<section class="summary-grid">
			<article v-for="card in summaryCards" :key="card.title" class="summary-card" :style="{ '--card-gradient': card.gradient }">
				<div class="summary-card__content">
					<p class="summary-card__title">{{ card.title }}</p>
					<h2 class="summary-card__value">{{ card.value }}</h2>
				</div>
				<div class="summary-card__stack" aria-hidden="true">
					<span></span>
					<span></span>
					<span></span>
					<span></span>
				</div>
			</article>
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
				<el-form-item label="产品规格">
					<el-input v-model="filterForm.productSpec" placeholder="请输入" clearable />
				</el-form-item>
				<el-form-item label="订单日期">
					<el-date-picker v-model="filterForm.orderDate" type="date" placeholder="请选择" value-format="YYYY-MM-DD" />
				</el-form-item>
				<el-form-item class="filter-form__action">
					<el-button type="primary" class="search-btn" :loading="tableLoading" @click="onSearch">
						<el-icon><ele-Search /></el-icon>
						搜索
					</el-button>
				</el-form-item>
			</el-form>
		</el-card>

		<el-card shadow="never" class="table-card">
			<el-table :data="tableRows" class="report-table" v-loading="tableLoading">
				<el-table-column prop="id" label="ID" width="80" />
				<el-table-column label="操作" width="92" fixed="left">
					<template #default>
						<el-button type="success" size="small" class="view-btn">查看</el-button>
					</template>
				</el-table-column>
				<el-table-column prop="audit_status" label="审核状态" width="156" />
				<el-table-column prop="contract_text" label="编号" min-width="240" show-overflow-tooltip />
				<el-table-column prop="customer_info" label="客户信息" min-width="340" show-overflow-tooltip />
				<el-table-column prop="order_date" label="订单日期" width="132" />
				<el-table-column prop="delivery_date" label="交货日期" width="132" />
				<el-table-column prop="product_name" label="名称" min-width="260" show-overflow-tooltip />
				<el-table-column prop="specification" label="规格" width="118" />
				<el-table-column prop="order_quantity" label="订单数量" width="106" align="center" />
				<el-table-column prop="unit_price" label="单价" width="88" align="center" />
				<el-table-column prop="ship_quantity" label="发货数量" width="108" align="center" />
				<el-table-column prop="load_date" label="装车日期" width="108" />
			</el-table>

			<div class="table-footer">
				<div class="table-footer__total">总记录：{{ pagination.total }}</div>
				<el-pagination
					@current-change="onPageChange"
					background
					layout="prev, pager, next"
					:page-size="pagination.pageSize"
					:total="pagination.total"
					:pager-count="7"
					:model-value="pagination.page"
				/>
			</div>
		</el-card>
	</div>
</template>

<script setup lang="ts" name="salesReportPage">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useSalesReportApi, type SalesOrderReportItem } from '/@/api/sales/index';

const salesReportApi = useSalesReportApi();

const summaryCards = ref([
	{
		title: '订单总量',
		value: '0',
		gradient: 'linear-gradient(135deg, #379fe0 0%, #56c6c7 100%)',
	},
	{
		title: '当天完工总量',
		value: '0',
		gradient: 'linear-gradient(135deg, #f7318d 0%, #ff6c79 100%)',
	},
	{
		title: '生产总量',
		value: '0',
		gradient: 'linear-gradient(135deg, #945acb 0%, #b53ed8 100%)',
	},
	{
		title: '完工总量',
		value: '0',
		gradient: 'linear-gradient(135deg, #f7a11a 0%, #ffd414 100%)',
	},
]);

const filterForm = reactive({
	contractNo: '',
	customerName: '',
	productSpec: '',
	orderDate: '',
});

const tableLoading = ref(false);
const tableRows = ref<SalesOrderReportItem[]>([]);
const pagination = reactive({
	page: 1,
	pageSize: 10,
	total: 0,
});

const loadSummary = async () => {
	const response = await salesReportApi.getSummary();
	summaryCards.value = [
		{
			title: '订单总量',
			value: String(response.data.order_total ?? 0),
			gradient: 'linear-gradient(135deg, #379fe0 0%, #56c6c7 100%)',
		},
		{
			title: '当天完工总量',
			value: String(response.data.today_finished_total ?? 0),
			gradient: 'linear-gradient(135deg, #f7318d 0%, #ff6c79 100%)',
		},
		{
			title: '生产总量',
			value: String(response.data.production_total ?? 0),
			gradient: 'linear-gradient(135deg, #945acb 0%, #b53ed8 100%)',
		},
		{
			title: '完工总量',
			value: String(response.data.completed_total ?? 0),
			gradient: 'linear-gradient(135deg, #f7a11a 0%, #ffd414 100%)',
		},
	];
};

const loadOrderList = async () => {
	tableLoading.value = true;
	try {
		const response = await salesReportApi.getOrderList({
			contract_no: filterForm.contractNo,
			customer_name: filterForm.customerName,
			product_spec: filterForm.productSpec,
			order_date: filterForm.orderDate,
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
	loadOrderList();
};

const onPageChange = (page: number) => {
	pagination.page = page;
	loadOrderList();
};

onMounted(async () => {
	try {
		await Promise.all([loadSummary(), loadOrderList()]);
	} catch (error) {
		ElMessage.error('销售报表数据加载失败');
	}
});
</script>

<style scoped lang="scss">
.sales-report-page {
	display: flex;
	flex-direction: column;
	gap: 12px;
	background: #f5f7fb;
	min-height: 100%;

	.report-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 2px 0 0;

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

	.summary-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 14px;
	}

	.summary-card {
		position: relative;
		display: flex;
		align-items: center;
		justify-content: space-between;
		min-height: 104px;
		padding: 14px 20px;
		border-radius: 4px;
		background: var(--card-gradient);
		color: #fff;
		overflow: hidden;
		box-shadow: 0 16px 30px rgba(45, 82, 130, 0.12);

		&::after {
			content: '';
			position: absolute;
			inset: 0;
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 65%);
			pointer-events: none;
		}

		&__content {
			position: relative;
			z-index: 1;
		}

		&__title {
			margin: 0 0 12px;
			font-size: 15px;
			font-weight: 500;
			opacity: 0.96;
		}

		&__value {
			margin: 0;
			font-size: 42px;
			line-height: 1;
			font-weight: 500;
			letter-spacing: 1px;
		}

		&__stack {
			position: relative;
			width: 82px;
			height: 72px;
			flex-shrink: 0;
			opacity: 0.32;
			transform: translateY(4px);

			span {
				position: absolute;
				right: 0;
				width: 58px;
				height: 28px;
				border-radius: 5px;
				background: rgba(255, 255, 255, 0.86);
				clip-path: polygon(50% 0%, 100% 35%, 50% 70%, 0% 35%);
				box-shadow: 0 12px 24px rgba(255, 255, 255, 0.1);
			}

			span:nth-child(1) {
				top: 0;
			}

			span:nth-child(2) {
				top: 16px;
			}

			span:nth-child(3) {
				top: 32px;
			}

			span:nth-child(4) {
				top: 48px;
			}
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
		padding: 12px 16px 14px;
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
		grid-template-columns: 1.1fr 1.1fr 1.1fr 1fr auto;
		gap: 10px;
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
			height: 36px;
			box-shadow: none;
			border: 1px solid #dfe5ee;
		}

		.search-btn {
			height: 36px;
			padding: 0 16px;
			border: none;
			background: #f5f7fb;
			color: #566172;
		}

		&__action {
			align-self: end;
		}
	}

	.report-table {
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
			padding: 9px 0;
			color: #606978;
			font-size: 13px;
			vertical-align: top;
		}

		:deep(.el-table__body tr:hover > td) {
			background: #f9fbff;
		}
	}

	.view-btn {
		border: none;
		background: #0ea79b;
	}

	.table-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding-top: 10px;

		&__total {
			font-size: 15px;
			color: #505766;
		}
	}
}

@media (max-width: 1680px) {
	.sales-report-page {
		.summary-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.filter-form {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
	}
}

@media (max-width: 992px) {
	.sales-report-page {
		.summary-grid,
		.filter-form {
			grid-template-columns: 1fr;
		}

		.summary-card {
			min-height: 146px;
			padding: 18px;

			&__value {
				font-size: 40px;
			}
		}

		.table-footer {
			flex-direction: column;
			align-items: flex-start;
			gap: 12px;
		}
	}
}
</style>
