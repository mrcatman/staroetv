<template>
    <div class="additional-channels">
        <input type="hidden" name="additional_channels" :value="channelsJson" />
        <div class="additional-channels__inner">
            <div  v-for="(channelItem, $index) in additionalChannels" :key="$index">
                <div class="row row--align-end">
                    <div class="col">
                        <input-container vertical :label="isRadio ? 'Радио' : 'Канал'">
                            <select2 v-model="channelItem.channel_id" :options="channelsList" />
                        </input-container>

                    </div>
                    <div class="col ">
                        <input-container vertical label="Название (если оно отличалось)">
                            <input v-model="channelItem.title" class="input"/>
                        </input-container>
                    </div>
                    <div class="col additional-channels__datepicker-container">
                        <input-container vertical label="Дата начала показа">
                            <datepicker v-model="channelItem.date_start"/>
                        </input-container>
                    </div>
                    <div class="col additional-channels__datepicker-container">
                        <input-container vertical label="Дата конца показа">
                            <datepicker v-model="channelItem.date_end"/>
                        </input-container>
                    </div>
                    <a class="button button--light" @click="additionalChannels.splice($index, 1)">Удалить</a>
                </div>
            </div>
        </div>
        <div class="additional-channels__bottom">
            <a class="button button--light" @click="addItem()">
                <i class="fa fa-plus"></i>
                Добавить {{ isRadio ? 'радио' : 'канал' }}
            </a>
        </div>

    </div>
</template>
<style lang="scss">
    .additional-channels {
        flex: 1;

        &__inner {
            display: flex;
            flex-direction: column;
            gap: 2em;
        }
        &__bottom {
            margin-top: 2em;
        }

        &__datepicker-container {
            flex: .5;
        }

        &__row {
            width: calc(100% - 2em);
            margin: 0;
            border-bottom: 1px solid var(--border-color);
            padding: 1em;
         }


    }
</style>
<script lang="ts" setup>
import { computed, onMounted, ref } from "vue";
import Select2 from "@/components/Select2.vue";
import InputContainer from "@/components/InputContainer.vue";
import { useChannelsStore } from "@/stores/channels";
import Datepicker from "@/components/Datepicker.vue";

const props = defineProps<{
    data: Models.AdditionalChannel[],
    isRadio: boolean
}>();

const additionalChannels = ref<Partial<Models.AdditionalChannel[]>>(props.data);

const channelsJson = computed(() => {
    return JSON.stringify(additionalChannels.value);
})

const addItem = () => {
    additionalChannels.value.push({
        title: "",
        channel_id: null,
        date_start: null,
        date_end: null,
    });
}

const channelsStore = useChannelsStore();
channelsStore.load();

const channelsList = computed(() => {
    return (props.isRadio ? channelsStore.radioStatuins : channelsStore.channels).map((channel) => ({ id: channel.id, text: channel.name }));
})
</script>
