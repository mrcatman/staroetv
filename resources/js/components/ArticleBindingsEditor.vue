<template>
    <div class="article-bindings">
        <input type="hidden" name="bindings" :value="JSON.stringify({programs: selectedPrograms, channels: selectedChannels})" />
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Программы</label>
            <div class="input-container__inner">
                <select2 ref="programs_input" :customOptions="programsAutocompleteOptions" multiple v-model="selectedChannels"></select2>
                <span class="input-container__message"></span>
            </div>
        </div>
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Каналы</label>
            <div class="input-container__inner">
                <select2 ref="channels_input" :customOptions="channelsAutocompleteOptions" multiple v-model="selectedPrograms"></select2>
                <span class="input-container__message"></span>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .article-bindings {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: var(--col-margin);
    }
</style>
<script lang="ts" setup>
import { ref } from "vue";

const props = defineProps<{
    bindings: Models.ArticleBinding[]
}>();

const selectedChannels = ref<Models.Channel[]>([]);
const selectedPrograms = ref<Models.Program[]>([]);


            // this.bindings.forEach(binding => {
            //     if (binding.channel_id) {
            //         let $option = $(`<option selected value="${binding.channel_id}">${binding.name}</option>`);
            //         $(this.$refs.channels_input.$el).append($option).trigger('change');
            //     } else {
            //         let $option = $(`<option selected value="${binding.program_id}">${binding.name}</option>`);
            //         $(this.$refs.programs_input.$el).append($option).trigger('change');
            //     }
            // })

const channelsAutocompleteOptions = {
    ajax: {
        method: 'GET',
        url: route('channels.autocomplete'),
        dataType: 'json',
        processResults: function (data) {
            return {
                results: data.data.channels.map(channel => {
                    return {
                        id: channel.id,
                        text: channel.name,
                    }
                }),
                pagination: {
                    more: data.data.channels.length > 0
                }
            };
        },
    }
};
const programsAutocompleteOptions = {
    ajax: {
        method: 'GET',
        url: route('programs.autocomplete'),
        dataType: 'json',
        processResults: function (data) {
            return {
                results: data.data.programs.map(program => {
                    return {
                        id: program.id,
                        text: program.name,
                    }
                }),
                pagination: {
                    more: data.data.programs.length > 0
                }
            };
        },
    }
};
</script>
