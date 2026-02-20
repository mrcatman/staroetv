<template>
    <div class="channels-manager">

        <snackbar ref="snackbar"></snackbar>

        <modal title="Удаление" :loading="deletePanel.loading" ref="deleteModal">
            <div class="form__content">
                <div class="modal-window__text">Вы уверены, что хотите удалить пользователя?</div>
                <div class="form__bottom">
                    <button class="button button--light" @click="deleteUser()">ОК</button>
                    <button class="button button--light" @click="deleteModalRef?.hide()">Отмена</button>
                    <response :light="true" :data="deletePanel.response"/>
                </div>
            </div>
        </modal>

        <modal :loading="changePasswordPanel.loading" title="Сменить пароль..." ref="changePasswordModal">
            <div class="form__content">
                <input-container label="Новый пароль">
                    <input class="input" v-model="changePasswordPanel.data.new_password"/>
                </input-container>
                <div class="form__bottom">
                    <button class="button button--light" @click="changePassword()">ОК</button>
                    <button class="button button--light" @click="changePasswordModalRef?.hide()">Отмена</button>
                    <response :light="true" :data="changePasswordPanel.response"/>
                </div>
            </div>
        </modal>


        <div class="admin-panel__main-content">
            <Preloader v-if="table.loading"/>
            <div class="admin-panel__table-filters">
                <div class="pager-container pager-container--light pager-container--admin-panel">
                    <b-pagination v-model="table.currentPage" :total-rows="totalRows" :per-page="table.perPage"
                                  align="fill" size="sm" />
                </div>
                <div class="admin-panel__table-filters__input">
                    <input class="input" placeholder="Поиск" v-model="table.filter"/>
                </div>
            </div>
            <b-table ref="tableRef" class="admin-panel__table" show-empty stacked="md" :filter="table.filter" :provider="users" :debounce="500"
                     :fields="table.fields" :current-page="table.currentPage" :per-page="table.perPage">
                <template v-slot:cell(username)="data">
                    <a target="_blank" :href="_route('users.show', data.item.id)">
                        {{data.item.username}}
                    </a>
                </template>
                <!--
                <template v-slot:cell(group_id)="data">
                    <div class="users-manager__group-select">
                        <select2 :key="data.item.id" theme="default" @change="() => onUserGroupChange(data.item)"
                                 :options="groupsOptions" v-model="data.item.group_id"/>
                    </div>
                </template>
                -->
                <template v-slot:cell(_options)="data">
                    <div class="buttons-row buttons-row--nowrap">
                        <a title="Сменить пароль" @click="showChangePasswordModal(data.item)" class="button button--icon-only button--light">
                            <i class="fas fa-key"></i>
                        </a>
                        <a title="Ред. профиль" :href="_route('profile.edit.user', data.item.id)" target="_blank" class="button button--icon-only button--light">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a title="Удалить" @click="showDeleteModal(data.item)" class="button button--icon-only button--light">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </template>
            </b-table>
            <div class="pager-container pager-container--light pager-container--admin-panel">
                <b-pagination v-model="table.currentPage" :total-rows="totalRows" :per-page="table.perPage" align="fill" size="sm"  />
            </div>
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
import { ref, computed, useTemplateRef } from 'vue';
import { BTable, BPagination } from 'bootstrap-vue-next'

import Modal from '../Modal.vue';
import Response from '../Response.vue';
import Snackbar from '../Snackbar.vue';
import Preloader from "../Preloader.vue";
import InputContainer from "@/components/InputContainer.vue";
import { getErrorMessage } from "@/utils/errors";


interface GroupOption {
    id: number;
    text: string;
}

const props = defineProps<{
    groups: Models.UserGroup[];
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
            key: 'country',
            label: 'Страна',
            sortable: false
        },
        // {
        //     key: 'group_id',
        //     label: 'Группа',
        //     sortable: true
        // },
        {
            key: 'created_at',
            label: 'Дата рег.',
            sortable: true
        },
        {
            key: 'was_online',
            label: 'Дата входа',
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
const tableRef = useTemplateRef<typeof BTable>('tableRef');

const changePasswordPanel = ref({
    data: {
        new_password: ''
    },
    loading: false,
    user: null as Models.User | null,
    response: null as Forms.Response | null
});

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
    }).fail((err) => {
        changePasswordPanel.value.loading = false;
        changePasswordPanel.value.response = {
            status: 0,
            text: getErrorMessage(err)
        };
    })
};

const showChangePasswordModal = (user: Models.User) => {
    changePasswordPanel.value.data.new_password = '';
    changePasswordPanel.value.response = null;
    changePasswordPanel.value.user = user;
    changePasswordModalRef.value?.show();
};

const onUserGroupChange = (user: Models.User) => {
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
            tableRef.value?.refresh();
            deleteModalRef.value?.hide();
        }
    }).fail((err) => {
        deletePanel.value.loading = false;
        deletePanel.value.response = {status: 0, text: getErrorMessage(err)};
    })
};

const showDeleteModal = (user: Models.User) => {
    deletePanel.value.response = null;
    deletePanel.value.user = user;
    deleteModalRef.value?.show();
};

const totalRows = ref<number>(0);

const users = (context) => {
    return new Promise((resolve, reject) => {
        $.get(route('admin.users.list'), {
            page: context.currentPage,
            count: context.perPage > 50 ? 50 : context.perPage,
            sort: context.sortBy,
            search: context.filter,
        })
            .then(res => {
                totalRows.value = res.total;
                resolve(res.data);
            })
            .catch(error => {
                resolve([]);
            });
    });
}

const _route = route;
</script>
