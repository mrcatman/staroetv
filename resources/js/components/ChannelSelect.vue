<template>
    <div class="input-container__element-outer">
        <Preloader v-if="loading" />
        <input type="hidden" name="channel_id" :value="channel.id"/>
        <div class="autocomplete">
            <div class="row">
                <div class="col">
                    <input class="input" @change="onNameChange()" v-model="channel.name"
                           :disabled="disabled || channel.unknown" placeholder="Поиск каналов по названию..."/>
                </div>
                <div class="col col--auto">
                    <slot></slot>
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
import { computed, ref, defineModel } from 'vue';
import { storeToRefs } from "pinia";
import { filterChannel, channelCategories } from "@/utils/channels";
import { useChannelsStore } from "@/stores/channels.js";
import { RecordsUploadChannelData } from "@/composables/record-form";
import Preloader from "@/components/Preloader.vue";

const { loading, channels } = storeToRefs(useChannelsStore());

const emit = defineEmits<{ (e: 'selected'): void }>();

const props = defineProps<{
    disabled?: boolean,
    isRadio?: boolean,
}>();

const channel = defineModel<RecordsUploadChannelData>('channel', {
    default: {
        id: null,
        name: ''
    }
});

const category = ref<string>('federal');

const filteredChannels = computed(() => {
    if (channel.value.name === '') {
        return channels.value.filter(channel => filterChannel(channel, category.value) && channel.is_radio === props.isRadio);
    } else {
        const lowercaseName = channel.value.name.toLowerCase();
        return channels.value.filter(channel => {
            if (channel.is_radio !== props.isRadio) {
                return false;
            }
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

let findByNameTimeout;
const onNameChange = () => {
    if (channel.value.id) {
        return;
    }
    if (channel.value.name === '') {
        channel.value.id = null;
    }
    clearTimeout(findByNameTimeout);
    findByNameTimeout = setTimeout(findByName, 500);
}

const getDisplayName = (_channel: Models.Channel) => {
    if (_channel.city && _channel.city !== '') {
        return `${_channel.name} (${_channel.city})`;
    } else {
        if (_channel.country && _channel.country !== '') {
            return `${_channel.name}  (${_channel.country})`;
        }
    }
    return _channel.name;
}

const getAdditionalNames = (_channel: Models.Channel) => {
    const additionalNames = [...new Set(_channel.names.filter(name => name.name && name.name !== '' && name.name !== _channel.name).map(name => name.name))];
    return additionalNames.join(', ');
}

const findByName = () => {
    const lowercaseName = channel.value.name.trim().toLowerCase();
    if (!lowercaseName.length) {
        return;
    }
    const foundChannel = filteredChannels.value.filter(channel => {
        return channel.name.toLowerCase() === lowercaseName || channel.names?.filter(name => name.name.toLowerCase() === lowercaseName).length;
    })[0];
    if (foundChannel) {
        selectChannel(foundChannel);
    }
}
</script>
