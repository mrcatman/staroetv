<template>
    <div class="snackbar__container">
        <div class="snackbar__message" :key="$index" v-for="(data, $index) in messages" :class="{'snackbar__message--success': data.status === 1, 'snackbar__message--error': data.status === 0}">
            <div class="snackbar__message__inner" v-html="data.text"></div>
        </div>
    </div>
</template>
<style lang="scss">
    .snackbar {
        &__container {
            position: fixed;
            bottom: 1em;
            right: 1em;
            z-index: 10000000;
            font-family: "Roboto", sans-serif;
        }
        &__message {
            background: #e0e0e0;
            z-index: 10000;
            padding: 3px;
            position: relative;
            animation: snackbarMessage .5s;
            margin: 0 0 4px;
            &--error {
                color: #f00;
            }
            &--success {
                color: blue;
            }
            &__inner {
                padding: 12px;
                background: #d7d7d7;
                border-left: 1px solid #eaebec;
                border-top: 1px solid #eaebec;
                border-bottom: 1px solid #c2c5ca;
                border-right: 1px solid #c2c5ca;
            }
        }
    }

    @keyframes snackbarMessage {
        0% {
            top: 32px;
            opacity: 0;
        }
        100% {
            top: 0;
            opacity: 1;
        }
    }
</style>
<script>
    export default {
        methods: {
            show(data) {
                data._key = Math.floor(Math.random() * 10000);
                this.messages.unshift(data);
                setTimeout(() => {
                    this.messages = this.messages.filter(message => message._key !== data._key)
                }, 7500)
            }
        },
        data() {
            return {
                messages: []
            }
        }
    }
</script>