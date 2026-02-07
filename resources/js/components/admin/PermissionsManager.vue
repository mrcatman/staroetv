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
                <button class="button button--light" @click="deleteModalRef?.hide()">Отмена</button>
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
                    <picture-uploader light v-model:path="editPanel.data.icon"/>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">SVG код иконки</label>
                <div class="input-container__inner">
                    <textarea class="input" v-model="editPanel.data.icon_svg_code"></textarea>
                </div>
            </div>
            <div class="form__bottom">
                <button class="button button--light" @click="saveGroup()">ОК</button>
                <button class="button button--light" @click="editModalRef?.hide()">Отмена</button>
                <response :light="true" :data="editPanel.response"/>
            </div>
        </modal>

        <table v-if="loaded" class="admin-panel__table permissions-manager__groups">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Название</td>
                    <td>Иконка</td>
                    <td></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(group, index) in groupsData" :key="index">
                    <td>{{group.id}}</td>
                    <td>{{group.name}}</td>
                    <td>
                        <div class="permissions-manager__group-icon-svg" v-html="group.icon_svg_code"></div>
                    </td>
                    <td>
                        <a class="button button--light" @click="showEditModal(group)">Редактировать</a>
                    </td>
                    <td>
                        <a class="button button--light" v-if="canDeleteGroup(group)" @click="showDeleteModal(group)">Удалить</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <a class="button button--light" @click="showAddModal()">Добавить еще группу</a>
        <br><br>
        <div class="form">
            <div class="form__preloader" v-if="permissionsPanel.loading"></div>
            <table v-if="loaded" class="admin-panel__table admin-panel__table--small" v-for="(section, index) in permissionsData" :key="index">
                <thead>
                    <tr>
                        <td :colspan="groupsData.length + 1">{{section.name}}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td v-for="(group, index2) in groupsData" :key="index2">{{group.name}}</td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index3) in section.items" :key="index3">
                        <td :title="item.id">{{item.name}}</td>
                        <td v-for="(group, index4) in groupsData" :key="index4">
                            <input :name="item.id + '_' + group.id" type="checkbox" v-if="item.values[group.id]" v-model="item.values[group.id].value">
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form__bottom">
                <a @click="savePermissions()" class="button button--light">Сохранить</a>
                <response :light="true" :data="permissionsPanel.response"/>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .permissions-manager {
        &__group-icon-svg {
            padding: 0 1em;
        }
    }
</style>
<script setup lang="ts">
import { ref, computed, onMounted, useTemplateRef } from 'vue';
import PictureUploader from '../PictureUploader.vue';
import Modal from '../Modal.vue';
import Response from '../Response.vue';

interface PermissionValue {
    id?: number;
    value: number | boolean;
    group_id?: number;
    permission_id?: number;
}

interface PermissionItem {
    id: number;
    name: string;
    values: { [key: number]: PermissionValue };
}

interface PermissionSection {
    name: string;
    items: PermissionItem[];
}

interface GroupToMoveOption {
    name: string;
    value: number;
}

interface EditPanelData {
    id?: number;
    name: string;
    icon?: string;
    icon_svg_code?: string;
}

const props = defineProps<{
    defaultgroups: number[];
    permissionsValues: { [key: number]: any[] };
    permissions: PermissionSection[];
    groups: Models.UserGroup[];
}>();

const deleteModalRef = useTemplateRef<typeof Modal>('deleteModal');
const editModalRef = useTemplateRef<typeof Modal>('editModal');

const groupsToMove = computed<GroupToMoveOption[]>(() => {
    if (!deletePanel.value.group) {
        return [];
    }
    return groupsData.value.filter(group => group.id !== deletePanel.value.group?.id).map(group => {
        return {
            name: group.name,
            value: group.id
        };
    });
});

const permissionsPanel = ref({
    loading: false,
    response: null as Forms.Response | null,
});

const editPanel = ref({
    editing: false,
    loading: false,
    response: null as Forms.Response | null,
    data: {
        name: '',
        icon: '',
        icon_svg_code: ''
    } as EditPanelData
});

const deletePanel = ref({
    loading: false,
    response: null as Forms.Response | null,
    group: null as Models.UserGroup | null,
    groupToMove: 2,
});

const loaded = ref<boolean>(false);
const groupsData = ref<Models.UserGroup[]>([...props.groups]);
const permissionsData = ref<PermissionSection[]>([...props.permissions]);

const savePermissions = () => {
    permissionsPanel.value.loading = true;
    const data: PermissionValue[] = [];
    permissionsData.value.forEach(permissionGroup => {
        permissionGroup.items.forEach(item => {
            Object.keys(item.values).forEach(key => {
                const value = item.values[parseInt(key)];
                value.group_id = parseInt(key);
                value.permission_id = item.id;
                data.push(value);
            });
        });
    });
    $.post(route('admin.permissions.save'), {permissions: JSON.stringify(data)}).done(res => {
        permissionsPanel.value.loading = false;
        permissionsPanel.value.response = res;
        //if (res.status) {
        //    window.location.reload();
        //}
    }).fail((xhr) => {
        permissionsPanel.value.loading = false;
        const error = xhr.responseJSON;
        permissionsPanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const canDeleteGroup = (group: Models.UserGroup) => {
    return props.defaultgroups.indexOf(group.id) === -1;
};

const showAddModal = () => {
    editPanel.value.response = null;
    editPanel.value.editing = false;
    editPanel.value.data = {
        name: '',
        icon: '',
        icon_svg_code: ''
    };
    editModalRef.value?.show();
};

const showEditModal = (group: Models.UserGroup) => {
    editPanel.value.response = null;
    editPanel.value.editing = true;
    editPanel.value.data = JSON.parse(JSON.stringify(group)) as EditPanelData;
    editModalRef.value?.show();
};

const showDeleteModal = (group: Models.UserGroup) => {
    deletePanel.value.group = group;
    deleteModalRef.value?.show();
};

const saveGroup = () => {
    editPanel.value.loading = true;
    const isEditing = editPanel.value.editing;
    const data = editPanel.value.data;
    const url = isEditing
        ? route('admin.user-groups.update', {user_group: data.id!.toString()})
        : route('admin.user-groups.store');
    const method = isEditing ? 'PUT' : 'POST';

    $.ajax({
        url,
        method,
        data
    }).done(res => {
        editPanel.value.loading = false;
        editPanel.value.response = res;
        if (res.status) {
            if (!isEditing) {
                groupsData.value.push(res.data.group);
            } else {
                const index = groupsData.value.findIndex(group => group.id === data.id);
                if (index !== -1) {
                    groupsData.value[index] = res.data.group;
                }
            }
            setTimeout(() => {
                editModalRef.value?.hide();
            }, 2500);
        }
    }).fail((xhr) => {
        editPanel.value.loading = false;
        const error = xhr.responseJSON;
        editPanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const deleteGroup = () => {
    if (!deletePanel.value.group) return;
    deletePanel.value.loading = true;
    const group = deletePanel.value.group;
    $.ajax({
        url: route('admin.user-groups.destroy', {user_group: group.id.toString()}),
        method: 'DELETE'
    }).done(res => {
        deletePanel.value.loading = false;
        deletePanel.value.response = res;
        if (res.status) {
            const index = groupsData.value.indexOf(group);
            if (index !== -1) {
                groupsData.value.splice(index, 1);
            }
            setTimeout(() => {
                deleteModalRef.value?.hide();
            }, 2500);
        }
    }).fail((xhr) => {
        deletePanel.value.loading = false;
        const error = xhr.responseJSON;
        deletePanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

onMounted(() => {
    const data = [...permissionsData.value];
    data.forEach(permissionGroup => {
        permissionGroup.items.forEach(permissionItem => {
            permissionItem.values = {};
            if (props.permissionsValues[permissionItem.id]) {
                props.permissionsValues[permissionItem.id].forEach(groupDataItem => {
                    permissionItem.values[groupDataItem.group_id] = {
                        id: groupDataItem.id,
                        value: !!groupDataItem.option_value
                    };
                });
            }
            const groupIds = Object.keys(permissionItem.values).map(item => parseInt(item));
            const groupsWithNoValues = groupsData.value.map(group => group.id).filter(x => !groupIds.includes(x));
            groupsWithNoValues.forEach(groupId => {
                permissionItem.values[groupId] = {value: false};
            });
        });
    });

    permissionsData.value = data;
    loaded.value = true;
});
</script>
