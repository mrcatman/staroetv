<template>
    <div class="article-bindings">
        <input type="hidden" name="bindings" :value="JSON.stringify({programs: programs.selected, channels: channels.selected})" />
        <div class="input-container">
            <label class="input-container__label">Программы</label>
            <div class="input-container__inner">
                <select2 ref="programs_input" :customOptions="programs.autocompleteOptions" multiple v-model="programs.selected"></select2>
                <span class="input-container__message"></span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Каналы</label>
            <div class="input-container__inner">
                <select2 ref="channels_input" :customOptions="channels.autocompleteOptions" multiple v-model="channels.selected"></select2>
                <span class="input-container__message"></span>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .article-bindings {
        margin: -1em 0 0;
        flex: 1;
    }
</style>
<script>
    const channelsAutocompleteOptions = {
        ajax: {
            method: 'POST',
            url: '/channels/autocomplete',
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
            method: 'POST',
            url: '/programs/autocomplete',
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

    export default {
        mounted() {
            this.bindings.forEach(binding => {
                if (binding.channel_id) {
                    let $option = $(`<option selected value="${binding.channel_id}">${binding.name}</option>`);
                    $(this.$refs.channels_input.$el).append($option).trigger('change');
                } else {
                    let $option = $(`<option selected value="${binding.program_id}">${binding.name}</option>`);
                    $(this.$refs.programs_input.$el).append($option).trigger('change');
                }
            })

        },

        data() {
            return {
                channels: {
                    selected: [],
                    autocompleteOptions: channelsAutocompleteOptions
                },
                programs: {
                    selected: [],
                    autocompleteOptions: programsAutocompleteOptions
                }
            }
        },
        props: {
            bindings: {
                type: Array,
                required: true
            }
        }
    }
</script>
