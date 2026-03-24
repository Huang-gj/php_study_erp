import table2excel from 'js-table2excel';

export interface ExportExcelColumn<T = Record<string, any>> {
	label: string;
	prop: keyof T | string;
}

export interface ExportExcelOptions<T = Record<string, any>> {
	filename: string;
	columns: ExportExcelColumn<T>[];
	data: T[];
}

export function exportExcel<T = Record<string, any>>(options: ExportExcelOptions<T>) {
	const column = options.columns.map((item) => ({
		title: item.label,
		key: item.prop as string,
		type: 'text',
	}));

	const payload = options.data.map((row) => {
		const nextRow: Record<string, any> = {};
		options.columns.forEach((item) => {
			nextRow[item.prop as string] = (row as Record<string, any>)[item.prop as string] ?? '';
		});
		return nextRow;
	});

	table2excel(column, payload, options.filename);
}
