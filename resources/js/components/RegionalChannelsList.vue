<template>
    <div class="regions__container">
        <div class="regions__search">
            <div class="input-container input-container__inner--with-icon">
                <div class="input-container__inner ">
                    <i class="fa fa-search input-container__icon"></i>
                    <input class="input" v-model="search" placeholder="Поиск каналов">
                </div>
            </div>
        </div>
        <div class="regions" v-show="search.length === 0">
            <div class="region region--all" :class="{ 'region--active': selectedRegion === null, 'region--parent-active': selectedRegion === null}"
                 @click="selectedRegion = null">
                <div class="region__name">Все</div>
            </div>
            <template v-for="(region, regionName) in data">
                <div class="region" @click="selectRegion(regionName)"
                     :class="{'region--last':Object.keys(region.cities).length <= 1, 'region--active': selectedRegion === regionName, 'region--parent-active': selectedRegion === regionName && !selectedCity}"
                     v-if="region.channels.length || Object.keys(region.cities).length">
                    <div class="region__name">
                        {{ shortenName(regionName) }}
                        <span class="region__count" v-if="region.count > 0">{{ region.count }}</span>
                    </div>
                </div>
                <div
                    v-if="Object.keys(region.cities).length > 1"
                    :class="{'region__city--parent-active': selectedRegion === regionName, 'region__city--active': selectedCity === cityName, 'region--last': cityName === Object.keys(region.cities)[Object.keys(region.cities).length - 1] }"
                    @click.stop="selectCity(cityName, regionName)" v-for="(cityChannels, cityName) in region.cities"
                    :key="cityName" class="region__city">
                    {{ cityName }}
                    <span class="region__city__count">{{ cityChannels.length }}</span>
                </div>

            </template>
        </div>
        <div class="records-list__nothing-found" v-if="search.length && !list.length">По запросу <strong>{{search}}</strong> ничего не найдено</div>
        <div ref="channels_list" class="channels-list">
            <a :title="channel.name" v-for="channel in list" :key="channel.id" :href="channel.url"
               class="channel-item" :class="{'channel-item--pending': channel.pending}">
                <div class="channel-item__logo"
                     :style="channel.logo ? {backgroundImage: `url(${channel.logo.url})`} : {}"></div>
                <span class="channel-item__name">{{ channel.name }}</span>
            </a>
        </div>
    </div>
</template>
<script lang="ts" setup>
import {computed, ref, nextTick, useTemplateRef} from "vue";

interface RegionalChannelsData {
    [key: string]: {
        count: number,
        channels: Channels.Base[],
        cities: {
            [key: string]: Channels.Base[]
        }
    }
}

const props = defineProps<{
  data: RegionalChannelsData
}>();

const selectedRegion = ref('');
const selectedCity = ref('');
const search = ref('');
const listRef = useTemplateRef<HTMLDivElement>('channels_list')

const shortenName = (name: string) => {
    return name.replace('область', 'обл.').replace('автономный округ', 'АО')
}

const list = computed(() => {
    if (search.value.length) {
        let channels = [];
        const _search = search.value.toLocaleLowerCase();

        Object.keys(props.data).forEach(regionName => {
            const region = props.data[regionName];
            if (regionName.toLocaleLowerCase().includes(_search)) {
                channels = [...channels, ...region.channels];
            }
            channels = [...channels, ...region.channels.filter(channel => channel.name.toLocaleLowerCase().includes(_search))];

            const cities = Object.keys(region.cities);
            if (cities.length) {
                cities.forEach(cityName => {
                    const cityChannels = region.cities[cityName];
                    if (cityName.toLocaleLowerCase().includes(_search)) {
                        channels = [...channels, ...cityChannels];
                    }
                    channels = [...channels, ...cityChannels.filter(channel => channel.name.toLocaleLowerCase().includes(_search))];
                })
            }
        });
        return [...new Map(channels.map(channel => [channel.id, channel])).values()];
    }

    if (!selectedRegion.value?.length) {
        let channels = [];
        Object.values(props.data).forEach(region => {
            channels = [...channels, ...region.channels];
            const cities = Object.values(region.cities);
            if (cities.length > 0) {
                cities.forEach(cityChannels => {
                    channels = [...channels, ...cityChannels];
                })
            }
        });
        return channels;
    } else {
        const region = props.data[selectedRegion.value];
        if (selectedCity.value) {
            return region.cities[selectedCity.value];
        }

        let channels = region.channels;
        let cities = Object.values(region.cities);
        if (cities.length > 0) {
            cities.forEach(cityChannels => {
                channels = [...channels, ...cityChannels];
            })
        }
        return channels;
    }
})

const scrollToChannels = () => {
    const rect = listRef.value.getBoundingClientRect();
    window.scrollTo({
        top: rect.y + window.scrollY - (window.innerHeight - rect.height) / 2,
        behavior: 'smooth'
    });
}

const selectRegion = async(region: string) => {
    selectedCity.value = null;
    selectedRegion.value = region;

    await nextTick();
    scrollToChannels();
}

const selectCity = async (city: string, region: string) => {
    selectedCity.value = city;
    selectedRegion.value = region;

    await nextTick();
    scrollToChannels();
}
</script>
