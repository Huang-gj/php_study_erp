import { createRouter, createWebHashHistory } from 'vue-router';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import pinia from '/@/stores/index';
import { storeToRefs } from 'pinia';
import { useKeepALiveNames } from '/@/stores/keepAliveNames';
import { useRoutesList } from '/@/stores/routesList';
import { useThemeConfig } from '/@/stores/themeConfig';
import { Session } from '/@/utils/storage';
import { staticRoutes, notFoundAndNoPower } from '/@/router/route';
import { initFrontEndControlRoutes } from '/@/router/frontEnd';
import { initBackEndControlRoutes } from '/@/router/backEnd';

/**
 * 1銆佸墠绔帶鍒惰矾鐢辨椂锛歩sRequestRoutes 涓?false锛岄渶瑕佸啓 roles锛岄渶瑕佽蛋 setFilterRoute 鏂规硶銆?
 * 2銆佸悗绔帶鍒惰矾鐢辨椂锛歩sRequestRoutes 涓?true锛屼笉闇€瑕佸啓 roles锛屼笉闇€瑕佽蛋 setFilterRoute 鏂规硶锛夛紝
 * 鐩稿叧鏂规硶宸叉媶瑙ｅ埌瀵瑰簲鐨?`backEnd.ts` 涓?`frontEnd.ts`锛堜粬浠簰涓嶅奖鍝嶏紝涓嶉渶瑕佸悓鏃舵敼 2 涓枃浠讹級銆?
 * 鐗瑰埆璇存槑锛?
 * 1銆佸墠绔帶鍒讹細璺敱鑿滃崟鐢卞墠绔幓鍐欙紙鏃犺彍鍗曠鐞嗙晫闈紝鏈夎鑹茬鐞嗙晫闈級锛岃鑹茬鐞嗕腑鏈?roles 灞炴€э紝闇€杩斿洖鍒?userInfo 涓€?
 * 2銆佸悗绔帶鍒讹細璺敱鑿滃崟鐢卞悗绔繑鍥烇紙鏈夎彍鍗曠鐞嗙晫闈€佹湁瑙掕壊绠＄悊鐣岄潰锛?
 */

// 璇诲彇 `/src/stores/themeConfig.ts` 鏄惁寮€鍚悗绔帶鍒惰矾鐢遍厤缃?
const storesThemeConfig = useThemeConfig(pinia);
const { themeConfig } = storeToRefs(storesThemeConfig);
const { isRequestRoutes } = themeConfig.value;

/**
 * 鍒涘缓涓€涓彲浠ヨ Vue 搴旂敤绋嬪簭浣跨敤鐨勮矾鐢卞疄渚?
 * @method createRouter(options: RouterOptions): Router
 * @link 鍙傝€冿細https://next.router.vuejs.org/zh/api/#createrouter
 */
export const router = createRouter({
	history: createWebHashHistory(),
	/**
	 * 璇存槑锛?
	 * 1銆乶otFoundAndNoPower 榛樿娣诲姞 404銆?01 鐣岄潰锛岄槻姝竴鐩存彁绀?No match found for location with path 'xxx'
	 * 2銆乥ackEnd.ts(鍚庣鎺у埗璺敱)銆乫rontEnd.ts(鍓嶇鎺у埗璺敱) 涓篃闇€瑕佸姞 notFoundAndNoPower 404銆?01 鐣岄潰銆?
	 *    闃叉 404銆?01 涓嶅湪 layout 甯冨眬涓紝涓嶈缃殑璇濓紝404銆?01 鐣岄潰灏嗗叏灞忔樉绀?
	 */
	routes: [...notFoundAndNoPower, ...staticRoutes],
});

/**
 * 璺敱澶氱骇宓屽鏁扮粍澶勭悊鎴愪竴缁存暟缁?
 * @param arr 浼犲叆璺敱鑿滃崟鏁版嵁鏁扮粍
 * @returns 杩斿洖澶勭悊鍚庣殑涓€缁磋矾鐢辫彍鍗曟暟缁?
 */
export function formatFlatteningRoutes(arr: any) {
	if (arr.length <= 0) return false;
	for (let i = 0; i < arr.length; i++) {
		if (arr[i].children) {
			arr = arr.slice(0, i + 1).concat(arr[i].children, arr.slice(i + 1));
		}
	}
	return arr;
}

/**
 * 涓€缁存暟缁勫鐞嗘垚澶氱骇宓屽鏁扮粍锛堝彧淇濈暀浜岀骇锛氫篃灏辨槸浜岀骇浠ヤ笂鍏ㄩ儴澶勭悊鎴愬彧鏈変簩绾э紝keep-alive 鏀寔浜岀骇缂撳瓨锛?
 * @description isKeepAlive 澶勭悊 `name` 鍊硷紝杩涜缂撳瓨銆傞《绾у叧闂紝鍏ㄩ儴涓嶇紦瀛?
 * @link 鍙傝€冿細https://v3.cn.vuejs.org/api/built-in-components.html#keep-alive
 * @param arr 澶勭悊鍚庣殑涓€缁磋矾鐢辫彍鍗曟暟缁?
 * @returns 杩斿洖灏嗕竴缁存暟缁勯噸鏂板鐞嗘垚 `瀹氫箟鍔ㄦ€佽矾鐢憋紙dynamicRoutes锛塦 鐨勬牸寮?
 */
export function formatTwoStageRoutes(arr: any) {
	if (arr.length <= 0) return false;
	const newArr: any = [];
	const cacheList: Array<string> = [];
	arr.forEach((v: any) => {
		if (v.path === '/') {
			newArr.push({ component: v.component, name: v.name, path: v.path, redirect: v.redirect, meta: v.meta, children: [] });
		} else {
			// 鍒ゆ柇鏄惁鏄姩鎬佽矾鐢憋紙xx/:id/:name锛夛紝鐢ㄤ簬 tagsView 绛変腑浣跨敤
			// 淇锛歨ttps://gitee.com/lyt-top/vue-next-admin/issues/I3YX6G
			if (v.path.indexOf('/:') > -1) {
				v.meta['isDynamic'] = true;
				v.meta['isDynamicPath'] = v.path;
			}
			newArr[0].children.push({ ...v });
			// 瀛?name 鍊硷紝keep-alive 涓?include 浣跨敤锛屽疄鐜拌矾鐢辩殑缂撳瓨
			// 璺緞锛?@/layout/routerView/parent.vue
			if (newArr[0].meta.isKeepAlive && v.meta.isKeepAlive) {
				cacheList.push(v.name);
				const stores = useKeepALiveNames(pinia);
				stores.setCacheKeepAlive(cacheList);
			}
		}
	});
	return newArr;
}

// 璺敱鍔犺浇鍓?
router.beforeEach(async (to, from, next) => {
	NProgress.configure({ showSpinner: false });
	if (to.meta.title) NProgress.start();
	const token = Session.get('token');
	if (to.path === '/login' && !token) {
		next();
		NProgress.done();
	} else {
		if (!token) {
			next(`/login?redirect=${to.path}&params=${JSON.stringify(to.query ? to.query : to.params)}`);
			Session.clear();
			NProgress.done();
		} else if (token && to.path === '/login') {
			next('/sales/report');
			NProgress.done();
		} else {
			const storesRoutesList = useRoutesList(pinia);
			const { routesList } = storeToRefs(storesRoutesList);
			if (routesList.value.length === 0) {
				if (isRequestRoutes) {
					// 鍚庣鎺у埗璺敱锛氳矾鐢辨暟鎹垵濮嬪寲锛岄槻姝㈠埛鏂版椂涓㈠け
					await initBackEndControlRoutes();
					// 瑙ｅ喅鍒锋柊鏃讹紝涓€鐩磋烦 404 椤甸潰闂锛屽叧鑱旈棶棰?No match found for location with path 'xxx'
					// to.query 闃叉椤甸潰鍒锋柊鏃讹紝鏅€氳矾鐢卞甫鍙傛暟鏃讹紝鍙傛暟涓㈠け銆傚姩鎬佽矾鐢憋紙xxx/:id/:name"锛塱sDynamic 鏃犻渶澶勭悊
					next({ path: to.path, query: to.query });
				} else {
					// https://gitee.com/lyt-top/vue-next-admin/issues/I5F1HP
					await initFrontEndControlRoutes();
					next({ path: to.path, query: to.query });
				}
			} else {
				next();
			}
		}
	}
});

// 璺敱鍔犺浇鍚?
router.afterEach(() => {
	NProgress.done();
});

// 瀵煎嚭璺敱
export default router;

