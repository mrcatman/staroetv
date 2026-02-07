<template>
    <div class="article-bindings">
        <input type="hidden" name="bindings" :value="JSON.stringify({programs: selectedPrograms, channels: selectedChannels})" />
        <input-container vertical label="Программы" label-small>
            <select2 ref="programs_input" :customOptions="programsAutocompleteOptions" multiple v-model="selectedPrograms"></select2>
        </input-container>
        <input-container vertical label="Каналы" label-small>
            <select2 ref="channels_input" :customOptions="channelsAutocompleteOptions" multiple v-model="selectedChannels"></select2>
        </input-container>
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
import { onMounted, ref, useTemplateRef } from "vue";
import InputContainer from "@/components/InputContainer.vue";
import Select2 from "@/components/Select2.vue";

const props = defineProps<{
    bindings: Models.ArticleBinding[]
}>();

const selectedChannels = ref<Models.Channel[]>([]);
const selectedPrograms = ref<Models.Program[]>([]);

const programsInputRef = useTemplateRef<typeof Select2>('programs_input');
const channelsInputRef = useTemplateRef<typeof Select2>('channels_input');

onMounted(() => {
    props.bindings.forEach(binding => {
        if (binding.channel_id) {
            const $option = $(`<option selected value="${binding.channel_id}">${binding.name}</option>`);
            $(channelsInputRef.value.select2).append($option).trigger('change');

        } else {
            const $option = $(`<option selected value="${binding.program_id}">${binding.name}</option>`);
            $(programsInputRef.value.select2).append($option).trigger('change');
        }
    })
});


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
