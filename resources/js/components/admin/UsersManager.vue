<template>
    <div class="channels-manager">

        <snackbar ref="snackbar"></snackbar>

        <modal title="Удаление канала" :loading="deletePanel.loading" ref="deleteModal">
            <div class="modal-window__text">Вы уверены, что хотите удалить пользователя?</div>
            <div class="form__bottom">
                <button class="button button--light" @click="deleteUser()">ОК</button>
                <button class="button button--light" @click="$refs.deleteModal.hide()">Отмена</button>
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
                <button class="button button--light" @click="$refs.changePasswordModal.hide()">Отмена</button>
                <response :light="true" :data="changePasswordPanel.response"/>
            </div>
        </modal>

        <div class="admin-panel__heading-container">
            <div class="admin-panel__heading">Управление пользователями</div>
        </div>
        <div class="admin-panel__main-content">
            <div class="form__preloader" v-if="table.loading">
                <img src="/pictures/ajax.gif">
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
                        <select2 :key="usersList[data.item._index].id" theme="default" @change="(e) => onUserGroupChange(e, usersList[data.item._index])" :options="groupsOptions" v-model="usersList[data.item._index].group_id"></select2>
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
<script>
    import PictureUploader from '../PictureUploader';
    import Modal from '../Modal';
    import Response from '../Response';
    import Snackbar from '../Snackbar';

    export default {
        computed: {
            groupsOptions() {
                return this.groups.map(group => {
                    return {
                        id: group.id,
                        text: group.name
                    }
                })
            }
        },
        methods: {
            changePassword() {
                this.changePasswordPanel.loading = true;
                $.post('/admin/users/change-password', {
                    new_password: this.changePasswordPanel.data.new_password,
                    user_id: this.changePasswordPanel.user.id,
                }).done(res => {
                    this.changePasswordPanel.loading = false;
                    this.changePasswordPanel.response = res;
                    if (res.status) {
                        this.$refs.changePasswordModal.hide();
                    }
                }).fail((xhr) => {
                    this.changePasswordPanel.loading = false;
                    let error = xhr.responseJSON;
                    this.changePasswordPanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            showChangePasswordModal(user) {
                this.changePasswordPanel.data.new_password = '';
                this.changePasswordPanel.response = null;
                this.changePasswordPanel.user = user;
                this.$refs.changePasswordModal.show();
            },
            onUserGroupChange(id, user) {
                $.post('/admin/users/change-group', {group_id: id, user_id: user.id}).done(res => {
                    this.$refs.snackbar.show(res);
                })
            },
            deleteUser() {
                this.deletePanel.loading = true;
                $.post('/admin/users/delete', {
                    user_id: this.deletePanel.user.id
                }).done(res => {
                    this.deletePanel.loading = false;
                    if (res.status) {
                        this.usersList = this.usersList.filter(user => user.id !== this.deletePanel.user.id);
                        this.$refs.deleteModal.hide();
                    }
                }).fail((xhr) => {
                    this.deletePanel.loading = false;
                    let error = xhr.responseJSON;
                    this.deletePanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            showDeleteModal(user) {
                this.deletePanel.response = null;
                this.deletePanel.user = user;
                this.$refs.deleteModal.show();
            },
            showMergeModal(channel) {
                this.mergePanel.response = null;
                this.mergePanel.channel = channel;
                this.$refs.mergeModal.show();
            },
        },
        props: {
            groups: {
                type: Array,
                required: true,
            },
            users: {
                type: Array,
                required: true,
            },
        },
        data() {
            return {
                table: {
                    response: null,
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
                },
                changePasswordPanel: {
                    data: {
                        new_password: ''
                    },
                    loading: false,
                    user: null,
                    response: null
                },
                usersList: [],
                deletePanel: {
                    loading: false,
                    user: null,
                    response: null
                }
            }
        },
        mounted() {
            this.usersList = this.users.map((user, index) => {
                user._index = index;
                return user;
            })
        },
        components: {
            Snackbar,
            Response,
            Modal,
            PictureUploader,
        }
    }
</script>
