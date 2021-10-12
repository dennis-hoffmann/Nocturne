<template>
    <div v-if="updates.length" class="notification-section">
        <Notification :key="key" v-for="(update, key) in updates" :icon="update.icon" :headline="update.headline" :message="update.message" :id="key"/>
    </div>
</template>

<script>
    import Notification from "./Notification";
    import {mapGetters} from "vuex";
    export default {
        name: "NotificationSection",
        components: {Notification},
        computed: {
            ...mapGetters('mercure', [
                'updates'
            ])
        }
    }
</script>

<style scoped lang="scss">
    .notification-section {
        height: auto;
        z-index: 601;
        position: absolute;
        top: 0;
        right: 0;
        padding: 1rem;
        max-height: 99vh;
        overflow-y: scroll;
        background-color: rgba(255, 255, 255, .50);
    }
</style>
