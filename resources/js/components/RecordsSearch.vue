<template>
    <div class="records-search--container">
        <div class="records-search">
            <div class="records-search__inner">
                <div class="records-search__title">Поиск по записям</div>
                <form @submit="onSubmit" ref="form" method="GET" :action="action" class="records-search__form">
                    <div class="records-search__input-container">
                        <input v-model="data.search" class="input" placeholder="" name="search"/>
                    </div>
                    <button class="button button--light">Найти</button>
                </form>
                <a class="records-search__expand" @click="showExtended = !showExtended">Расширенный поиск</a>
            </div>
            <div class="records-search__extended" v-show="showExtended">
                <div class="records-search__sort">
                    <span class="records-search__sort__title">Сортировать по: </span>
                    <a class="records-search__sort__option" :class="{'records-search__sort__option--active': data.sort === option.key}" @click="setSort(option)" :key="$index" v-for="(option, $index) in sortOptions">
                        <span class="records-search__sort__option__title">{{option.title}}</span>
                        <span class="records-search__sort__option__arrow" v-if="data.sort === option.key">{{data.sort_order === 'asc' ? '↑' : '↓'}}</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="records-search__result" v-if="showResults">
            <div class="form__preloader" v-show="isLoading"><img src="/pictures/ajax.gif"></div>
            <div class="row">
                <div class="box">
                    <div class="box__inner">
                        <div class="records-list__pager-container records-list__pager-container--top" v-show="resultsList.last_page > 1">
                            <pagination :limit="3" :data="resultsList" @pagination-change-page="getResults"></pagination>
                        </div>
                        <div class="records-list">
                            <a :href="record.url" v-for="(record, $index) in resultsList.data" :key="record.id" class="record-item">
                                <div class="record-item__cover" :style="{backgroundImage: `url(${record.cover})`}"></div>
                                <div class="record-item__texts">
                                    <span class="record-item__title">
                                        {{record.title}}
                                    </span>
                                    <div class="record-item__info">
                                        <span class="record-item__date"><i class="fa fa-calendar"></i>{{record.created_at}}</span>
                                        <span class="record-item__views"><i class="fa fa-eye"></i>{{record.views}}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="records-list__pager-container" v-show="resultsList.last_page > 1">
                            <pagination :limit="3" :data="resultsList" @pagination-change-page="getResults"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        methods: {
            setSort(option) {
                if (this.data.sort === option.key) {
                    this.$set(this.data, 'sort_order', this.data.sort_order === 'desc' ? 'asc' : 'desc')
                } else {
                    this.$set(this.data, 'sort', option.key);
                    this.$set(this.data, 'sort_order', 'desc');
                }
                if (this.showResults) {
                    this.load();
                } else {
                    this.$refs.form.submit();
                }
            },
            load() {
                this.isLoading = true;
                $.post(this.action, this.data).done((res) => {
                    this.resultsList = res.records;
                    this.isLoading = false;
                    window.scrollTo(0, 0);
                })
            },
            onSubmit(e) {
                if (this.showResults) {
                    e.preventDefault();
                    this.data.page = 1;
                    this.load();
                }
            },
            getResults(page) {
                this.data.page = page;
                this.load();
            }
        },
        mounted() {

        },
        data() {
            return {
                showExtended: this.showResults,
                isLoading: false,
                resultsList: this.results,
                data: JSON.parse(JSON.stringify(this.params)),
                sortOptions: [
                    {
                        title: 'Дате выхода', key: 'date'
                    },
                    {
                        title: 'Дате заливки', key: 'created_at'
                    }
                ]
            }
        },
        props: ['action', 'params', 'showResults', 'results'],
    }
</script>