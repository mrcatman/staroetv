<template>
    <div class="channels-manager">

        <snackbar ref="snackbar"></snackbar>

        <modal title="Удаление канала" :loading="deletePanel.loading" ref="deleteModal">
            <div class="modal-window__text">Вы уверены, что хотите удалить пользователя?</div>
            <div class="form__bottom">
                <button class="button button--light" @click="deleteUser()">ОК</button>
                <button class="button button--light" @click="deleteModalRef?.hide()">Отмена</button>
                <response :light="true" :data="deletePanel.response"/>
            </div>
        </modal>

        <modal :loading="changePasswordPanel.loading" title="Сменить пароль..." ref="changePasswordModal">
            <div class="input-container">
                <label class="input-container__label">Новый пароль</label>
                <div class="input-container__inner">
                    <input class="input" v-model="changePasswordPanel.data.new_password"/>
                </div>
            </div>
            <div class="form__bottom">
                <button class="button button--light" @click="changePassword()">ОК</button>
                <button class="button button--light" @click="changePasswordModalRef?.hide()">Отмена</button>
                <response :light="true" :data="changePasswordPanel.response"/>
            </div>
        </modal>

        <div class="admin-panel__heading-container">
            <div class="admin-panel__heading">Управление пользователями</div>
        </div>
        <div class="admin-panel__main-content">
            <div class="form__preloader" v-if="table.loading">
                <img src="/resources/images/ajax.gif">
            </div>
            <div class="admin-panel__table-filters">
                <div class="pager-container pager-container--light pager-container--admin-panel">
                    <b-pagination v-model="table.currentPage" :total-rows="usersList.length" :per-page="table.perPage" align="fill" size="sm" class="my-0"></b-pagination>
                </div>
                <div class="admin-panel__table-filters__input">
                    <input class="input" placeholder="Поиск" v-model="table.filter"/>
                </div>
            </div>
            <b-table class="admin-panel__table" show-empty stacked="md" :filter="table.filter" :items="usersList" :fields="table.fields" :current-page="table.currentPage" :per-page="table.perPage">
                <template v-slot:cell(group_id)="data">
                    <div class="users-manager__group-select">
                        <select2 :key="usersList[data.item._index].id" theme="default" @change="() => onUserGroupChange(usersList[data.item._index])" :options="groupsOptions" v-model="usersList[data.item._index].group_id"></select2>
                    </div>
                </template>
                <template v-slot:cell(_options)="data">
                    <div class="users-manager__buttons">
                        <a @click="showChangePasswordModal(data.item)" class="button button--light">Изменить пароль</a>
                        <a :href="'/profile/edit/' + data.item.id" target="_blank" class="button button--light">Ред. профиль</a>
                        <a @click="showDeleteModal(data.item)" class="button button--light">Удалить</a>
                    </div>
                </template>
            </b-table>
        </div>
    </div>
</template>
<style lang="scss">
    .users-manager {
        &__group-select {
            .select2-container {
                min-width: 16em;
            }
        }
    }
</style>
<script setup lang="ts">
import { ref, computed, onMounted, useTemplateRef } from 'vue';
import PictureUploader from '../PictureUploader.vue';
import Modal from '../Modal.vue';
import Response from '../Response.vue';
import Snackbar from '../Snackbar.vue';

interface UserWithIndex extends Models.User {
    _index?: number;
}

interface GroupOption {
    id: number;
    text: string;
}

const props = defineProps<{
    groups: Models.UserGroup[];
    users: Models.User[];
}>();

const snackbarRef = useTemplateRef<typeof Snackbar>('snackbar');
const deleteModalRef = useTemplateRef<typeof Modal>('deleteModal');
const changePasswordModalRef = useTemplateRef<typeof Modal>('changePasswordModal');

const groupsOptions = computed<GroupOption[]>(() => {
    return props.groups.map(group => {
        return {
            id: group.id,
            text: group.name
        };
    });
});

const table = ref({
    response: null as Forms.Response | null,
    loading: false,
    filter: '',
    currentPage: 1,
    perPage: 50,
    fields: [
        {
            key: 'username',
            label: 'Ник',
            sortable: true
        },
        {
            key: 'ip_address_reg',
            label: 'IP',
            sortable: true
        },
        {
            key: 'group_id',
            label: 'Группа',
            sortable: true
        },
        {
            key: 'email',
            label: 'E-mail',
            sortable: true
        },
        {
            key: '_options',
            label: '',
            sortable: false
        },
    ],
});

const changePasswordPanel = ref({
    data: {
        new_password: ''
    },
    loading: false,
    user: null as Models.User | null,
    response: null as Forms.Response | null
});

const usersList = ref<UserWithIndex[]>([]);

const deletePanel = ref({
    loading: false,
    user: null as Models.User | null,
    response: null as Forms.Response | null
});

const changePassword = () => {
    if (!changePasswordPanel.value.user) return;
    changePasswordPanel.value.loading = true;
    $.post(route('admin.users.change-password'), {
        new_password: changePasswordPanel.value.data.new_password,
        user_id: changePasswordPanel.value.user.id,
    }).done(res => {
        changePasswordPanel.value.loading = false;
        changePasswordPanel.value.response = res;
        if (res.status) {
            changePasswordModalRef.value?.hide();
        }
    }).fail((xhr) => {
        changePasswordPanel.value.loading = false;
        const error = xhr.responseJSON;
        changePasswordPanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const showChangePasswordModal = (user: Models.User) => {
    changePasswordPanel.value.data.new_password = '';
    changePasswordPanel.value.response = null;
    changePasswordPanel.value.user = user;
    changePasswordModalRef.value?.show();
};

const onUserGroupChange = (user: UserWithIndex) => {
    $.post(route('admin.users.change-group'), {group_id: user.group_id, user_id: user.id}).done(res => {
        snackbarRef.value?.show(res);
    })
};

const deleteUser = () => {
    if (!deletePanel.value.user) return;
    deletePanel.value.loading = true;
    $.post(route('admin.users.delete'), {
        user_id: deletePanel.value.user.id
    }).done(res => {
        deletePanel.value.loading = false;
        if (res.status) {
            usersList.value = usersList.value.filter(user => user.id !== deletePanel.value.user?.id);
            deleteModalRef.value?.hide();
        }
    }).fail((xhr) => {
        deletePanel.value.loading = false;
        const error = xhr.responseJSON;
        deletePanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const showDeleteModal = (user: Models.User) => {
    deletePanel.value.response = null;
    deletePanel.value.user = user;
    deleteModalRef.value?.show();
};

onMounted(() => {
    usersList.value = props.users.map((user, index) => {
        (user as UserWithIndex)._index = index;
        return user as UserWithIndex;
    });
});
</script>
