import axios, { AxiosInstance } from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import { Session } from '/@/utils/storage';
import qs from 'qs';

const service: AxiosInstance = axios.create({
	baseURL: import.meta.env.VITE_API_URL,
	timeout: 50000,
	headers: { 'Content-Type': 'application/json' },
	paramsSerializer: {
		serialize(params) {
			return qs.stringify(params, { allowDots: true });
		},
	},
});

service.interceptors.request.use(
	(config) => {
		const token = Session.get('token');
		if (token) {
			config.headers!['Authorization'] = `${token}`;
		}
		return config;
	},
	(error) => Promise.reject(error)
);

service.interceptors.response.use(
	(response) => {
		const res = response.data;
		if (res?.code !== 0) {
			if (res?.code === 401 || res?.code === 4001) {
				Session.clear();
				window.location.href = '/';
				ElMessageBox.alert('登录状态已失效，请重新登录', '提示', {})
					.then(() => {})
					.catch(() => {});
			}
			ElMessage.error(res?.msg || '请求失败');
			return Promise.reject(new Error(res?.msg || '请求失败'));
		}
		return res;
	},
	(error) => {
		if (error.message?.includes('timeout')) {
			ElMessage.error('请求超时');
		} else if (error.message === 'Network Error') {
			ElMessage.error('网络连接错误');
		} else if (error.response?.data?.msg) {
			ElMessage.error(error.response.data.msg);
		} else if (error.response?.statusText) {
			ElMessage.error(error.response.statusText);
		} else {
			ElMessage.error('接口请求失败');
		}
		return Promise.reject(error);
	}
);

export default service;
