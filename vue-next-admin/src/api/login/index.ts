import request from '/@/utils/request';

export function useLoginApi() {
	return {
		signIn: (data: object) => {
			return request({
				url: '/admin/login',
				method: 'post',
				data,
			});
		},
		signOut: (data: object) => {
			return request({
				url: '/admin/logout',
				method: 'post',
				data,
			});
		},
	};
}
