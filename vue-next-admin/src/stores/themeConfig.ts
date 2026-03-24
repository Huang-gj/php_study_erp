import { defineStore } from 'pinia';

// Keep comments ASCII-only to avoid encoding issues in mixed environments.
export const useThemeConfig = defineStore('themeConfig', {
	state: (): ThemeConfigState => ({
		themeConfig: {
			isDrawer: false,

			// Global theme
			primary: '#409eff',
			isIsDark: false,

			// Top bar
			topBar: '#ffffff',
			topBarColor: '#303133',
			isTopBarColorGradual: false,

			// Side menu
			menuBar: '#545c64',
			menuBarColor: '#eaeaea',
			menuBarActiveColor: 'rgba(0, 0, 0, 0.2)',
			isMenuBarColorGradual: false,

			// Columns menu
			columnsMenuBar: '#545c64',
			columnsMenuBarColor: '#e6e6e6',
			isColumnsMenuBarColorGradual: false,
			isColumnsMenuHoverPreload: false,

			// Layout behavior
			isCollapse: true,
			isUniqueOpened: true,
			isFixedHeader: false,
			isFixedHeaderChange: false,
			isClassicSplitMenu: true,
			isLockScreen: false,
			lockScreenTime: 30,

			// Visible modules
			isShowLogo: false,
			isShowLogoChange: false,
			isBreadcrumb: true,
			isTagsview: true,
			isBreadcrumbIcon: false,
			isTagsviewIcon: false,
			isCacheTagsView: false,
			isSortableTagsView: true,
			isShareTagsView: false,
			isFooter: false,
			isGrayscale: false,
			isInvert: false,
			isWartermark: false,
			wartermarkText: '东氩科技信息管理系统（仿制）',

			// Extra UI config
			tagsStyle: 'tags-style-five',
			animation: 'slide-right',
			columnsAsideStyle: 'columns-round',
			columnsAsideLayout: 'columns-vertical',

			// Main layout mode
			layout: 'classic',

			// Routing mode
			isRequestRoutes: false,

			// Site text
			globalTitle: '东氩科技信息管理系统（仿制）',
			globalViceTitle: '东氩科技信息管理系统（仿制）',
			globalViceTitleMsg: '东氩科技信息管理系统后台登录入口',
			globalI18n: 'zh-cn',
			globalComponentSize: 'large',
		},
	}),
	actions: {
		setThemeConfig(data: ThemeConfigState) {
			this.themeConfig = data.themeConfig;
		},
	},
});

