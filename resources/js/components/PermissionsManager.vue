<template>
    <div class="permissions-manager">
        <modal title="Удаление группы" :loading="deletePanel.loading" ref="deleteModal">
            <div class="input-container">
                <label class="input-container__label">Переместить пользователей в группу</label>
                <div class="input-container__inner">
                    <select class="select-classic" v-model="deletePanel.groupToMove">
                        <option v-for="variant in groupsToMove" :value="variant.value">{{variant.name}}</option>
                    </select>
                </div>
            </div>
            <div class="form__bottom">
                <button class="button button--light" @click="deleteGroup()">ОК</button>
                <button class="button button--light" @click="$refs.deleteModal.hide()">Отмена</button>
                <response :light="true" :data="deletePanel.response"/>
            </div>
        </modal>
        <modal :loading="editPanel.loading" title="Редактирование группы" ref="editModal">
            <div class="input-container">
                <label class="input-container__label">Название</label>
                <div class="input-container__inner">
                    <input class="input" v-model="editPanel.data.name"/>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Иконка</label>
                <div class="input-container__inner">
                    <picture-uploader :light="true" v-model="editPanel.data.icon" :returnPath="true"/>
                </div>
            </div>
            <div class="form__bottom">
                <button class="button button--light" @click="saveGroup()">ОК</button>
                <button class="button button--light" @click="$refs.editModal.hide()">Отмена</button>
                <response :light="true" :data="editPanel.response"/>
            </div>
        </modal>
        <table v-if="loaded" class="permissions-manager__groups">
            <thead>
                <tr>
                    <td>
                        ID
                    </td>
                    <td>
                        Название
                    </td>
                    <td>
                        Иконка
                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(group, $index) in groupsData" :key="$index">
                    <td>{{group.id}}</td>
                    <td>{{group.name}}</td>
                    <td>
                        <img class="permissions-manager__group-icon" :src="group.icon"/>
                    </td>
                    <td>
                        <a @click="showEditModal(group)">Редактировать</a>
                    </td>
                    <td>
                        <a v-if="canDeleteGroup(group)" @click="showDeleteModal(group)">Удалить</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <a class="button button--light" @click="showAddModal()">Добавить еще группу</a>
        <br><br>
        <div class="form">
            <div class="form__preloader" v-if="permissionsPanel.loading"></div>
            <table v-if="loaded" v-for="(section, $index) in permissions" :key="$index">
                <thead>
                    <tr>
                        <td :colspan="groups.length + 1">
                            {{section.name}}
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td v-for="(group, $index2) in groupsData" :key="$index2">
                            {{group.name}}
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, $index3) in section.items" :key="$index3">
                        <td>{{item.name}}</td>
                        <td v-for="(group, $index4) in groupsData" :key="$index4">
                            <input :name="item.id + '_' + group.id" type="checkbox" v-if="item.values[group.id]" v-model="item.values[group.id].value">
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form__bottom">
                <a @click="savePermissions()" class="button button--light">Сохранить</a>
                <response :data="permissionsPanel.response"/>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .permissions-manager {
        font-size: .85em;
        background: #fff;
        color: #111;
        padding: 1em;
        &__group-icon {
            max-height: 5em;
        }
        a {
            color: #555;
            cursor: pointer;
            text-decoration: underline;
        }
        .button {
            text-decoration: none;
        }
        &__groups {
            font-size: 1.25em;
        }
        table {
            font-family: Roboto, sans-serif;
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 1em;
        }

        thead td {
            font-size: .85em;
            max-width: 7.5em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }

        tbody td:nth-of-type(1) {
            font-size: .85em;
            max-width: 24em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
            font-weight: bold;
        }

        tbody td {
            text-align: center;
        }

        td {
            border: 1px solid #ccc;
        }

        thead tr td:only-of-type {
            font-size: 1.25em;
            text-align: center;
            padding: .5em 0;
            background: #eee;
        }
    }
</style>
<script>
    import PictureUploader from './PictureUploader';
    import Modal from './Modal';
    import Response from './Response';
    export default {
        computed: {
            groupsToMove() {
                if (!this.deletePanel.group) {
                    return [];
                }
                return this.groupsData.filter(group => group.id !== this.deletePanel.group.id).map(group => {
                    return {
                        name: group.name,
                        value: group.id
                    }
                })
            }
        },
        methods: {
            savePermissions() {
                this.permissionsPanel.loading = true;
                let data = [];
                this.permissionsData.forEach(permissionGroup => {
                    permissionGroup.items.forEach(item => {
                        Object.keys(item.values).forEach(key => {
                            let value = item.values[key];
                            value.group_id = key;
                            value.permission_id = item.id;
                            data.push(value);
                        })
                    })
                });
                $.post('/admin/permissions', {permissions: JSON.stringify(data)}).done(res => {
                    this.permissionsPanel.loading = false;
                    this.permissionsPanel.response = res;
                    if (res.status) {
                        window.location.reload();
                    }
                }).fail((xhr) => {
                    this.permissionsPanel.loading = false;
                    let error = xhr.responseJSON;
                    this.permissionsPanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            canDeleteGroup(group) {
                return this.defaultgroups.indexOf(group.id) === -1;
            },
            showAddModal() {
                this.editPanel.response = null;
                this.editPanel.editing = false;
                this.editPanel.data = {};
                this.$refs.editModal.show();
            },
            showEditModal(group) {
                this.editPanel.response = null;
                this.editPanel.editing = true;
                this.editPanel.data = JSON.parse(JSON.stringify(group));
                this.$refs.editModal.show();
            },
            showDeleteModal(group) {
                this.deletePanel.group = group;
                this.$refs.deleteModal.show();
            },
            saveGroup() {
                this.editPanel.loading = true;
                let isEditing = this.editPanel.editing;
                let data = this.editPanel.data;
                $[isEditing ? 'put' : 'post'](isEditing ? '/admin/user-groups/' + data.id : '/admin/user-groups', data).done(res => {
                    this.editPanel.loading = false;
                    this.editPanel.response = res;
                    if (res.status) {
                        if (!isEditing) {
                            this.groupsData.push(res.data.group);
                        } else {
                            this.groupsData.forEach((group, index) => {
                                if (group.id === data.id) {
                                    this.groupsData[index] = data;
                                }
                            })
                        }
                        setTimeout(() => {
                            this.$refs.editModal.hide();
                        }, 2500)
                    }
                }).fail((xhr) => {
                    this.editPanel.loading = false;
                    let error = xhr.responseJSON;
                    this.editPanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            deleteGroup() {
                this.deletePanel.loading = true;
                let group = this.deletePanel.group;
                $.delete('/admin/user-groups/' + group.id).done(res => {
                    this.deletePanel.loading = false;
                    this.deletePanel.response = res;
                    if (res.status) {
                        this.groupsData.splice(this.groupsData.indexOf(group), 1);
                        setTimeout(() => {
                            this.$refs.deleteModal.hide();
                        }, 2500)
                    }
                }).fail((xhr) => {
                    this.deletePanel.loading = false;
                    let error = xhr.responseJSON;
                    this.editPanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            }
        },
        props: {
            defaultgroups: {
                type: Array,
                required: true,
            },
            permissionsvalues: {
                type: Object,
                required: true
            },
            permissions: {
                type: Array,
                required: true
            },
            groups: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                permissionsPanel: {
                    loading: false,
                    response: null,
                },
                editPanel: {
                    editing: false,
                    loading: false,
                    response: null,
                    data: {
                        name: '',
                        icon: ''
                    }
                },
                deletePanel: {
                    loading: false,
                    response: null,
                    group: null,
                    groupToMove: 2,
                },
                loaded: false,
                permissionsByGroup: [],
                groupsData: this.groups,
                permissionsData: this.permissions,
                permissionsValuesData: this.permissionsvalues,
            }
        },
        mounted() {
            this.permissionsData.forEach(permissionGroup => {
                permissionGroup.items.forEach(permissionItem => {
                    permissionItem.values = {};
                    this.permissionsValuesData[permissionItem.id].forEach(groupDataItem => {
                        permissionItem.values[groupDataItem.group_id] = {id: groupDataItem.id, value: groupDataItem.option_value};
                    });
                    let groupIds = Object.keys(permissionItem.values).map(item => parseInt(item));
                    let groupsWithNoValues = this.groupsData.map(group => group.id).filter(x => !groupIds.includes(x));
                    groupsWithNoValues.forEach(groupId => {
                        permissionItem.values[groupId] = {value: 0};
                    })
                })
            });
            //this.permissionsByGroup = permissionsByGroup;
            this.loaded = true;
        },
        components: {
            Response,
            Modal,
            PictureUploader,
        }
    }
</script>
