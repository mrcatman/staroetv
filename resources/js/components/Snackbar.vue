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
       }
        &__message {
            background: linear-gradient(#e0e0e0, #dadada);
            z-index: 10000;

            padding: .5em 1em;
            position: relative;
            animation: snackbarMessage .5s;
            margin: 0 0 .25em;
            font-size: 1.25em;
            &--error {
                background: #f00;
                color: #fff;
            }
            &--success {
                background: #2baf2b;
                color: #fff;
            }
            &__inner {

            }
        }
    }

    @keyframes snackbarMessage {
        0% {
            top: 2em;
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
