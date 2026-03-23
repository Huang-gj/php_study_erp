<template>
	<el-form size="large" class="login-content-form">
		<el-form-item class="login-animation1">
			<el-input text :placeholder="$t('message.account.accountPlaceholder1')" v-model="state.ruleForm.userName" clearable autocomplete="off">
				<template #prefix>
					<el-icon class="el-input__icon"><ele-User /></el-icon>
				</template>
			</el-input>
		</el-form-item>
		<el-form-item class="login-animation2">
			<el-input
				:type="state.isShowPassword ? 'text' : 'password'"
				:placeholder="$t('message.account.accountPlaceholder2')"
				v-model="state.ruleForm.password"
				autocomplete="off"
				@keyup.enter="onSignIn"
			>
				<template #prefix>
					<el-icon class="el-input__icon"><ele-Unlock /></el-icon>
				</template>
				<template #suffix>
					<i
						class="iconfont el-input__icon login-content-password"
						:class="state.isShowPassword ? 'icon-yincangmima' : 'icon-xianshimima'"
						@click="state.isShowPassword = !state.isShowPassword"
					></i>
				</template>
			</el-input>
		</el-form-item>
		<el-form-item class="login-animation3">
			<el-input text :placeholder="$t('message.account.accountPlaceholder3')" disabled>
				<template #prefix>
					<el-icon class="el-input__icon"><ele-Position /></el-icon>
				</template>
			</el-input>
		</el-form-item>
		<el-form-item class="login-animation4">
			<el-button type="primary" class="login-content-submit" round v-waves @click="onSignIn" :loading="state.loading.signIn">
				<span>{{ $t('message.account.accountBtnText') }}</span>
			</el-button>
		</el-form-item>
	</el-form>
</template>

<script setup lang="ts" name="loginAccount">
import { reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import { useThemeConfig } from '/@/stores/themeConfig';
import { useUserInfo } from '/@/stores/userInfo';
import { useLoginApi } from '/@/api/login';
import { initFrontEndControlRoutes } from '/@/router/frontEnd';
import { initBackEndControlRoutes } from '/@/router/backEnd';
import { Session } from '/@/utils/storage';
import { formatAxis } from '/@/utils/formatTime';
import { NextLoading } from '/@/utils/loading';
import logoMini from '/@/assets/logo-mini.svg';

const { t } = useI18n();
const storesThemeConfig = useThemeConfig();
const storesUserInfo = useUserInfo();
const { themeConfig } = storeToRefs(storesThemeConfig);
const route = useRoute();
const router = useRouter();
const loginApi = useLoginApi();

const state = reactive({
	isShowPassword: false,
	ruleForm: {
		userName: '',
		password: '',
	},
	loading: {
		signIn: false,
	},
});

const currentTime = computed(() => {
	return formatAxis(new Date());
});

const buildDeviceName = () => {
	const platform = navigator.platform || 'Web';
	const deviceName = `Web-${platform}`;
	return deviceName.slice(0, 100);
};

const onSignIn = async () => {
	if (!state.ruleForm.userName.trim()) {
		ElMessage.warning('请输入登录账号');
		return;
	}

	if (!state.ruleForm.password) {
		ElMessage.warning('请输入登录密码');
		return;
	}

	state.loading.signIn = true;

	try {
		const res: any = await loginApi.signIn({
			username: state.ruleForm.userName.trim(),
			password: state.ruleForm.password,
			login_ip: window.location.hostname || '127.0.0.1',
			device_name: buildDeviceName(),
			client_type: 'web',
		});

		const adminInfo = res.data?.admin_info || {};
		const userInfo = {
			userName: adminInfo.username || state.ruleForm.userName.trim(),
			photo: adminInfo.avatar || logoMini,
			time: new Date().getTime(),
			roles: adminInfo.is_super_admin === 1 ? ['admin'] : ['common'],
			authBtnList: adminInfo.is_super_admin === 1 ? ['btn.add', 'btn.del', 'btn.edit', 'btn.link'] : ['btn.add', 'btn.link'],
			adminUserId: adminInfo.admin_user_id || 0,
			nickname: adminInfo.nickname || '',
			realName: adminInfo.real_name || '',
			phoneNumber: adminInfo.phone_number || '',
			departmentName: adminInfo.department_name || '',
			roleName: adminInfo.role_name || '',
			isSuperAdmin: adminInfo.is_super_admin || 0,
		};

		Session.set('token', `${res.data.token_type} ${res.data.access_token}`);
		Session.set('userInfo', userInfo);
		storesUserInfo.userInfos = userInfo as any;

		if (!themeConfig.value.isRequestRoutes) {
			const isNoPower = await initFrontEndControlRoutes();
			signInSuccess(isNoPower);
		} else {
			const isNoPower = await initBackEndControlRoutes();
			signInSuccess(isNoPower);
		}
	} catch (error) {
		state.loading.signIn = false;
	}
};

const signInSuccess = (isNoPower: boolean | undefined) => {
	if (isNoPower) {
		ElMessage.warning('抱歉，您当前没有登录权限');
		Session.clear();
	} else {
		const currentTimeInfo = currentTime.value;
		if (route.query?.redirect) {
			router.push({
				path: route.query.redirect as string,
				query: Object.keys((route.query?.params as string) || '').length > 0 ? JSON.parse(route.query.params as string) : '',
			});
		} else {
			router.push('/');
		}
		ElMessage.success(`${currentTimeInfo}，${t('message.signInText')}`);
		NextLoading.start();
	}
	state.loading.signIn = false;
};
</script>

<style scoped lang="scss">
.login-content-form {
	margin-top: 20px;
	@for $i from 1 through 4 {
		.login-animation#{$i} {
			opacity: 0;
			animation-name: error-num;
			animation-duration: 0.5s;
			animation-fill-mode: forwards;
			animation-delay: calc($i/10) + s;
		}
	}
	.login-content-password {
		display: inline-block;
		width: 20px;
		cursor: pointer;
		&:hover {
			color: #909399;
		}
	}
	.login-content-submit {
		width: 100%;
		letter-spacing: 2px;
		font-weight: 300;
		margin-top: 15px;
	}
}
</style>
