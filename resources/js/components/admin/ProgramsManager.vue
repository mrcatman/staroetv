<template>
    <div class="channels-manager">

        <snackbar ref="snackbar" />

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
            <b-table ref="tableRef" class="admin-panel__table" show-empty stacked="md" :filter="table.filter" :provider="programs" :debounce="500"
                     :fields="table.fields" :current-page="table.currentPage" :per-page="table.perPage" empty-filtered-text="По вашему запросу не найдено программ">
                <template v-slot:cell(name)="data">
                    <a target="_blank" :href="_route('programs.show', data.item.id)">
                        {{data.item.name}}
                    </a>
                </template>
                <template v-slot:cell(_options)="data">
                    <div class="buttons-row buttons-row--nowrap">
                        <a title="Редактировать" :href="_route('programs.edit', data.item.id)" target="_blank" class="button button--icon-only button--light">
                            <i class="fas fa-edit"></i>
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
<script setup lang="ts">
import { ref, computed, useTemplateRef } from 'vue';
import { BTable, BPagination } from 'bootstrap-vue-next'

import Snackbar from '../Snackbar.vue';
import Preloader from "../Preloader.vue";

const table = ref({
    response: null as Forms.Response | null,
    loading: false,
    filter: '',
    currentPage: 1,
    perPage: 50,
    fields: [
        {
            key: 'name',
            label: 'Название',
            sortable: true
        },
        {
            key: 'сhannel_name',
            label: 'Канал',
            sortable: false
        },
        {
            key: 'genre_name',
            label: 'Категория',
            sortable: false
        },

        {
            key: 'records_count',
            label: 'Кол-во записей',
            sortable: false
        },
        {
            key: 'created_at',
            label: 'Дата создания',
            sortable: true
        },
    ],
});

const tableRef = useTemplateRef<typeof BTable>('tableRef');
const totalRows = ref<number>(0);

const programs = (context) => {
    return new Promise((resolve, reject) => {
        $.get(route('admin.programs.list'), {
            page: context.currentPage,
            count: context.perPage > 50 ? 50 : context.perPage,
            sort: context.sortBy,
            search: context.filter,
        })
            .then(res => {
                totalRows.value = res.total;
                console.log(res);
                resolve(res.data);
            })
            .catch(error => {
                resolve([]);
            });
    });
}

const _route = route;
</script>
