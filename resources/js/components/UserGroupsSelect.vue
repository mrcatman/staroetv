<template>
    <div class="user-groups-select">
        <input type="hidden" :name="name" v-model="value"/>
        <div class="user-groups-select__default-settings" v-show="showDefaultSettings">
            <label class="input-container input-container--checkbox">
                <input type="checkbox" v-model="defaultSettings">
                <div class="input-container--checkbox__element"></div>
                <div class="input-container__label">Настройки по умолчанию</div>
            </label>
        </div>
        <div class="user-groups-select__items" v-show="!defaultSettings || !showDefaultSettings">
            <div class="user-groups-select__item" v-for="(group, $index) in groups" :key="$index" >
                <label class="input-container input-container--checkbox">
                    <input type="checkbox" v-model="dataByGroup[group.id]">
                    <div class="input-container--checkbox__element"></div>
                    <div class="input-container__label">{{group.name}}</div>
                </label>
            </div>
            <div class="user-groups-select__item user-groups-select__item--all-groups">
                <label class="input-container input-container--checkbox ">
                    <input type="checkbox" v-model="allGroups">
                    <div class="input-container--checkbox__element"></div>
                    <div class="input-container__label">Все группы</div>
                </label>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .user-groups-select {
        &__items {
            display: flex;
            flex-wrap: wrap;
            font-size: .75em;
            background: #fafafa;
            border: 1px solid #ccc;
            color: #333;
        }
        &__item {
            &--all-groups {
                color: blue;
            }
        }

        &__default-settings {
            margin: -.35em -.75em .5em;
        }
    }
</style>
<script>
    export default {
        props: ['name', 'data', 'groups', 'showDefaultSettings'],
        computed: {
            value() {
                if (this.defaultSettings && this.showDefaultSettings) {
                    return "0";
                }
                let groups = [];
                this.groups.forEach(group => {
                    if (this.dataByGroup[group.id]) {
                        groups.push(group.id)
                    }
                });
                return groups.join(",");
            }
        },
        watch: {
            allGroups(allGroupsHandler) {
                if (this.set) {
                    this.groups.forEach(group => {
                        this.$set(this.dataByGroup, group.id, allGroupsHandler);
                    });
                }
            }
        },
        mounted() {
            let splitted = this.val.split(",").filter(val => val.length > 0).map(val => parseInt(val));
            this.groups.forEach(group => {
                let groupVal = !this.val || this.val === "0" || splitted.indexOf(group.id) !== -1;
                this.$set(this.dataByGroup, group.id, groupVal);
                if (!groupVal) {
                    this.allGroups = false;
                }
            });
            this.$nextTick(() => {
                this.set = true;
            })
        },
        data() {
            return {
                set: false,
                allGroups: true,
                defaultSettings: this.data === "0" ||  this.data === 0,
                val: this.data,
                dataByGroup: {}
            }
        }
    }
</script>