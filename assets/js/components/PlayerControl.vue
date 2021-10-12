<template>
    <div class="control">
        <div>
            <slot></slot>

        </div>
    </div>
</template>

<script>
    export default {
        name: "PlayerControl",
    }
</script>

<style lang="scss">
    @import "../../css/mediaqueries.scss";

    .control {
        box-sizing: border-box;
        width: 33.33%;
        float: left;
        height: 70px;
        display: table;
        cursor: pointer;

        &.active {
            svg {
                fill: #dc3545;
            }
        }

        @include smartphone {
            height: 35px;
        }

        div {
            width: 100%;
            height: 100%;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        &:hover {
            svg {
                fill: rgba(255, 255, 255, 1);
            }
        }

        svg:not([fill]), svg[fill="currentColor"] {
            fill: rgba(255, 255, 255, 0.5);
            transition: fill .3s ease;
        }
    }
</style>