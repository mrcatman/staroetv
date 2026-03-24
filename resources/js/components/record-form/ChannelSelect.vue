<template>
    <div class="input-container__element-outer">
        <Preloader v-if="loading" />
        <input type="hidden" name="channel_id" :value="channel.id"/>
        <div class="autocomplete">
            <div class="row">
                <div class="col">
                    <div class="input-container__inner input-container__inner--with-button">
                        <div class="input-container__element-outer">
                            <input class="input" @change="onNameChange()" v-model="channel.name"
                                   :disabled="disabled || channel.unknown" placeholder="Поиск каналов по названию..."/>
                            <a v-if="channel.name.length && !channel.id && !channel.unknown" class="input-container__button input-container__button--big input-container__button--info">
                                <span class="tooltip">Будет создан новый канал</span>
                                <i class="fa fa-exclamation-circle"></i>
                            </a>
                        </div>
                    </div>

                    <slot></slot>
                </div>
                <div class="col col--auto">
                    <label class="input-container input-container--checkbox">
                        <input type="checkbox" v-model="channel.unknown">
                        <div class="input-container--checkbox__element"></div>
                        {{isRadio ? "Радиостанция неизвестна" : "Канал неизвестен"}}
                    </label>
                </div>
            </div>


            <div class="tabs" v-show="!channel.name.length">
                <a class="tab" v-for="(categoryName, categoryId) in channelCategories" :class="{'tab--active': category === categoryId}" @click="category = categoryId">
                    {{categoryName}}
                </a>
            </div>
            <div class="autocomplete__items" v-show="!channel.unknown">
                <a
                    v-for="filteredChannel in filteredChannels"
                    :key="filteredChannel.id"
                    @click="selectChannel(filteredChannel)"
                    class="autocomplete__item"
                    :class="{'autocomplete__item--selected': channel && channel.id === filteredChannel.id }"
                >
                <span v-if="filteredChannel.logo" class="autocomplete__item__logo"
                      :style="{backgroundImage: `url(${filteredChannel.logo.url})`}"></span>
                    <div class="autocomplete__item__texts">
                        <span class="autocomplete__item__name">{{ getDisplayName(filteredChannel) }}</span>
                        <span class="autocomplete__item__description">{{ getAdditionalNames(filteredChannel) }}</span>
                    </div>

                </a>
            </div>
        </div>

    </div>
</template>
<script lang="ts" setup>
import { computed, ref, watch, defineModel } from 'vue';
import { storeToRefs } from "pinia";
import { filterChannel, channelCategories, findByName, getDisplayName, getAdditionalNames } from "@/utils/channels";
import { useChannelsStore } from "@/stores/channels.js";
import { RecordsUploadRelationData } from "@/composables/record-form";
import Preloader from "@/components/Preloader.vue";

const channelsStore = useChannelsStore()
const { loading, channels, radioStations } = storeToRefs(channelsStore);
channelsStore.load();

const emit = defineEmits<{ (e: 'selected'): void }>();

const props = defineProps<{
    disabled?: boolean,
    isRadio?: boolean,
    channel?: RecordsUploadRelationData
}>();

const model = defineModel<RecordsUploadRelationData>({
    default: () => ({
        id: null,
        name: '',
        unknown: false,
    })
});

const channel = ref<RecordsUploadRelationData>(props.channel ?? model.value);
watch(() => channel, () => {
    model.value = channel.value;
}, { deep: true })

const category = ref<string>('federal');

const filteredChannels = computed(() => {
    const list = props.isRadio ? radioStations : channels;
    if (channel.value.name === '') {
        return list.value.filter(channel => filterChannel(channel, category.value) && channel.is_radio === props.isRadio);
    } else {
        const lowercaseName = channel.value.name.toLowerCase();
        return list.value.filter(channel => {
            if (channel.name.toLowerCase().indexOf(lowercaseName) !== -1) {
                return true;
            }
            if (channel.names) {
                return !!channel.names.filter(name => name.name.toLowerCase().indexOf(lowercaseName) !== -1).length;
            }
            return false;
        }).sort((a, b) => {
            return b.is_federal - a.is_federal;
        })
    }
});


const selectChannel = (_channel: Models.Channel) => {
    channel.value.id = _channel.id;
    channel.value.name = _channel.name;
    emit('selected');
}

let findChannelTimeout;
const onNameChange = () => {
    channel.value.id = null;
    clearTimeout(findChannelTimeout);
    findChannelTimeout = setTimeout(findChannel, 500);
}


const findChannel = () => {
    const { channel: foundChannel } = findByName(channel.value.name, filteredChannels.value);
    if (foundChannel) {
        selectChannel(foundChannel);
    }
}
</script>
