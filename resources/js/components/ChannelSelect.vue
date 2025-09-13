<template>
    <div class="input-container__element-outer">
        <input type="hidden" name="channel_id" :value="channel.id" />
        <input class="input" @change="onChannelNameChange()" v-model="channel.name" :disabled="disabled || channel.unknown"/>
        <slot></slot>

        <div class="autocomplete__items" v-show="!channel.unknown">
            <a
                v-for="filteredChannel in filteredChannels"
                :key="filteredChannel.id"
                @click="selectChannel(filteredChannel)"
               class="autocomplete__item"
               :class="{'autocomplete__item--selected': channel && (channel.id === filteredChannel.id || channel.name === filteredChannel.name) }"
              >
                <span v-if="filteredChannel.logo" class="autocomplete__item__logo" :style="{backgroundImage: `url(${filteredChannel.logo.url})`}"></span>
                <span class="autocomplete__item__name">{{getFullName(filteredChannel)}}</span>
            </a>
        </div>
    </div>
</template>
<script>
let setChannelByNameTimeout;
export default {
    props: {
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
        channelsList: {
            type: Array,
            required: true
        },
        isRadio: {
            type: Boolean,
            required: false,
            default: false,
        },
        value: {
            type: Object,
            required: false,
            default: () => {
                return {
                    id: null,
                    name: ''
                }
            }
        },
    },
    data() {
        return {
            channel: this.value || {
                id: null,
                name: ''
            },
        }
    },
    watch: {
        channel(channel) {
            this.$emit('input', channel);
        },
        value (channel) {
            this.channel = channel;
        }
    },
    computed: {
        filteredChannels() {
            if (this.channel.name === '') {
                return this.channelsList.filter(channel => channel.is_federal && channel.is_radio === this.isRadio);
            } else {
                const lowercaseName = this.channel.name.toLowerCase();
                return this.channelsList.filter(channel => {
                    if (channel.is_radio !== this.isRadio) {
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
        }
    },
    methods: {
        selectChannel(channel) {
            this.channel.name = channel.name;
            this.channel.id = channel.id;
            this.$emit('selected');
        },
        onChannelNameChange() {
            if (this.channel.name === '') {
                this.channel.id = null;
            }
            clearTimeout(setChannelByNameTimeout);
            setChannelByNameTimeout = setTimeout(this.setChannelByName, 500);
        },
        getFullName(channel) {
            const additionalNames = [...new Set(channel.names.filter(name => name.name && name.name !== '' && name.name !== channel.name).map(name => name.name))];
            const additionalNamesText = additionalNames.length > 0 ? ` (${additionalNames.join(', ')})` : '';

            if (channel.city && channel.city !== '') {
                return `${channel.name} (${channel.city})${additionalNamesText}`;
            } else {
                if (channel.country && channel.country !== '') {
                    return `${channel.name} (${channel.country})${additionalNamesText}`;
                }
            }
            return `${channel.name} ${additionalNamesText}`;
        },
        setChannelByName() {
            const lowercaseName = this.channel.name.trim().toLowerCase();
            if (!lowercaseName.length) {
                return;
            }
            const foundChannel = this.filteredChannels.filter(channel => {
                return channel.name.toLowerCase() === lowercaseName || channel.names?.filter(name => name.name.toLowerCase() === lowercaseName).length;
            })[0];
            if (foundChannel) {
                this.selectChannel(foundChannel);
            }
        }
    }
}
</script>
