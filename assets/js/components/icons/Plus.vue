<template>
  <span :aria-hidden="decorative"
        :aria-label="title"
        class="material-design-icon plus-icon"
        role="img"
  >
    <svg :fill="fillColor"
         class="material-design-icon__svg"
         :width="size"
         :height="size"
         viewBox="0 0 24 24">
      <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z">
        <title v-if="!decorative">{{ title }}</title>
      </path>
    </svg>
  </span>
</template>

<script>
    export default {
        name: "PlusIcon",
        props: {
            title: {
                type: String,
                default: "Plus icon"
            },
            decorative: {
                type: Boolean,
                default: false
            },
            fillColor: {
                type: String,
                default: "currentColor"
            },
            size: {
                type: Number,
                default: 24
            }
        }
    }
</script>
