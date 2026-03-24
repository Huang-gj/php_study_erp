import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import { resolve } from 'path';
import { defineConfig, loadEnv, ConfigEnv } from 'vite';
import vueSetupExtend from 'vite-plugin-vue-setup-extend-plus';
import viteCompression from 'vite-plugin-compression';
import { buildConfig } from './src/utils/build';

const pathResolve = (dir: string) => {
	return resolve(__dirname, '.', dir);
};

const alias: Record<string, string> = {
	'/@': pathResolve('./src/'),
	'vue-i18n': 'vue-i18n/dist/vue-i18n.cjs.js',
};

const deployTargetFile = resolve(__dirname, '../deploy.target');

const getDeployTarget = () => {
	try {
		const deployTarget = fs.readFileSync(deployTargetFile, 'utf-8').trim();
		return deployTarget === 'local' ? 'local' : 'server';
	} catch (error) {
		return 'server';
	}
};

const viteConfig = defineConfig((mode: ConfigEnv) => {
	const env = loadEnv(mode.mode, process.cwd());
	const envDeployTarget = env.VITE_DEPLOY_TARGET === 'local' || env.VITE_DEPLOY_TARGET === 'server' ? env.VITE_DEPLOY_TARGET : '';
	const deployTarget = envDeployTarget || (mode.mode === 'production' ? 'server' : getDeployTarget());
	const apiTarget = deployTarget === 'local' ? env.VITE_LOCAL_API_TARGET : env.VITE_SERVER_API_TARGET;
	const loginIpFallback = new URL(apiTarget).hostname;

	return {
		plugins: [vue(), vueSetupExtend(), viteCompression(), JSON.parse(env.VITE_OPEN_CDN) ? buildConfig.cdn() : null],
		root: process.cwd(),
		resolve: { alias },
		base: mode.command === 'serve' ? './' : env.VITE_PUBLIC_PATH,
		optimizeDeps: { exclude: ['vue-demi'] },
		server: {
			host: '0.0.0.0',
			port: env.VITE_PORT as unknown as number,
			open: JSON.parse(env.VITE_OPEN),
			hmr: true,
			proxy: {
				'/admin': {
					target: apiTarget,
					changeOrigin: true,
				},
				'/sales': {
					target: apiTarget,
					changeOrigin: true,
				},
				'/gitee': {
					target: 'https://gitee.com',
					ws: true,
					changeOrigin: true,
					rewrite: (path) => path.replace(/^\/gitee/, ''),
				},
			},
		},
		build: {
			outDir: 'dist',
			chunkSizeWarningLimit: 1500,
			rollupOptions: {
				output: {
					chunkFileNames: 'assets/js/[name]-[hash].js',
					entryFileNames: 'assets/js/[name]-[hash].js',
					assetFileNames: 'assets/[ext]/[name]-[hash].[ext]',
					manualChunks(id) {
						if (id.includes('node_modules')) {
							return id.toString().match(/\/node_modules\/(?!.pnpm)(?<moduleName>[^\/]*)\//)?.groups!.moduleName ?? 'vender';
						}
					},
				},
				...(JSON.parse(env.VITE_OPEN_CDN) ? { external: buildConfig.external } : {}),
			},
		},
		css: { preprocessorOptions: { css: { charset: false } } },
		define: {
			__API_BASE_URL__: JSON.stringify(mode.command === 'serve' ? '/' : apiTarget),
			__API_TARGET__: JSON.stringify(apiTarget),
			__DEPLOY_TARGET__: JSON.stringify(deployTarget),
			__LOGIN_IP_FALLBACK__: JSON.stringify(loginIpFallback),
			__VUE_I18N_LEGACY_API__: JSON.stringify(false),
			__VUE_I18N_FULL_INSTALL__: JSON.stringify(false),
			__INTLIFY_PROD_DEVTOOLS__: JSON.stringify(false),
			__NEXT_VERSION__: JSON.stringify(process.env.npm_package_version),
			__NEXT_NAME__: JSON.stringify(process.env.npm_package_name),
		},
	};
});

export default viteConfig;
