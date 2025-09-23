<template>
    <div class="regions__container">
        <div class="regions__search">
            <div class="input-container">
                <div class="input-container__inner input-container__inner--with-icon">
                    <i class="fa fa-search input-container__icon"></i>
                    <input class="input" v-model="search" placeholder="Поиск каналов">
                </div>
            </div>
        </div>
        <div class="regions" v-show="search.length === 0">
            <div class="region region--all" :class="{'region--active': selectedRegion === null, 'region--parent-active': selectedRegion === null}"
                 @click="selectedRegion = null">
                <div class="region__name">Все</div>
            </div>
            <template v-for="(region, regionName) in data">
                <div class="region" @click="selectRegion(regionName)"
                     :class="{'region--active': selectedRegion === regionName, 'region--parent-active': selectedRegion === regionName && !selectedCity}"
                     v-if="region.channels.length || Object.keys(region.cities).length">
                    <div class="region__name">
                        {{ regionName }}
                        <span class="region__count" v-if="region.count > 0">{{ region.count }}</span>
                    </div>
                </div>
                <div
                    v-if="Object.keys(region.cities).length > 1"
                    :class="{'region__city--parent-active': selectedRegion === regionName, 'region__city--active': selectedCity === cityName, 'region__city--first': cityName === Object.keys(region.cities)[0], 'region__city--last': cityName === Object.keys(region.cities)[Object.keys(region.cities).length - 1] }"
                    @click.stop="selectCity(cityName, regionName)" v-for="(cityChannels, cityName) in region.cities"
                    :key="cityName" class="region__city">
                    {{ cityName }}
                    <span class="region__city__count">{{ cityChannels.length }}</span>
                </div>

            </template>
        </div>
        <div class="records-list__nothing-found" v-if="search.length && !channelsList.length">По запросу <strong>{{search}}</strong> ничего не найдено</div>
        <div ref="channels_list" class="channels-list">
            <a :title="channel.name" v-for="channel in channelsList" :key="channel.id" :href="channel.url"
               class="channel-item" :class="{'channel-item--pending': channel.pending}">
                <div class="channel-item__logo"
                     :style="channel.logo ? {backgroundImage: `url(${channel.logo.url})`} : {}"></div>
                <span class="channel-item__name">{{ channel.name }}</span>
            </a>
        </div>
    </div>
</template>
<script>
export default {
    computed: {
        channelsList() {
            if (this.search.length) {
                let channels = [];
                const search = this.search.toLocaleLowerCase();
                Object.keys(this.data).forEach(regionName => {
                    const region = this.data[regionName];
                    if (regionName.toLocaleLowerCase().includes(search)) {
                        channels = [...channels, ...region.channels];
                    }
                    channels = [...channels, ...region.channels.filter(channel => channel.name.toLocaleLowerCase().includes(search))];
                    const cities = Object.keys(region.cities);
                    if (cities.length > 0) {
                        cities.forEach(cityName => {
                            const cityChannels  = region.cities[cityName];
                            if (cityName.toLocaleLowerCase().includes(search)) {
                                channels = [...channels, ...cityChannels];
                            }
                            channels = [...channels, ...cityChannels.filter(channel => channel.name.toLocaleLowerCase().includes(search))];
                        })
                    }
                });
                return [...new Map(channels.map(channel => [channel.id, channel])).values()];
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
                const region = this.data[this.selectedRegion];
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
            window.scrollTo({
                top: rect.y + window.scrollY - (window.innerHeight - rect.height) / 2,
                behavior: 'smooth'
            });
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
