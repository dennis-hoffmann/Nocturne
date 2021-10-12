<template>
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <img v-if="icon" :src="icon" class="rounded mr-2 toast-icon" alt="">
            <strong class="mr-auto">{{ headline }}</strong>
            <small ref="created" class="text-muted created-at">{{ createdAt }}</small>
            <button @click="dismiss" type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            {{ message }}
        </div>
    </div>
</template>

<script>
    import {mapMutations} from "vuex";

    export default {
        name: "Notification",
        props: {
            icon: String,
            headline: String,
            message: String,
            id: Number,
        },
        data() {
            return {
                createdAt: 'Just now',
                secondsShown: 0,
                interval: null,
            }
        },
        methods: {
            ...mapMutations('mercure', [
                'dismissUpdate'
            ]),
            dismiss() {
                this.dismissUpdate(this.id)
            }
        },
        mounted() {
            this.interval = setInterval(() => {
                this.secondsShown++;
                const minutes = this.secondsShown / 60;
                const remainder = this.secondsShown % 60;
                let message = '';

                if (minutes < 1) {
                    message = `${this.secondsShown} seconds ago.`;
                } else {
                    if (remainder) {
                        message = `${Math.floor(minutes)} minutes and ${remainder} seconds ago.`;
                    } else {
                        message = `${Math.floor(minutes)} minutes ago.`;
                    }
                }

                this.createdAt = message
            }, 1000);
        },
        unmounted() {
            clearInterval(this.interval);
            this.createdAt = '';
        },
    }
</script>

<style scoped lang="scss">
    .toast {
        opacity: 1;
    }

    .toast-header {
        strong {
            padding-right: 1rem;
        }
    }

    .toast-icon {
        height: 1.5rem;
    }
</style>