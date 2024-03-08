<template>
    <div class="regions__container">
        <div class="regions__search">
            <input class="input" v-model="search" placeholder="Поиск каналов">
        </div>
        <div class="regions" v-show="search.length === 0">
            <div class="region region--all" :class="{'region--active': selectedRegion === null}" @click="selectedRegion = null">
                <div class="region__name">Все</div>
            </div>
            <div class="region" :class="{'region--active': selectedRegion === regionName}" @click="selectRegion(regionName)" v-for="(region, regionName) in data" :key="region.name">
                <div v-if="region.channels.length > 0 || Object.keys(region.cities).length > 1" class="region__name" >
                    {{regionName}}
                    <span class="region__count">{{region.count}}</span>
                </div>
                <div class="region__cities">
                    <div :class="{'region__city--active': selectedCity === cityName}" @click.stop="selectCity(cityName, regionName)" v-for="(cityChannels, cityName) in region.cities" :key="cityName" class="region__city">
                        {{cityName}}
                        <span class="region__city__count">{{cityChannels.length}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div ref="channels_list" class="channels-list">
            <a v-for="channel in channelsList" :key="channel.id" :href="channel.url" class="channel-item" :class="{'channel-item--pending': channel.pending}">
                <div class="channel-item__logo" :style="channel.logo ? {backgroundImage: `url(${channel.logo.url})`} : {}"></div>
                <span class="channel-item__name">{{channel.name}}</span>
            </a>
        </div>
    </div>
</template>
<style lang="scss">

</style>
<script>
    export default {
        computed: {
            channelsList() {
                if (this.search.length > 0) {
                    let channels = [];
                    let search = this.search.toLocaleLowerCase();
                    Object.values(this.data).forEach(region => {
                        channels = [...channels, ...region.channels.filter(channel => channel.name.toLocaleLowerCase().indexOf(search) !== -1)];
                        let cities = Object.values(region.cities);
                        if (cities.length > 0) {
                            cities.forEach(cityChannels => {
                                channels = [...channels, ...cityChannels.filter(channel => channel.name.toLocaleLowerCase().indexOf(search) !== -1)];
                            })
                        }
                    });
                    return channels;
                }
                if (!this.selectedRegion) {
                    let channels = [];
                    Object.values(this.data).forEach(region => {
                        channels = [...channels, ...region.channels];
                        let cities = Object.values(region.cities);
                        if (cities.length > 0) {
                            cities.forEach(cityChannels => {
                                channels = [...channels, ...cityChannels];
                            })
                        }
                    });
                    return channels;
                } else {
                    let region = this.data[this.selectedRegion];
                    let channels = region.channels;
                    if (this.selectedCity) {
                        return region.cities[this.selectedCity];
                    } else {
                        let cities = Object.values(region.cities);
                        if (cities.length > 0) {
                            cities.forEach(cityChannels => {
                                channels = [...channels, ...cityChannels];
                            })
                        }
                        return channels;
                    }
                }
            }
        },
        methods: {
            scrollToChannels() {
                const rect = this.$refs.channels_list.getBoundingClientRect();
                window.scrollTo(0, rect.y + window.scrollY - (window.innerHeight - rect.height) / 2);
            },
            selectRegion(regionName) {
                this.selectedCity = null;
                this.selectedRegion = regionName;
                this.$nextTick(() => {
                    this.scrollToChannels();
                });
            },

            selectCity(cityName, regionName) {
                this.selectedCity = cityName;
                this.selectedRegion = regionName;
                this.$nextTick(() => {
                    this.scrollToChannels();
                });
            }
        },
        props: {
            data: {
                type: [Array, Object],
                required: true
            }
        },
        data() {
            return {
                selectedRegion: null,
                selectedCity: null,
                search: ''
            }
        },
        mounted() {

        },
    }
</script>
